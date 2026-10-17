<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminUserController extends Controller
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $admins = User::where('ROLE', 'admin')->orWhere('ROLE', 'SUPER_ADMIN')->get();
        return view('admin.admin-users', compact('admins'));
    }

    public function store(Request $request)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:USERS,EMAIL',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|min:8',
            'admin_type' => 'required|in:admin,super_admin',
        ]);

        User::create([
            'FULL_NAME' => $request->full_name,
            'EMAIL' => $request->email,
            'PHONE' => $request->phone,
            'PASSWORD_HASH' => bcrypt($request->password),
            'ROLE' => strtoupper($request->admin_type),
            'STATUS' => 'ACTIVE',
            'PASSWORD_CHANGE_REQUIRED' => 1,
        ]);

        return back()->with('status', 'Admin account created successfully.');
    }

    public function edit($id)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $admin = User::findOrFail($id);
        return view('admin.admin-users-edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        $admin = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:USERS,EMAIL,' . $admin->ID . ',ID',
            'phone' => 'nullable|string|max:50',
            'admin_type' => 'required|in:admin,super_admin',
        ]);

        $admin->FULL_NAME = $request->full_name;
        $admin->EMAIL = $request->email;
        $admin->PHONE = $request->phone;
        $admin->ROLE = strtoupper($request->admin_type);
        $admin->save();

        return redirect()->route('admin.admin-users')->with('status', 'Admin updated successfully.');
    }

    public function toggleStatus($id)
    {
        if (!Session::has('admin_id')) {
            return redirect()->route('login');
        }

        // Prevent an admin from deactivating their own account
        if ((int) $id === (int) Session::get('admin_id')) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $admin = User::findOrFail($id);
        $admin->STATUS = strtoupper($admin->STATUS) === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $admin->save();

        return back()->with('status', 'Admin status updated.');
    }
}