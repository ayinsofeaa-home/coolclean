<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\QueuedEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    private const FALLBACK_OTP = '123456';

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower(trim($data['email']));
        if (User::where('EMAIL', $email)->exists()) {
            return $this->error('Email already exists.');
        }
        $this->createOtp($email);

        return response()->json(['message' => 'OTP has been sent to your email.']);
    }

    public function sendForgotPasswordOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower(trim($data['email']));
        if (! User::where('EMAIL', $email)->exists()) {
            return $this->error('Email was not found.');
        }
        $this->createOtp($email);

        return response()->json(['message' => 'Password reset OTP has been sent to your email.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'], 'otp' => ['required', 'string'],
            'newPassword' => ['required', 'string'], 'confirmPassword' => ['required', 'same:newPassword'],
        ]);
        if (! $this->validPassword($data['newPassword'])) {
            return $this->error('Password must be at least 8 characters and contain uppercase, lowercase, and number.');
        }
        $email = strtolower(trim($data['email']));
        if (! $this->consumeOtp($email, $data['otp'])) {
            return $this->error('Invalid or expired OTP.');
        }
        $user = User::where('EMAIL', $email)->first();
        if (! $user) {
            return $this->error('Email was not found.');
        }
        $user->update(['PASSWORD_HASH' => Hash::make($data['newPassword'])]);

        return response()->json(['message' => 'Password has been reset.']);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('EMAIL', strtolower($data['email']))->first();
        if (! $user || ($user->LOCKED_UNTIL && $user->LOCKED_UNTIL->isFuture())) {
            return $this->error($user ? 'Too many failed login attempts. Please try again later.' : 'Invalid email or password.', $user ? 403 : 401);
        }
        if (! Hash::check($data['password'], $user->getAuthPassword())) {
            $attempts = $user->FAILED_LOGIN_ATTEMPTS + 1;
            $user->update(['FAILED_LOGIN_ATTEMPTS' => $attempts, 'LOCKED_UNTIL' => $attempts >= 5 ? now()->addMinutes(15) : null]);
            return $this->error('Invalid email or password.', 401);
        }
        $user->update(['FAILED_LOGIN_ATTEMPTS' => 0, 'LOCKED_UNTIL' => null]);
        if (in_array($user->ROLE, ['ADMIN', 'SUPER_ADMIN'], true)) {
            return $this->error('Admin users must use the web portal.', 403);
        }
        if (in_array($user->STATUS, ['INACTIVE', 'REJECTED'], true)) {
            return $this->error('This account is not active.', 403);
        }
        $user->load(['customer', 'driver']);

        return response()->json($this->authResponse($user));
    }

    public function registerCustomer(Request $request): JsonResponse
    {
        $data = $this->validateRegistration($request, false);
        if ($data instanceof JsonResponse) {
            return $data;
        }
        $user = DB::transaction(function () use ($data) {
            $user = $this->createUser($data, 'CUSTOMER', 'ACTIVE');
            Customer::create([
                'USER_ID' => $user->ID, 'DEFAULT_ADDRESS' => $data['defaultAddress'] ?? null,
                'DEFAULT_POSTCODE' => $data['defaultPostcode'] ?? null, 'DEFAULT_CITY' => $data['defaultCity'] ?? null,
                'DEFAULT_STATE' => $data['defaultState'] ?? null, 'DEFAULT_LATITUDE' => $data['defaultLatitude'] ?? null,
                'DEFAULT_LONGITUDE' => $data['defaultLongitude'] ?? null,
            ]);
            return $user;
        });
        $user->load('customer');

        return response()->json($this->authResponse($user));
    }

    public function registerDriver(Request $request): JsonResponse
    {
        $data = $this->validateRegistration($request, true);
        if ($data instanceof JsonResponse) {
            return $data;
        }
        $user = DB::transaction(function () use ($data) {
            $user = $this->createUser($data, 'DRIVER', 'PENDING_APPROVAL');
            Driver::create([
                'USER_ID' => $user->ID, 'VEHICLE_TYPE' => $data['vehicleType'] ?? null,
                'VEHICLE_PLATE' => $data['vehiclePlate'] ?? null, 'SERVICE_ADDRESS' => $data['serviceAddress'] ?? null,
                'SERVICE_POSTCODE' => $data['servicePostcode'] ?? null, 'SERVICE_CITY' => $data['serviceCity'] ?? null,
                'SERVICE_STATE' => $data['serviceState'] ?? null, 'CURRENT_LATITUDE' => $data['serviceLatitude'] ?? null,
                'CURRENT_LONGITUDE' => $data['serviceLongitude'] ?? null, 'AVAILABILITY_STATUS' => 'OFFLINE',
            ]);
            return $user;
        });
        $user->load('driver');

        return response()->json($this->authResponse($user));
    }

    private function validateRegistration(Request $request, bool $driver): array|JsonResponse
    {
        $rules = [
            'fullName' => ['required', 'string'], 'email' => ['required', 'email'], 'phone' => ['nullable', 'string'],
            'otp' => ['required', 'string'], 'password' => ['required', 'string'], 'confirmPassword' => ['required', 'same:password'],
        ];
        foreach ($driver ? ['vehicleType','vehiclePlate','serviceAddress','servicePostcode','serviceCity','serviceState','serviceLatitude','serviceLongitude'] : ['defaultAddress','defaultPostcode','defaultCity','defaultState','defaultLatitude','defaultLongitude'] as $field) {
            $rules[$field] = ['nullable'];
        }
        $data = $request->validate($rules);
        $data['email'] = strtolower(trim($data['email']));
        if (User::where('EMAIL', $data['email'])->exists()) return $this->error('Email already exists.');
        if (! $this->validPassword($data['password'])) return $this->error('Password must be at least 8 characters and contain uppercase, lowercase, and number.');
        if (! $this->consumeOtp($data['email'], $data['otp'])) return $this->error('Invalid or expired OTP.');
        return $data;
    }

    private function createUser(array $data, string $role, string $status): User
    {
        $user = User::create([
            'FULL_NAME' => $data['fullName'], 'EMAIL' => $data['email'], 'PHONE' => $data['phone'] ?? null,
            'ROLE' => $role, 'STATUS' => $status, 'PASSWORD_HASH' => Hash::make($data['password']),
            'PASSWORD_CHANGE_REQUIRED' => false, 'FAILED_LOGIN_ATTEMPTS' => 0, 'CREATED_AT' => now(),
        ]);
        $this->queueEmail($user->EMAIL, 'Welcome to CoolClean', 'Your CoolClean registration is complete.');
        return $user;
    }

    private function authResponse(User $user): array
    {
        $profile = $user->ROLE === 'CUSTOMER' ? $user->customer : $user->driver;
        return [
            'id' => $user->ID, 'fullName' => $user->FULL_NAME, 'email' => $user->EMAIL,
            'phone' => $user->PHONE, 'role' => $user->ROLE, 'status' => $user->STATUS,
            'defaultAddress' => $profile?->{($user->ROLE === 'CUSTOMER' ? 'DEFAULT_ADDRESS' : 'SERVICE_ADDRESS')},
            'defaultPostcode' => $profile?->{($user->ROLE === 'CUSTOMER' ? 'DEFAULT_POSTCODE' : 'SERVICE_POSTCODE')},
            'defaultCity' => $profile?->{($user->ROLE === 'CUSTOMER' ? 'DEFAULT_CITY' : 'SERVICE_CITY')},
            'defaultState' => $profile?->{($user->ROLE === 'CUSTOMER' ? 'DEFAULT_STATE' : 'SERVICE_STATE')},
            'vehicleType' => $user->driver?->VEHICLE_TYPE, 'vehiclePlate' => $user->driver?->VEHICLE_PLATE,
            'driverAvailability' => $user->driver?->AVAILABILITY_STATUS,
        ];
    }

    private function createOtp(string $email): void
    {
        Cache::store('file')->put('otp:'.$email, self::FALLBACK_OTP, now()->addMinutes(5));
        $this->queueEmail($email, 'CoolClean OTP', 'Your OTP is '.self::FALLBACK_OTP.'. It expires in 5 minutes.');
    }

    private function consumeOtp(string $email, string $otp): bool
    {
        $key = 'otp:'.$email;
        if (Cache::store('file')->get($key) !== $otp) return false;
        Cache::store('file')->forget($key);
        return true;
    }

    private function queueEmail(string $email, string $subject, string $body): void
    {
        QueuedEmail::create(['RECIPIENT' => $email, 'SUBJECT' => $subject, 'HTML_BODY' => '<p>'.e($body).'</p>', 'STATUS' => 'PENDING', 'ATTEMPT_COUNT' => 0, 'CREATED_AT' => now()]);
    }

    private function validPassword(string $password): bool
    {
        return strlen($password) >= 8 && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/\d/', $password);
    }

    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
