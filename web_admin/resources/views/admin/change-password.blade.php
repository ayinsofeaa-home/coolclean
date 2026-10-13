@extends('layouts.admin')

@section('title', 'Change Password - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Change Password</h1>
<p class="text-slate-500 mt-1 mb-6">Update your admin account password.</p>

<div class="bg-white rounded-xl shadow p-6 max-w-md">
    @if (session('status'))
        <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.change-password.update') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
            <input type="password" name="current_password"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
            <input type="password" name="new_password"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required minlength="8">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
            <input type="password" name="new_password_confirmation"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required minlength="8">
        </div>
        <button type="submit"
                class="w-full bg-slate-900 text-white py-3 rounded-lg font-semibold hover:bg-slate-800 transition">
            Update Password
        </button>
    </form>
</div>
@endsection