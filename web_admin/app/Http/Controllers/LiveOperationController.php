<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Support\Facades\Session;

class LiveOperationController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $activeBookings = Booking::with(['customer.user', 'driver.user'])
            ->whereNotIn('BOOKING_STATUS', ['completed', 'cancelled'])
            ->orderBy('PICKUP_DATE_TIME', 'asc')
            ->get();

        $onlineDrivers = Driver::with('user')
            ->where('AVAILABILITY_STATUS', 'online')
            ->get();

        return view('admin.live-operations', compact('activeBookings', 'onlineDrivers'));
    }
}