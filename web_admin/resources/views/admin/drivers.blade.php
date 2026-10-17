@extends('layouts.admin')

@section('title', 'Drivers - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Driver Management</h1>
<p class="text-slate-500 mt-1 mb-6">View all registered drivers, their vehicles, and availability.</p>

@if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm font-medium">
        {{ session('status') }}
    </div>
@endif

@php
    $pendingDrivers = $drivers->filter(fn($d) => strtoupper($d->user->STATUS ?? '') === 'PENDING_APPROVAL');
    $otherDrivers = $drivers->reject(fn($d) => strtoupper($d->user->STATUS ?? '') === 'PENDING_APPROVAL');
@endphp

@if ($pendingDrivers->count() > 0)
<h2 class="text-lg font-semibold text-slate-700 mb-3">Pending Approval ({{ $pendingDrivers->count() }})</h2>
<div class="bg-white rounded-xl shadow overflow-hidden mb-8">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Name</th>
                <th class="py-3 px-5 font-semibold">Email</th>
                <th class="py-3 px-5 font-semibold">Phone</th>
                <th class="py-3 px-5 font-semibold">Vehicle</th>
                <th class="py-3 px-5 font-semibold">Service Address</th>
                <th class="py-3 px-5 font-semibold text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($pendingDrivers as $driver)
                <tr class="hover:bg-amber-50/50 transition">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold uppercase">
                                {{ substr($driver->user->FULL_NAME ?? '-', 0, 1) }}
                            </div>
                            <span class="font-medium text-slate-700">{{ $driver->user->FULL_NAME ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->user->EMAIL ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->user->PHONE ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->VEHICLE_TYPE ?? '-' }} ({{ $driver->VEHICLE_PLATE ?? '-' }})</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->SERVICE_ADDRESS ?? '-' }}, {{ $driver->SERVICE_CITY ?? '-' }}</td>
                    <td class="py-3 px-5 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.drivers.approve', $driver->user->ID) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-600 text-white hover:bg-green-700">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.drivers.reject', $driver->user->ID) }}" class="inline" onsubmit="return confirm('Reject this driver application?');">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Reject</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<h2 class="text-lg font-semibold text-slate-700 mb-3">All Drivers</h2>
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Name</th>
                <th class="py-3 px-5 font-semibold">Email</th>
                <th class="py-3 px-5 font-semibold">Phone</th>
                <th class="py-3 px-5 font-semibold">Status</th>
                <th class="py-3 px-5 font-semibold">Availability</th>
                <th class="py-3 px-5 font-semibold">Vehicle Type</th>
                <th class="py-3 px-5 font-semibold">Vehicle Plate</th>
                <th class="py-3 px-5 font-semibold">Service City</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($otherDrivers as $driver)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold uppercase">
                                {{ substr($driver->user->FULL_NAME ?? '-', 0, 1) }}
                            </div>
                            <span class="font-medium text-slate-700">{{ $driver->user->FULL_NAME ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->user->EMAIL ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->user->PHONE ?? '-' }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($driver->user->STATUS ?? '') === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $driver->user->STATUS ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($driver->AVAILABILITY_STATUS ?? '') === 'ONLINE' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ strtoupper($driver->AVAILABILITY_STATUS ?? '') === 'ONLINE' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                            {{ $driver->AVAILABILITY_STATUS ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->VEHICLE_TYPE ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->VEHICLE_PLATE ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $driver->SERVICE_CITY ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-8 text-center text-slate-400">No drivers found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection