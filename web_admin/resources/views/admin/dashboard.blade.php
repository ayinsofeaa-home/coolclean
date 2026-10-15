@extends('layouts.admin')

@section('title', 'Dashboard - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Operations Dashboard</h1>
<p class="text-slate-500 mt-1 mb-6">Monitor bookings, drivers, payment status, and service progress.</p>

<div class="grid grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Total Bookings</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalBookings }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Waiting Driver</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ $waitingDriver }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Online Drivers</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ $onlineDrivers }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Completed Revenue</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($completedRevenue, 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Booking Grand Total</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($bookingGrandTotal, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Payment Grand Total</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($paymentGrandTotal, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Completed Booking Cost</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($completedBookingCost, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase">Driver Earned</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($driverEarned, 2) }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ number_format($driverPercentage, 2) }}%</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow p-5 mb-6 w-1/4">
    <p class="text-xs font-semibold text-slate-400 uppercase">Admin Earned</p>
    <p class="text-3xl font-bold text-slate-800 mt-2">RM {{ number_format($adminEarned, 2) }}</p>
    <p class="text-xs text-slate-400 mt-1">{{ number_format($adminPercentage, 2) }}%</p>
</div>

<div class="grid grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 pb-0">
            <h2 class="font-bold text-lg text-slate-800 mb-4">Recent Bookings</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-y border-slate-100">
                        <th class="py-2.5 px-4 font-semibold">Booking</th>
                        <th class="py-2.5 px-4 font-semibold">Customer</th>
                        <th class="py-2.5 px-4 font-semibold">Driver</th>
                        <th class="py-2.5 px-4 font-semibold">Status</th>
                        <th class="py-2.5 px-4 font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentBookings as $booking)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-medium text-slate-700">{{ $booking->BOOKING_NO }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $booking->customer->user->FULL_NAME ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $booking->driver->user->FULL_NAME ?? '-' }}</td>
                            <td class="py-2.5 px-4">
                                <span class="inline-block px-2 py-1 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-700 whitespace-nowrap">
                                    {{ $booking->BOOKING_STATUS }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-slate-700">RM {{ number_format($booking->TOTAL_AMOUNT, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">No active bookings in database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-5 pb-0">
            <h2 class="font-bold text-lg text-slate-800 mb-4">Driver Monitoring</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-y border-slate-100">
                        <th class="py-2.5 px-4 font-semibold">Driver</th>
                        <th class="py-2.5 px-4 font-semibold">Availability</th>
                        <th class="py-2.5 px-4 font-semibold">Vehicle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($drivers as $driver)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold uppercase shrink-0">
                                        {{ substr($driver->user->FULL_NAME ?? '-', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-slate-700">{{ $driver->user->FULL_NAME ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 text-[10px] font-semibold rounded-full whitespace-nowrap
                                    {{ strtoupper($driver->AVAILABILITY_STATUS ?? '') === 'ONLINE' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ strtoupper($driver->AVAILABILITY_STATUS ?? '') === 'ONLINE' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                    {{ $driver->AVAILABILITY_STATUS }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $driver->VEHICLE_TYPE }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">No drivers found in database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection