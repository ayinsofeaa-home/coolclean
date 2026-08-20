@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Operations Dashboard')
@section('subheading', 'Monitor bookings, drivers, payment status, and service progress.')
@section('content')
{{-- Metric cards turn database aggregates from AdminController into quick summaries. --}}
<section class="metrics">
    <div class="card metric"><label>Total Bookings</label><strong>{{ $bookingCount }}</strong></div>
    <div class="card metric"><label>Waiting Driver</label><strong>{{ $waitingDriverCount }}</strong></div>
    <div class="card metric"><label>Online Drivers</label><strong>{{ $activeDriverCount }}</strong></div>
    <div class="card metric"><label>Paid Revenue</label><strong>RM {{ number_format($paidTotal, 2) }}</strong></div>
    <div class="card metric"><label>Booking Grand Total</label><strong>RM {{ number_format($bookingGrandTotal, 2) }}</strong></div>
    <div class="card metric"><label>Payment Grand Total</label><strong>RM {{ number_format($paymentGrandTotal, 2) }}</strong></div>
    <div class="card metric"><label>Driver Earned</label><strong>RM {{ number_format($driverEarningTotal, 2) }}</strong></div>
    <div class="card metric"><label>Admin Earned</label><strong>RM {{ number_format($adminEarningTotal, 2) }}</strong></div>
</section>

<section class="grid-2">
    <div class="card">
        <h2>Recent Bookings</h2>
        <div class="table-wrap"><table class="table"><thead><tr><th>Booking</th><th>Customer</th><th>Driver</th><th>Status</th><th>Amount</th></tr></thead><tbody>
        {{-- Eager-loaded relationships prevent one database query per table row. --}}
        @forelse($recentBookings as $booking)
            <tr><td><a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->BOOKING_NO }}</a></td><td>{{ $booking->customer?->user?->FULL_NAME }}</td><td>{{ $booking->driver?->user?->FULL_NAME ?? '-' }}</td><td><span class="pill blue">{{ $booking->BOOKING_STATUS }}</span></td><td>RM {{ number_format($booking->TOTAL_AMOUNT, 2) }}</td></tr>
        @empty<tr><td colspan="5">No bookings found.</td></tr>@endforelse
        </tbody></table></div>
    </div>

    <div class="card">
        <h2>Driver Monitoring</h2>
        <div class="table-wrap"><table class="table"><thead><tr><th>Driver</th><th>Availability</th><th>Vehicle</th><th>Jobs</th></tr></thead><tbody>
        @forelse($drivers as $driver)
            <tr><td>{{ $driver->user?->FULL_NAME }}</td><td><span class="pill {{ $driver->AVAILABILITY_STATUS === 'ONLINE' ? 'green' : 'yellow' }}">{{ $driver->user?->STATUS === 'ACTIVE' ? $driver->AVAILABILITY_STATUS : $driver->user?->STATUS }}</span></td><td>{{ $driver->VEHICLE_TYPE }} / {{ $driver->VEHICLE_PLATE }}</td><td>{{ $driver->bookings_count }}</td></tr>
        @empty<tr><td colspan="4">No drivers found.</td></tr>@endforelse
        </tbody></table></div>
    </div>
</section>
@endsection
