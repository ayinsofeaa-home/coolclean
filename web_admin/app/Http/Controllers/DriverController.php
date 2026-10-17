<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\QueuedEmail;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class DriverController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $drivers = Driver::with('user')->get();

        return view('admin.drivers', compact('drivers'));
    }

    public function approve($id)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $user = User::where('ID', $id)->where('ROLE', 'DRIVER')->firstOrFail();
        $user->STATUS = 'ACTIVE';
        $user->save();

        QueuedEmail::create([
            'RECIPIENT' => $user->EMAIL,
            'SUBJECT' => 'CoolClean Driver Application Approved',
            'HTML_BODY' => '<p>Congratulations! Your driver application has been approved. You can now log in and start accepting jobs.</p>',
            'STATUS' => 'PENDING',
            'ATTEMPT_COUNT' => 0,
            'CREATED_AT' => now(),
        ]);

        return back()->with('status', $user->FULL_NAME.' has been approved.');
    }

    public function reject($id)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $user = User::where('ID', $id)->where('ROLE', 'DRIVER')->firstOrFail();
        $user->STATUS = 'REJECTED';
        $user->save();

        QueuedEmail::create([
            'RECIPIENT' => $user->EMAIL,
            'SUBJECT' => 'CoolClean Driver Application Update',
            'HTML_BODY' => '<p>We\'re sorry, your driver application was not approved at this time.</p>',
            'STATUS' => 'PENDING',
            'ATTEMPT_COUNT' => 0,
            'CREATED_AT' => now(),
        ]);

        return back()->with('status', $user->FULL_NAME.' has been rejected.');
    }
}