@extends('layouts.admin')
@section('title', 'Reports')
@section('heading', 'Reports')
@section('content')
<div class="cards">
 <div class="card">Paid revenue<strong>RM {{ number_format($paidRevenue, 2) }}</strong></div>
 <div class="card">Refunded<strong>RM {{ number_format($refundedTotal, 2) }}</strong></div>
 <div class="card">Driver earnings<strong>RM {{ number_format($driverEarnings, 2) }}</strong></div>
 <div class="card">Admin earnings<strong>RM {{ number_format($adminEarnings, 2) }}</strong></div>
 <div class="card">Completed bookings<strong>{{ $completedBookings }}</strong></div>
</div>
@endsection
