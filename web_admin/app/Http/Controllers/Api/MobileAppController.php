<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingEarning;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\Driver;
use App\Models\LaundryService;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\FirebasePushService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MobileAppController extends Controller
{
    public function __construct(private readonly FirebasePushService $push)
    {
    }

    public function services(): JsonResponse
    {
        $services = LaundryService::where('SERVICE_NAME', '!=', 'Wash + Dry')->get()->map(fn ($service) => [
            'id' => $service->ID, 'name' => $service->SERVICE_NAME, 'description' => $service->DESCRIPTION,
            // Decimal database values are cast to numbers for Flutter's `num` parser.
            'price' => (float) $service->PRICE, 'status' => $service->STATUS,
        ]);
        return response()->json($services);
    }

    public function pricing(): JsonResponse
    {
        return response()->json(['serviceFee' => $this->setting('pricing.service-fee', 2), 'deliveryFee' => $this->setting('pricing.delivery-fee', 6)]);
    }

    public function customerBookings(User $user): JsonResponse
    {
        $customer = $this->customer($user);
        return response()->json($customer->bookings()->with(['customer.user', 'driver.user'])->latest('CREATED_AT')->get()->map(fn ($booking) => $this->bookingResponse($booking, $this->unread($booking, $user))));
    }

    public function customerMoneySummary(User $user): JsonResponse
    {
        $customer = $this->customer($user);
        $spending = Payment::whereHas('booking', fn ($query) => $query->where('CUSTOMER_ID', $customer->ID))->where('PAYMENT_STATUS', 'PAID')->sum('AMOUNT');
        return response()->json(['totalSpending' => (float) $spending, 'walletBalance' => $this->walletBalance($customer)]);
    }

    public function createBooking(Request $request, User $user): JsonResponse
    {
        $customer = $this->customer($user);
        $data = $request->validate([
            'serviceId' => ['nullable', 'integer'], 'serviceIds' => ['nullable', 'array'], 'serviceIds.*' => ['integer'],
            'pickupAddress' => ['required', 'string'], 'pickupPostcode' => ['required', 'string'],
            'pickupCity' => ['required', 'string'], 'pickupState' => ['required', 'string'],
            'pickupLatitude' => ['nullable', 'numeric'], 'pickupLongitude' => ['nullable', 'numeric'],
            'pickupDateTime' => ['nullable', 'date'],
        ]);
        $ids = array_values(array_unique($data['serviceIds'] ?? (isset($data['serviceId']) ? [$data['serviceId']] : [])));
        if (! $ids) $this->fail('Please choose at least one service.');
        $services = LaundryService::whereIn('ID', $ids)->get();
        if ($services->count() !== count($ids)) $this->fail('One or more selected services were not found.');
        if ($services->contains(fn ($service) => strtoupper($service->STATUS) !== 'ACTIVE')) $this->fail('One or more selected services are currently unavailable.');

        $booking = DB::transaction(function () use ($customer, $data, $services) {
            $laundryFee = $services->sum('PRICE');
            $serviceFee = $this->setting('pricing.service-fee', 2);
            $deliveryFee = $this->setting('pricing.delivery-fee', 6);
            $booking = Booking::create([
                'BOOKING_NO' => 'CC-'.now()->format('YmdHisv'), 'PUBLIC_ID' => (string) str()->uuid(),
                'CUSTOMER_ID' => $customer->ID, 'PICKUP_ADDRESS' => trim($data['pickupAddress']),
                'PICKUP_POSTCODE' => $data['pickupPostcode'], 'PICKUP_CITY' => $data['pickupCity'], 'PICKUP_STATE' => $data['pickupState'],
                'PICKUP_LATITUDE' => $data['pickupLatitude'] ?? null, 'PICKUP_LONGITUDE' => $data['pickupLongitude'] ?? null,
                'PICKUP_DATE_TIME' => $data['pickupDateTime'] ?? null, 'SERVICE_SUMMARY' => $services->pluck('SERVICE_NAME')->join(', '),
                'BOOKING_STATUS' => 'PENDING_PAYMENT', 'LAUNDRY_FEE' => $laundryFee, 'SERVICE_FEE' => $serviceFee,
                'DELIVERY_FEE' => $deliveryFee, 'TOTAL_AMOUNT' => $laundryFee + $serviceFee + $deliveryFee, 'CREATED_AT' => now(),
            ]);
            Payment::create(['BOOKING_ID' => $booking->ID, 'PAYMENT_METHOD' => 'Mobile Mock Payment', 'PAYMENT_STATUS' => 'UNPAID', 'AMOUNT' => $booking->TOTAL_AMOUNT]);
            $this->history($booking, 'PENDING_PAYMENT', 'Booking created from mobile app.', $customer->user);
            return $booking;
        });
        $booking->load(['customer.user', 'driver.user']);
        return response()->json($this->bookingResponse($booking));
    }

    public function payBooking(User $user, Booking $booking): JsonResponse
    {
        $customer = $this->ownsCustomerBooking($user, $booking);
        if ($booking->BOOKING_STATUS !== 'PENDING_PAYMENT') $this->fail('This booking is not waiting for payment.');
        DB::transaction(function () use ($booking, $customer) {
            $useWallet = $this->walletBalance($customer) >= (float) $booking->TOTAL_AMOUNT;
            $booking->payment()->update([
                'PAYMENT_METHOD' => $useWallet ? 'Customer Wallet' : 'Mobile Mock Payment', 'PAYMENT_STATUS' => 'PAID',
                'TRANSACTION_REF' => ($useWallet ? 'WALLET-' : 'MOB-').$booking->BOOKING_NO, 'PAID_AT' => now(),
            ]);
            if ($useWallet) CustomerWalletTransaction::create(['CUSTOMER_ID' => $customer->ID, 'BOOKING_ID' => $booking->ID, 'TRANSACTION_TYPE' => 'DEBIT', 'AMOUNT' => $booking->TOTAL_AMOUNT, 'REMARKS' => 'Wallet payment for '.$booking->BOOKING_NO.'.', 'CREATED_AT' => now()]);
            $booking->update(['BOOKING_STATUS' => 'PAID_WAITING_DRIVER']);
            $this->history($booking, 'PAID_WAITING_DRIVER', $useWallet ? 'Payment completed using customer wallet.' : 'Mock payment completed from mobile app.', $customer->user);
        });
        $this->push->sendToActiveDrivers('New CoolClean job available', $booking->BOOKING_NO.' is paid and waiting for pickup.');
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function cancelCustomerBooking(User $user, Booking $booking): JsonResponse
    {
        $customer = $this->ownsCustomerBooking($user, $booking);
        if (! in_array($booking->BOOKING_STATUS, ['PENDING_PAYMENT', 'PAID_WAITING_DRIVER'], true)) $this->fail('Customer cancellation is only available before a driver accepts the booking.');
        DB::transaction(function () use ($booking, $customer) {
            if ($booking->payment?->PAYMENT_STATUS === 'PAID') {
                $booking->payment->update(['PAYMENT_STATUS' => 'REFUNDED']);
                CustomerWalletTransaction::create(['CUSTOMER_ID' => $customer->ID, 'BOOKING_ID' => $booking->ID, 'TRANSACTION_TYPE' => 'CREDIT', 'AMOUNT' => $booking->TOTAL_AMOUNT, 'REMARKS' => 'Refund for '.$booking->BOOKING_NO.'.', 'CREATED_AT' => now()]);
            }
            $booking->update(['BOOKING_STATUS' => 'CANCELLED']);
            $this->history($booking, 'CANCELLED', 'Customer cancelled before driver acceptance.', $customer->user);
        });
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function availableJobs(User $user): JsonResponse
    {
        $driver = $this->driver($user);
        $jobs = Booking::with(['customer.user', 'driver.user'])->where('BOOKING_STATUS', 'PAID_WAITING_DRIVER')->whereNull('DRIVER_ID')->oldest('CREATED_AT')->get()
            ->map(fn ($booking) => $this->bookingResponse($booking, 0, $this->distance($driver, $booking)))->filter(fn ($booking) => $booking['distanceKm'] === null || $booking['distanceKm'] <= 30)->values();
        return response()->json($jobs);
    }

    public function driverJobs(User $user): JsonResponse
    {
        $driver = $this->driver($user);
        return response()->json($driver->bookings()->with(['customer.user', 'driver.user'])->latest('CREATED_AT')->get()->map(fn ($booking) => $this->bookingResponse($booking, $this->unread($booking, $user))));
    }

    public function driverEarnings(User $user): JsonResponse
    {
        $driver = $this->driver($user);
        $query = BookingEarning::whereHas('booking', fn ($q) => $q->where('DRIVER_ID', $driver->ID));
        $earned = (float) $query->sum('DRIVER_EARNING');
        $paid = (float) (clone $query)->where('PAYOUT_STATUS', 'PAID')->sum('DRIVER_EARNING');
        return response()->json(['totalEarned' => $earned, 'totalPaid' => $paid, 'pendingPayout' => $earned - $paid]);
    }

    public function updateAvailability(Request $request, User $user): JsonResponse
    {
        $driver = $this->driver($user);
        $data = $request->validate(['availability' => ['required', 'in:ONLINE,OFFLINE']]);
        $driver->update(['AVAILABILITY_STATUS' => $data['availability']]);
        return response()->json(['id' => $driver->ID, 'availability' => $driver->AVAILABILITY_STATUS]);
    }

    public function acceptJob(User $user, Booking $booking): JsonResponse
    {
        $driver = $this->driver($user);
        if ($booking->DRIVER_ID) $this->fail('This job has already been accepted.');
        if ($booking->BOOKING_STATUS !== 'PAID_WAITING_DRIVER') $this->fail('This job is not waiting for a driver.');
        $active = $driver->bookings()->whereNotIn('BOOKING_STATUS', ['COMPLETED', 'CANCELLED'])->count();
        if ($active >= (int) $this->setting('driver.max-active-jobs', 5)) $this->fail('Maximum active jobs reached.');
        $booking->update(['DRIVER_ID' => $driver->ID, 'BOOKING_STATUS' => 'ACCEPTED_BY_DRIVER']);
        $this->history($booking, 'ACCEPTED_BY_DRIVER', 'Driver accepted the job.', $user);
        $this->push->sendToUser($booking->customer?->user, 'CoolClean booking accepted', $user->FULL_NAME.' accepted '.$booking->BOOKING_NO.'.');
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function updateJobStatus(Request $request, User $user, Booking $booking): JsonResponse
    {
        $driver = $this->ownsDriverBooking($user, $booking);
        $data = $request->validate(['status' => ['required', 'string'], 'proofImage' => ['nullable', 'string', 'max:1500000']]);
        $sequence = ['ACCEPTED_BY_DRIVER' => 'DRIVER_GOING_TO_CUSTOMER', 'DRIVER_GOING_TO_CUSTOMER' => 'CLOTHES_COLLECTED', 'CLOTHES_COLLECTED' => 'ARRIVED_AT_LAUNDRY', 'ARRIVED_AT_LAUNDRY' => 'WASHING', 'WASHING' => 'DRYING', 'DRYING' => 'READY_FOR_DELIVERY', 'READY_FOR_DELIVERY' => 'RETURNING_TO_CUSTOMER', 'RETURNING_TO_CUSTOMER' => 'COMPLETED'];
        $next = $sequence[$booking->BOOKING_STATUS] ?? null;
        if (! $next || $data['status'] !== $next) $this->fail($next ? 'Next required status is '.$next.'.' : 'This job has no further driver progress.');
        $updates = ['BOOKING_STATUS' => $next];
        if (in_array($next, ['CLOTHES_COLLECTED', 'COMPLETED'], true)) {
            if (empty($data['proofImage']) || ! str_starts_with($data['proofImage'], 'data:image/')) $this->fail('A valid proof photo is required.');
            $updates[$next === 'CLOTHES_COLLECTED' ? 'PICKUP_PROOF_IMAGE' : 'DELIVERY_PROOF_IMAGE'] = $data['proofImage'];
            $updates[$next === 'CLOTHES_COLLECTED' ? 'PICKUP_PROOF_AT' : 'DELIVERY_PROOF_AT'] = now();
        }
        if ($next === 'COMPLETED') $updates['COMPLETED_AT'] = now();
        DB::transaction(function () use ($booking, $updates, $next, $user) {
            $booking->update($updates); $this->history($booking, $next, 'Updated from driver mobile app.', $user);
            if ($next === 'COMPLETED') $this->recordEarning($booking);
        });
        $this->push->sendToUser($booking->customer?->user, 'CoolClean status update', $booking->BOOKING_NO.' is now '.str_replace('_', ' ', $next).'.');
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function cancelDriverJob(Request $request, User $user, Booking $booking): JsonResponse
    {
        $driver = $this->ownsDriverBooking($user, $booking);
        $data = $request->validate(['remarks' => ['required', 'string', 'max:500']]);
        if (! in_array($booking->BOOKING_STATUS, ['ACCEPTED_BY_DRIVER', 'DRIVER_GOING_TO_CUSTOMER'], true)) $this->fail('Driver cancellation is only available before laundry is collected.');
        DB::transaction(function () use ($booking, $driver, $user, $data) {
            if ($booking->payment?->PAYMENT_STATUS === 'PAID') {
                $booking->payment->update(['PAYMENT_STATUS' => 'REFUNDED']);
                CustomerWalletTransaction::create(['CUSTOMER_ID' => $booking->CUSTOMER_ID, 'BOOKING_ID' => $booking->ID, 'TRANSACTION_TYPE' => 'CREDIT', 'AMOUNT' => $booking->TOTAL_AMOUNT, 'REMARKS' => 'Refund after driver cancellation.', 'CREATED_AT' => now()]);
            }
            $booking->update(['BOOKING_STATUS' => 'CANCELLED']);
            $this->history($booking, 'CANCELLED', 'Driver cancelled: '.$data['remarks'], $user);
        });
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function customerHistory(User $user, Booking $booking): JsonResponse { $this->ownsCustomerBooking($user, $booking); $booking->update(['CUSTOMER_MESSAGES_SEEN_AT' => now()]); return response()->json($this->historyResponse($booking)); }
    public function driverHistory(User $user, Booking $booking): JsonResponse { $this->ownsDriverBooking($user, $booking); $booking->update(['DRIVER_MESSAGES_SEEN_AT' => now()]); return response()->json($this->historyResponse($booking)); }
    public function customerMessage(Request $request, User $user, Booking $booking): JsonResponse { $customer=$this->ownsCustomerBooking($user,$booking); if(!$booking->DRIVER_ID)$this->fail('A driver must accept this booking before messages can be sent.'); return response()->json($this->message($request,$booking,$customer->user)); }
    public function driverMessage(Request $request, User $user, Booking $booking): JsonResponse { $this->ownsDriverBooking($user,$booking); return response()->json($this->message($request,$booking,$user)); }

    public function rateBooking(Request $request, User $user, Booking $booking): JsonResponse
    {
        $customer = $this->ownsCustomerBooking($user, $booking);
        if ($booking->BOOKING_STATUS !== 'COMPLETED') $this->fail('Rating is available after booking is completed.');
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'max:1000']]);
        $booking->update(['CUSTOMER_RATING' => $data['rating'], 'CUSTOMER_COMMENT' => $data['comment'] ?? null, 'RATED_AT' => now()]);
        $this->history($booking, 'COMPLETED', 'Customer rated driver '.$data['rating'].'/5.', $customer->user);
        return response()->json($this->bookingResponse($booking->fresh(['customer.user', 'driver.user'])));
    }

    public function updateProfile(Request $request, User $user): JsonResponse
    {
        if (! in_array($user->ROLE, ['CUSTOMER','DRIVER'], true)) $this->fail('Mobile profile is only available for customer and driver accounts.', 403);
        $data = $request->validate(['fullName'=>['required','string'],'phone'=>['nullable','string'],'defaultAddress'=>['nullable'],'defaultPostcode'=>['nullable'],'defaultCity'=>['nullable'],'defaultState'=>['nullable'],'vehicleType'=>['nullable'],'vehiclePlate'=>['nullable']]);
        $user->update(['FULL_NAME'=>$data['fullName'],'PHONE'=>$data['phone']??null]);
        if($user->ROLE==='CUSTOMER')$user->customer->update(['DEFAULT_ADDRESS'=>$data['defaultAddress']??null,'DEFAULT_POSTCODE'=>$data['defaultPostcode']??null,'DEFAULT_CITY'=>$data['defaultCity']??null,'DEFAULT_STATE'=>$data['defaultState']??null]);
        else $user->driver->update(['VEHICLE_TYPE'=>$data['vehicleType']??null,'VEHICLE_PLATE'=>$data['vehiclePlate']??null]);
        return response()->json(['id'=>$user->ID,'fullName'=>$user->FULL_NAME,'email'=>$user->EMAIL,'phone'=>$user->PHONE,'role'=>$user->ROLE,'status'=>$user->STATUS,'defaultAddress'=>$user->customer?->DEFAULT_ADDRESS,'defaultPostcode'=>$user->customer?->DEFAULT_POSTCODE,'defaultCity'=>$user->customer?->DEFAULT_CITY,'defaultState'=>$user->customer?->DEFAULT_STATE,'vehicleType'=>$user->driver?->VEHICLE_TYPE,'vehiclePlate'=>$user->driver?->VEHICLE_PLATE]);
    }

    public function registerDeviceToken(Request $request, User $user): JsonResponse { $data=$request->validate(['token'=>['required','string']]); $user->update(['FCM_TOKEN'=>$data['token'],'FCM_TOKEN_UPDATED_AT'=>now()]); return response()->json(['message'=>'Device token registered.']); }
    public function changePassword(Request $request, User $user): JsonResponse { $data=$request->validate(['currentPassword'=>['required'],'newPassword'=>['required','min:8'],'confirmPassword'=>['required','same:newPassword']]); if(!Hash::check($data['currentPassword'],$user->getAuthPassword()))$this->fail('Current password is not correct.'); $user->update(['PASSWORD_HASH'=>Hash::make($data['newPassword'])]); return response()->json(['message'=>'Password updated.']); }

    private function customer(User $user): Customer { if($user->ROLE!=='CUSTOMER'||$user->STATUS!=='ACTIVE')$this->fail('Customer account is not active.',403); return $user->customer ?? tap(null,fn()=>$this->fail('Customer profile was not found.',404)); }
    private function driver(User $user): Driver { if($user->ROLE!=='DRIVER'||$user->STATUS!=='ACTIVE')$this->fail('Driver account must be approved first.',403); return $user->driver ?? tap(null,fn()=>$this->fail('Driver profile was not found.',404)); }
    private function ownsCustomerBooking(User $user, Booking $booking): Customer { $customer=$this->customer($user); if($booking->CUSTOMER_ID!==$customer->ID)$this->fail('This booking does not belong to this customer.',403); return $customer; }
    private function ownsDriverBooking(User $user, Booking $booking): Driver { $driver=$this->driver($user); if($booking->DRIVER_ID!==$driver->ID)$this->fail('This job is not assigned to this driver.',403); return $driver; }
    private function history(Booking $booking,string $status,string $remarks,?User $user): BookingStatusHistory { return BookingStatusHistory::create(['BOOKING_ID'=>$booking->ID,'STATUS'=>$status,'REMARKS'=>$remarks,'UPDATED_BY_USER_ID'=>$user?->ID,'UPDATED_AT'=>now()]); }
    private function historyResponse(Booking $booking) { return $booking->statusHistory()->with('updatedBy')->oldest('UPDATED_AT')->get()->map(fn($h)=>['status'=>$h->STATUS,'remarks'=>$h->REMARKS,'updatedBy'=>$h->updatedBy?->FULL_NAME,'updatedByRole'=>$h->updatedBy?->ROLE,'updatedAt'=>$h->UPDATED_AT?->toISOString()]); }
    private function message(Request $request,Booking $booking,User $user): array { $data=$request->validate(['message'=>['required','string','max:500']]); $h=$this->history($booking,'MESSAGE',trim($data['message']),$user); return ['status'=>$h->STATUS,'remarks'=>$h->REMARKS,'updatedBy'=>$user->FULL_NAME,'updatedByRole'=>$user->ROLE,'updatedAt'=>$h->UPDATED_AT->toISOString()]; }
    private function unread(Booking $booking,User $user): int { $seen=$user->ROLE==='CUSTOMER'?$booking->CUSTOMER_MESSAGES_SEEN_AT:$booking->DRIVER_MESSAGES_SEEN_AT; return $booking->statusHistory()->where('STATUS','MESSAGE')->where('UPDATED_BY_USER_ID','!=',$user->ID)->when($seen,fn($q)=>$q->where('UPDATED_AT','>',$seen))->count(); }
    private function walletBalance(Customer $customer): float { $credit=(float)$customer->walletTransactions()->where('TRANSACTION_TYPE','CREDIT')->sum('AMOUNT'); $debit=(float)$customer->walletTransactions()->where('TRANSACTION_TYPE','DEBIT')->sum('AMOUNT'); return $credit-$debit; }
    private function setting(string $key,float $default): float { return (float)(SystemSetting::find($key)?->SETTING_VALUE ?? $default); }
    private function recordEarning(Booking $booking): void { if(BookingEarning::where('BOOKING_ID',$booking->ID)->exists())return; $driverPct=$this->setting('earning.driver-percentage',20); $deliveryPct=$this->setting('earning.driver-delivery-fee-percentage',100); $driver=round(((float)$booking->SERVICE_FEE*$driverPct/100)+((float)$booking->DELIVERY_FEE*$deliveryPct/100),2); BookingEarning::create(['BOOKING_ID'=>$booking->ID,'BOOKING_AMOUNT'=>$booking->TOTAL_AMOUNT,'DRIVER_PERCENTAGE'=>$driverPct,'ADMIN_PERCENTAGE'=>$this->setting('earning.admin-percentage',30),'DRIVER_EARNING'=>$driver,'ADMIN_EARNING'=>(float)$booking->TOTAL_AMOUNT-$driver,'EARNED_AT'=>now(),'PAYOUT_STATUS'=>'PENDING']); }
    private function distance(Driver $driver,Booking $booking): ?float { if($driver->CURRENT_LATITUDE===null||$booking->PICKUP_LATITUDE===null)return null; $lat1=deg2rad((float)$driver->CURRENT_LATITUDE);$lat2=deg2rad((float)$booking->PICKUP_LATITUDE);$dlat=$lat2-$lat1;$dlon=deg2rad((float)$booking->PICKUP_LONGITUDE-(float)$driver->CURRENT_LONGITUDE);$a=sin($dlat/2)**2+cos($lat1)*cos($lat2)*sin($dlon/2)**2;return round(6371*2*atan2(sqrt($a),sqrt(1-$a)),2); }
    private function bookingResponse(Booking $b,int $unread=0,?float $distance=null): array { return ['id'=>$b->ID,'bookingNo'=>$b->BOOKING_NO,'customerName'=>$b->customer?->user?->FULL_NAME,'driverName'=>$b->driver?->user?->FULL_NAME,'serviceSummary'=>$b->SERVICE_SUMMARY,'pickupAddress'=>$b->PICKUP_ADDRESS,'pickupPostcode'=>$b->PICKUP_POSTCODE,'pickupCity'=>$b->PICKUP_CITY,'pickupState'=>$b->PICKUP_STATE,'pickupLatitude'=>$b->PICKUP_LATITUDE===null?null:(float)$b->PICKUP_LATITUDE,'pickupLongitude'=>$b->PICKUP_LONGITUDE===null?null:(float)$b->PICKUP_LONGITUDE,'status'=>$b->BOOKING_STATUS,'laundryFee'=>(float)$b->LAUNDRY_FEE,'serviceFee'=>(float)$b->SERVICE_FEE,'deliveryFee'=>(float)$b->DELIVERY_FEE,'totalAmount'=>(float)$b->TOTAL_AMOUNT,'pickupDateTime'=>$b->PICKUP_DATE_TIME?->toISOString(),'createdAt'=>$b->CREATED_AT?->toISOString(),'completedAt'=>$b->COMPLETED_AT?->toISOString(),'aging'=>$b->CREATED_AT?->diffForHumans(),'customerRating'=>$b->CUSTOMER_RATING,'customerComment'=>$b->CUSTOMER_COMMENT,'unreadMessageCount'=>$unread,'distanceKm'=>$distance]; }
    private function fail(string $message,int $status=400): never { throw new HttpResponseException(response()->json(['message'=>$message],$status)); }
}
