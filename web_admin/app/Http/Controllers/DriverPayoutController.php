<?php

namespace App\Http\Controllers;

use App\Models\BookingEarning;
use Illuminate\Support\Facades\Session;

class DriverPayoutController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $payouts = BookingEarning::with('booking.driver.user')->orderBy('EARNED_AT', 'desc')->get();
        return view('admin.driver-payouts', compact('payouts'));
    }
}