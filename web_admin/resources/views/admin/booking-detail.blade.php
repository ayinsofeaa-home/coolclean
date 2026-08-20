@extends('layouts.admin')
@section('title', 'Booking '.$booking->BOOKING_NO)
@section('heading', 'Booking '.$booking->BOOKING_NO)
@section('content')
<div class="cards">
 <div class="card">Status<strong>{{ $booking->BOOKING_STATUS }}</strong></div>
 <div class="card">Customer<strong>{{ $booking->customer?->user?->FULL_NAME }}</strong></div>
 <div class="card">Driver<strong>{{ $booking->driver?->user?->FULL_NAME ?? 'Not assigned' }}</strong></div>
 <div class="card">Total<strong>RM {{ number_format($booking->TOTAL_AMOUNT, 2) }}</strong></div>
</div>
<div class="panel"><p><b>Pickup:</b> {{ $booking->PICKUP_ADDRESS }}</p><p><b>Services:</b> {{ $booking->SERVICE_SUMMARY }}</p><p><b>Payment:</b> {{ $booking->payment?->PAYMENT_STATUS ?? 'None' }}</p><p><b>Laundry:</b> {{ $booking->laundryLocation?->NAME ?? 'Not assigned' }}</p></div>
<h3>Status history</h3><div class="table-wrap"><table><thead><tr><th>Time</th><th>Status</th><th>Remarks</th><th>Updated by</th></tr></thead><tbody>
@foreach ($booking->statusHistory->sortByDesc('UPDATED_AT') as $history)
<tr><td>{{ $history->UPDATED_AT?->format('d M Y H:i') }}</td><td>{{ $history->STATUS }}</td><td>{{ $history->REMARKS }}</td><td>{{ $history->updatedBy?->FULL_NAME }}</td></tr>
@endforeach
</tbody></table></div>
@endsection
