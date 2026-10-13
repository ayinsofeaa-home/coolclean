@extends('layouts.admin')

@section('title', 'Edit Admin - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Edit Admin</h1>
<p class="text-slate-500 mt-1 mb-6">Update admin profile and admin type. Password is not editable here.</p>

@if ($errors->any())
    <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4 max-w-lg">
        {{ $errors->first() }}
    </div>
@endif

<div class="bg-white rounded-xl shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('admin.admin-users.update', $admin->ID) }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" name="full_name" value="{{ old('full_name', $admin->FULL_NAME) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $admin->EMAIL) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $admin->PHONE) }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Admin Type</label>
            <select name="admin_type" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="admin" {{ strtoupper($admin->ROLE) === 'ADMIN' ? 'selected' : '' }}>Admin</option>
                <option value="super_admin" {{ strtoupper($admin->ROLE) === 'SUPER_ADMIN' ? 'selected' : '' }}>Super Admin</option>
            </select>
        </div>

        <p class="text-sm text-slate-400 mb-4">
            Password changes are handled only by the admin from Change Password.
        </p>

        <button type="submit"
                class="w-full bg-slate-900 text-white py-3 rounded-lg font-semibold hover:bg-slate-800 transition">
            Save Changes
        </button>
    </form>
</div>
@endsection