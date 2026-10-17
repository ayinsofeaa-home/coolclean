@extends('layouts.admin')

@section('title', 'Live Operations - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Live Operations</h1>
<p class="text-slate-500 mt-1 mb-6">Snapshot of active bookings and drivers currently online. Refresh the page to update.</p>

<div class="grid grid-cols-1 gap-6">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 pb-0"><h2 class="font-bold text-lg text-slate-800 mb-4">Active Bookings</h2></div>
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-y border-slate-100">
                    <th class="py-2.5 px-5 font-semibold">Booking</th>
                    <th class="py-2.5 px-5 font-semibold">Customer</th>
                    <th class="py-2.5 px-5 font-semibold">Driver</th>
                    <th class="py-2.5 px-5 font-semibold">Pickup Time</th>
                    <th class="py-2.5 px-5 font-semibold">Pickup Address</th>
                    <th class="py-2.5 px-5 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($activeBookings as $booking)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-5 font-medium text-slate-700">{{ $booking->BOOKING_NO }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $booking->customer->user->FULL_NAME ?? '-' }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $booking->driver->user->FULL_NAME ?? 'Unassigned' }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $booking->PICKUP_DATE_TIME }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $booking->PICKUP_ADDRESS }}, {{ $booking->PICKUP_CITY }}</td>
                        <td class="py-2.5 px-5">
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                {{ $booking->BOOKING_STATUS }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">No active bookings right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 pb-0"><h2 class="font-bold text-lg text-slate-800 mb-4">Drivers Online</h2></div>
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead>
                <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-y border-slate-100">
                    <th class="py-2.5 px-5 font-semibold">Name</th>
                    <th class="py-2.5 px-5 font-semibold">Phone</th>
                    <th class="py-2.5 px-5 font-semibold">Current Latitude</th>
                    <th class="py-2.5 px-5 font-semibold">Current Longitude</th>
                    <th class="py-2.5 px-5 font-semibold">Vehicle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($onlineDrivers as $driver)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold uppercase">
                                    {{ substr($driver->user->FULL_NAME ?? '-', 0, 1) }}
                                </div>
                                <span class="font-medium text-slate-700">{{ $driver->user->FULL_NAME ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $driver->user->PHONE ?? '-' }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $driver->CURRENT_LATITUDE ?? '-' }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $driver->CURRENT_LONGITUDE ?? '-' }}</td>
                        <td class="py-2.5 px-5 text-slate-500">{{ $driver->VEHICLE_TYPE ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-400">No drivers currently online.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection