<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChangePasswordController extends Controller
{
    public function show()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        return view('admin.change-password');
    }

    public function update(Request $request)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::find(Session::get('admin_id'));

        if (!$user || !password_verify($request->current_password, $user->PASSWORD_HASH)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->PASSWORD_HASH = bcrypt($request->new_password);
        $user->save();

        return back()->with('status', 'Password updated successfully.');
    }
}