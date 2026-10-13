<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('EMAIL', $request->email)->first();

        if (!$user || !password_verify($request->password, $user->PASSWORD_HASH)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        $role = strtolower($user->ROLE);
        if ($role !== 'admin' && $role !== 'super_admin') {
            return back()->withErrors([
                'email' => 'You are not authorized to access the admin panel.',
            ])->onlyInput('email');
        }

        Session::put('admin_id', $user->ID);
        Session::put('admin_name', $user->FULL_NAME);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Session::forget('admin_id');
        Session::forget('admin_name');
        return redirect()->route('login');
    }
}