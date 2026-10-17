<?php

namespace App\Http\Controllers;

use App\Models\LaundryLocation;
use Illuminate\Support\Facades\Session;

class LaundryLocationController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $locations = LaundryLocation::all();
        return view('admin.laundry-locations', compact('locations'));
    }
}