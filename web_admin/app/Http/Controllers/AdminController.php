<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingEarning;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\LaundryLocation;
use App\Models\LaundryService;
use App\Models\Payment;
use App\Models\QueuedEmail;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\FirebasePushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private readonly FirebasePushService $push)
    {
    }

    public function dashboard(): View
    {
        $drivers = Driver::with('user')->withCount('bookings')->get();

        return view('admin.dashboard', [
            'bookingCount' => Booking::count(),
            'waitingDriverCount' => Booking::where('BOOKING_STATUS', 'PAID_WAITING_DRIVER')->count(),
            'customerCount' => Customer::count(),
            'driverCount' => Driver::count(),
            'activeDriverCount' => Driver::where('AVAILABILITY_STATUS', 'ONLINE')->count(),
            'paidTotal' => Payment::where('PAYMENT_STATUS', 'PAID')->sum('AMOUNT'),
            'bookingGrandTotal' => Booking::sum('TOTAL_AMOUNT'),
            'paymentGrandTotal' => Payment::sum('AMOUNT'),
            'driverEarningTotal' => BookingEarning::sum('DRIVER_EARNING'),
            'adminEarningTotal' => BookingEarning::sum('ADMIN_EARNING'),
            'drivers' => $drivers,
            'recentBookings' => Booking::with(['customer.user', 'driver.user'])
                ->latest('CREATED_AT')->limit(8)->get(),
        ]);
    }

    public function bookings(): View
    {
        return view('admin.bookings', [
            'bookings' => Booking::with(['customer.user', 'driver.user', 'payment'])
                ->latest('CREATED_AT')->paginate(20),
        ]);
    }

    public function booking(Booking $booking): View
    {
        $booking->load([
            'customer.user', 'driver.user', 'laundryLocation',
            'payment', 'earning', 'statusHistory.updatedBy',
        ]);

        return view('admin.booking-detail', compact('booking'));
    }

    public function customers(): View
    {
        return view('admin.customers', [
            'customers' => Customer::with('user')->withCount('bookings')->paginate(20),
        ]);
    }

    public function drivers(): View
    {
        return view('admin.drivers', [
            'drivers' => Driver::with('user')->withCount('bookings')->paginate(20),
        ]);
    }

    public function services(): View
    {
        return view('admin.services', [
            'services' => LaundryService::orderBy('SERVICE_NAME')->get(),
        ]);
    }

    public function laundryLocations(): View
    {
        return view('admin.laundry-locations', [
            'locations' => LaundryLocation::orderBy('NAME')->get(),
        ]);
    }

    public function payments(): View
    {
        return view('admin.payments', [
            'payments' => Payment::with('booking.customer.user')->latest('ID')->paginate(20),
        ]);
    }

    public function payouts(): View
    {
        $drivers = Driver::with('user')->get()->map(function (Driver $driver) {
            $earnings = BookingEarning::whereHas('booking', fn ($query) => $query->where('DRIVER_ID', $driver->ID));
            $total = (float) (clone $earnings)->sum('DRIVER_EARNING');
            $paid = (float) (clone $earnings)->where('PAYOUT_STATUS', 'PAID')->sum('DRIVER_EARNING');

            return [
                'driver' => $driver,
                'total' => $total,
                'paid' => $paid,
                'pending' => $total - $paid,
                'lastPaidAt' => (clone $earnings)->where('PAYOUT_STATUS', 'PAID')->max('PAID_AT'),
            ];
        });

        return view('admin.payouts', [
            'drivers' => $drivers,
        ]);
    }

    public function reports(): View
    {
        return view('admin.reports', [
            'paidRevenue' => Payment::where('PAYMENT_STATUS', 'PAID')->sum('AMOUNT'),
            'refundedTotal' => Payment::where('PAYMENT_STATUS', 'REFUNDED')->sum('AMOUNT'),
            'driverEarnings' => BookingEarning::sum('DRIVER_EARNING'),
            'adminEarnings' => BookingEarning::sum('ADMIN_EARNING'),
            'completedBookings' => Booking::where('BOOKING_STATUS', 'COMPLETED')->count(),
        ]);
    }

    public function admins(): View
    {
        return view('admin.admins', [
            'admins' => User::whereIn('ROLE', ['ADMIN', 'SUPER_ADMIN'])->orderBy('FULL_NAME')->get(),
        ]);
    }

    public function operations(): View
    {
        $settings = SystemSetting::pluck('SETTING_VALUE', 'SETTING_KEY');
        return view('admin.operations', compact('settings'));
    }

    public function updateOperations(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'max_active_jobs' => ['required', 'integer', 'between:1,20'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'driver_percentage' => ['required', 'numeric', 'between:0,100'],
            'driver_delivery_percentage' => ['required', 'numeric', 'between:0,100'],
        ]);
        $values = [
            'driver.max-active-jobs' => $data['max_active_jobs'],
            'pricing.service-fee' => $data['service_fee'],
            'pricing.delivery-fee' => $data['delivery_fee'],
            'earning.driver-percentage' => $data['driver_percentage'],
            'earning.admin-percentage' => 100 - $data['driver_percentage'],
            'earning.driver-delivery-fee-percentage' => $data['driver_delivery_percentage'],
        ];
        foreach ($values as $key => $value) {
            SystemSetting::updateOrCreate(['SETTING_KEY' => $key], ['SETTING_VALUE' => $value]);
        }
        return back()->with('success', 'Operation settings updated.');
    }

    public function approveDriver(Driver $driver): RedirectResponse
    {
        $driver->user()->update(['STATUS' => 'ACTIVE']);
        $driver->update(['REJECTION_REMARKS' => null, 'AVAILABILITY_STATUS' => 'OFFLINE']);
        $this->queueEmail($driver->user, 'Driver application approved', 'Your CoolClean driver account has been approved.');
        $this->push->sendToUser($driver->user, 'CoolClean driver approved', 'Your driver account has been approved. You can now receive jobs.');

        return back()->with('success', 'Driver approved.');
    }

    public function rejectDriver(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['required', 'string', 'max:1000']]);
        $driver->user()->update(['STATUS' => 'REJECTED']);
        $driver->update(['REJECTION_REMARKS' => $data['remarks'], 'AVAILABILITY_STATUS' => 'OFFLINE']);
        $this->queueEmail($driver->user, 'Driver application update', 'Your application was rejected: '.$data['remarks']);
        $this->push->sendToUser($driver->user, 'CoolClean driver application update', 'Your application was not approved. Remarks: '.$data['remarks']);

        return back()->with('success', 'Driver rejected.');
    }

    public function updateService(Request $request, LaundryService $service): RedirectResponse
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $service->update(['PRICE' => $data['price'], 'STATUS' => $data['status']]);

        return back()->with('success', 'Service updated.');
    }

    public function markPayoutPaid(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate(['reference' => ['nullable', 'string', 'max:255']]);
        $reference = $data['reference'] ?: 'PAYOUT-'.$driver->ID.'-'.now()->toDateString();
        $earnings = BookingEarning::whereHas('booking', fn ($query) => $query->where('DRIVER_ID', $driver->ID))
            ->where('PAYOUT_STATUS', 'PENDING');
        $amount = (float) (clone $earnings)->sum('DRIVER_EARNING');

        if ($amount <= 0) {
            return back()->withErrors(['reference' => 'No pending payout was found for this driver.']);
        }

        $earnings->update([
            'PAYOUT_STATUS' => 'PAID',
            'PAYOUT_REFERENCE' => $reference,
            'PAID_AT' => now(),
        ]);

        return back()->with('success', 'Driver payout marked as paid: RM '.number_format($amount, 2));
    }

    public function createAdmin(Request $request): RedirectResponse
    {
        abort_unless($request->user()->ROLE === 'SUPER_ADMIN', 403);
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,EMAIL'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:ADMIN,SUPER_ADMIN'],
        ]);
        User::create([
            'FULL_NAME' => $data['full_name'],
            'EMAIL' => strtolower($data['email']),
            'PASSWORD_HASH' => Hash::make($data['password']),
            'ROLE' => $data['role'],
            'STATUS' => 'ACTIVE',
            'PASSWORD_CHANGE_REQUIRED' => true,
            'FAILED_LOGIN_ATTEMPTS' => 0,
            'CREATED_AT' => now(),
        ]);

        return back()->with('success', 'Administrator created.');
    }

    public function editAdmin(Request $request, User $admin): View
    {
        abort_unless($request->user()->ROLE === 'SUPER_ADMIN', 403);
        abort_unless(in_array($admin->ROLE, ['ADMIN', 'SUPER_ADMIN'], true), 404);

        return view('admin.admin-edit', compact('admin'));
    }

    public function updateAdmin(Request $request, User $admin): RedirectResponse
    {
        abort_unless($request->user()->ROLE === 'SUPER_ADMIN', 403);
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,EMAIL,'.$admin->ID.',ID'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:ADMIN,SUPER_ADMIN'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
        $admin->update([
            'FULL_NAME' => $data['full_name'], 'EMAIL' => strtolower($data['email']),
            'PHONE' => $data['phone'], 'ROLE' => $data['role'], 'STATUS' => $data['status'],
        ]);

        return redirect()->route('admin.admins')->with('success', 'Administrator updated.');
    }

    public function passwordForm(): View
    {
        return view('admin.change-password');
    }

    public function updateAdminStatus(Request $request, User $admin): RedirectResponse
    {
        abort_unless($request->user()->ROLE === 'SUPER_ADMIN', 403);
        $data = $request->validate(['status' => ['required', 'in:ACTIVE,INACTIVE']]);
        $admin->update(['STATUS' => $data['status']]);

        return back()->with('success', 'Administrator status updated.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->getAuthPassword())) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['PASSWORD_HASH' => Hash::make($data['new_password']), 'PASSWORD_CHANGE_REQUIRED' => false]);

        return back()->with('success', 'Password changed.');
    }

    private function queueEmail(User $user, string $subject, string $message): void
    {
        QueuedEmail::create([
            'RECIPIENT' => $user->EMAIL,
            'SUBJECT' => $subject,
            'HTML_BODY' => '<p>'.e($message).'</p>',
            'STATUS' => 'PENDING',
            'ATTEMPT_COUNT' => 0,
            'CREATED_AT' => now(),
        ]);
    }
}
