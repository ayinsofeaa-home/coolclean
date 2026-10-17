@extends('layouts.admin')

@section('title', 'Admin Users - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Admin Users</h1>
<p class="text-slate-500 mt-1 mb-6">Manage administrator accounts with system access.</p>

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

<div class="bg-white rounded-xl shadow overflow-hidden mb-6">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Name</th>
                <th class="py-3 px-5 font-semibold">Email</th>
                <th class="py-3 px-5 font-semibold">Phone</th>
                <th class="py-3 px-5 font-semibold">Type</th>
                <th class="py-3 px-5 font-semibold">Status</th>
                <th class="py-3 px-5 font-semibold">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($admins as $admin)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold uppercase">
                                {{ substr($admin->FULL_NAME, 0, 1) }}
                            </div>
                            <span class="font-medium text-slate-800">{{ $admin->FULL_NAME }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $admin->EMAIL }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $admin->PHONE ?? '-' }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($admin->ROLE) === 'SUPER_ADMIN' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ strtoupper($admin->ROLE) }}
                        </span>
                    </td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($admin->STATUS) === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ strtoupper($admin->STATUS) === 'ACTIVE' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                            {{ strtoupper($admin->STATUS) }}
                        </span>
                    </td>
                    <td class="py-3 px-5">
                        @if ($admin->ID == Session::get('admin_id'))
                            <span class="text-xs text-slate-400 italic">Not allowed</span>
                        @else
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.admin-users.edit', $admin->ID) }}"
                                   class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.admin-users.toggle-status', $admin->ID) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg text-white transition
                                                {{ strtoupper($admin->STATUS) === 'ACTIVE' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                        {{ strtoupper($admin->STATUS) === 'ACTIVE' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">No admin users found in database.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="bg-white rounded-xl shadow p-6 max-w-lg">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Create Backup Admin</h2>
    <form method="POST" action="{{ route('admin.admin-users.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}"
                   class="w-full border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Temporary Password</label>
            <input type="password" name="password"
                   class="w-full border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required minlength="8">
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Admin Type</label>
            <select name="admin_type" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
        </div>

        <p class="text-sm text-slate-400 mb-4">
            Password must be at least 8 characters and contain uppercase, lowercase, and number. New admin must change it on first login.
        </p>

        <button type="submit"
                class="w-full bg-slate-900 text-white py-3 rounded-lg font-semibold hover:bg-slate-800 transition">
            Create Admin
        </button>
    </form>
</div>
@endsection