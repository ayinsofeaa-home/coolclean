<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $bookings = Booking::with(['customer.user', 'driver.user', 'earning'])
            ->orderBy('CREATED_AT', 'desc')
            ->get();

        return view('admin.bookings', compact('bookings'));
    }
}