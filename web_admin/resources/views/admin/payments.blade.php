@extends('layouts.admin')
@section('title', 'Payments')
@section('heading', 'Payments')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Booking</th><th>Customer</th><th>Method</th><th>Status</th><th>Amount</th><th>Reference</th><th>Paid</th></tr></thead><tbody>
@foreach ($payments as $payment)<tr><td>{{ $payment->booking?->BOOKING_NO }}</td><td>{{ $payment->booking?->customer?->user?->FULL_NAME }}</td><td>{{ $payment->PAYMENT_METHOD }}</td><td>{{ $payment->PAYMENT_STATUS }}</td><td>RM {{ number_format($payment->AMOUNT, 2) }}</td><td>{{ $payment->TRANSACTION_REF }}</td><td>{{ $payment->PAID_AT?->format('d M Y H:i') }}</td></tr>@endforeach
</tbody></table></div><div class="pagination">{{ $payments->links() }}</div>
@endsection
