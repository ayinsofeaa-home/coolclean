<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $customers = Customer::with('user')->get();

        return view('admin.customers', compact('customers'));
    }
}