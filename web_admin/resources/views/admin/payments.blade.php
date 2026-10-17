@extends('layouts.admin')

@section('title', 'Payments - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Payments</h1>
<p class="text-slate-500 mt-1 mb-6">View all customer payment transactions.</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left whitespace-nowrap">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Booking</th>
                <th class="py-3 px-5 font-semibold">Amount</th>
                <th class="py-3 px-5 font-semibold">Method</th>
                <th class="py-3 px-5 font-semibold">Status</th>
                <th class="py-3 px-5 font-semibold">Transaction Ref</th>
                <th class="py-3 px-5 font-semibold">Paid At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($payments as $payment)
                @php
                    $status = strtoupper($payment->PAYMENT_STATUS ?? '');
                    $badge = $status === 'PAID' ? 'bg-green-100 text-green-700' : ($status === 'REFUNDED' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-500');
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5 font-medium text-slate-700">{{ $payment->booking->BOOKING_NO ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-700">RM {{ number_format($payment->AMOUNT, 2) }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $payment->PAYMENT_METHOD }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">{{ $status }}</span>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $payment->TRANSACTION_REF ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $payment->PAID_AT ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400">No payments found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection