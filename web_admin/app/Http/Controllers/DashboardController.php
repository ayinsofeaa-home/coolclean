<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\BookingEarning;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $totalBookings = Booking::count();
        $waitingDriver = Booking::where('BOOKING_STATUS', 'PAID_WAITING_DRIVER')->count();
        $onlineDrivers = Driver::where('AVAILABILITY_STATUS', 'ONLINE')->count();

        $bookingGrandTotal = Booking::sum('TOTAL_AMOUNT');
        $paymentGrandTotal = Payment::where('PAYMENT_STATUS', 'PAID')->sum('AMOUNT');
        $completedBookingCost = Booking::where('BOOKING_STATUS', 'COMPLETED')->sum('TOTAL_AMOUNT');
        $completedRevenue = $completedBookingCost;

        $driverEarned = BookingEarning::sum('DRIVER_EARNING');
        $adminEarned = BookingEarning::sum('ADMIN_EARNING');
        $driverPercentage = BookingEarning::avg('DRIVER_PERCENTAGE') ?? 0;
        $adminPercentage = BookingEarning::avg('ADMIN_PERCENTAGE') ?? 0;

        $recentBookings = Booking::with(['customer.user', 'driver.user'])
            ->orderBy('CREATED_AT', 'desc')
            ->take(5)
            ->get();

        $drivers = Driver::with('user')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalBookings',
            'waitingDriver',
            'onlineDrivers',
            'completedRevenue',
            'bookingGrandTotal',
            'paymentGrandTotal',
            'completedBookingCost',
            'driverEarned',
            'adminEarned',
            'driverPercentage',
            'adminPercentage',
            'recentBookings',
            'drivers'
        ));
    }
}