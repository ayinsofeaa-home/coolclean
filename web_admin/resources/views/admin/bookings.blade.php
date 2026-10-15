@extends('layouts.admin')

@section('title', 'Bookings - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Booking Monitoring</h1>
<p class="text-slate-500 mt-1 mb-6">View every customer booking and its current progress.</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left whitespace-nowrap">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Booking</th>
                <th class="py-3 px-5 font-semibold">Customer</th>
                <th class="py-3 px-5 font-semibold">Driver</th>
                <th class="py-3 px-5 font-semibold">Pickup</th>
                <th class="py-3 px-5 font-semibold">Status</th>
                <th class="py-3 px-5 font-semibold">Aging</th>
                <th class="py-3 px-5 font-semibold">Rating</th>
                <th class="py-3 px-5 font-semibold">Amount</th>
                <th class="py-3 px-5 font-semibold">Driver Earned</th>
                <th class="py-3 px-5 font-semibold">Admin Earned</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($bookings as $booking)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5 font-medium text-slate-700">{{ $booking->BOOKING_NO }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $booking->customer->user->FULL_NAME ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $booking->driver->user->FULL_NAME ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $booking->PICKUP_ADDRESS }}, {{ $booking->PICKUP_CITY }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                            {{ $booking->BOOKING_STATUS }}
                        </span>
                    </td>
                    <td class="py-3 px-5 text-slate-500">
                        {{ $booking->CREATED_AT ? \Carbon\Carbon::parse($booking->CREATED_AT)->diffForHumans(null, true) : '-' }}
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $booking->CUSTOMER_RATING ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-700">RM {{ number_format($booking->TOTAL_AMOUNT, 2) }}</td>
                    <td class="py-3 px-5 text-slate-500">RM {{ number_format($booking->earning->DRIVER_EARNING ?? 0, 2) }}</td>
                    <td class="py-3 px-5 text-slate-500">RM {{ number_format($booking->earning->ADMIN_EARNING ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="py-8 text-center text-slate-400">No bookings found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection