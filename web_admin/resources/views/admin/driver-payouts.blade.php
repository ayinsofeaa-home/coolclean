@extends('layouts.admin')

@section('title', 'Driver Payouts - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Driver Payouts</h1>
<p class="text-slate-500 mt-1 mb-6">Track earnings and payout status for each driver.</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left whitespace-nowrap">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Booking</th>
                <th class="py-3 px-5 font-semibold">Driver</th>
                <th class="py-3 px-5 font-semibold">Driver Earning</th>
                <th class="py-3 px-5 font-semibold">Admin Earning</th>
                <th class="py-3 px-5 font-semibold">Payout Status</th>
                <th class="py-3 px-5 font-semibold">Payout Reference</th>
                <th class="py-3 px-5 font-semibold">Paid At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($payouts as $payout)
                @php
                    $status = strtoupper($payout->PAYOUT_STATUS ?? '');
                    $badge = $status === 'PAID' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5 font-medium text-slate-700">{{ $payout->booking->BOOKING_NO ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $payout->booking->driver->user->FULL_NAME ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-700">RM {{ number_format($payout->DRIVER_EARNING, 2) }}</td>
                    <td class="py-3 px-5 text-slate-700">RM {{ number_format($payout->ADMIN_EARNING, 2) }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ $status ?: '-' }}</span>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $payout->PAYOUT_REFERENCE ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $payout->PAID_AT ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-400">No payout records found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection