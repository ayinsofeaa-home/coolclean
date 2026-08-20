@extends('layouts.admin')
@section('title', 'Bookings')
@section('heading', 'Bookings')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Booking</th><th>Customer</th><th>Driver</th><th>Status</th><th>Amount</th><th>Created</th><th></th></tr></thead><tbody>
@foreach ($bookings as $booking)
<tr><td>{{ $booking->BOOKING_NO }}</td><td>{{ $booking->customer?->user?->FULL_NAME }}</td><td>{{ $booking->driver?->user?->FULL_NAME ?? 'Not assigned' }}</td><td><span class="badge">{{ $booking->BOOKING_STATUS }}</span></td><td>RM {{ number_format($booking->TOTAL_AMOUNT, 2) }}</td><td>{{ $booking->CREATED_AT?->format('d M Y H:i') }}</td><td><a class="button" href="{{ route('admin.bookings.show', $booking) }}">View</a></td></tr>
@endforeach
</tbody></table></div><div class="pagination">{{ $bookings->links() }}</div>
@endsection
