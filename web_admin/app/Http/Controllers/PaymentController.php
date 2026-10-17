<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $payments = Payment::with('booking')->orderBy('PAID_AT', 'desc')->get();
        return view('admin.payments', compact('payments'));
    }
}