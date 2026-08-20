@extends('layouts.public')
@section('title','Terms of Use')
@section('page-class', 'legal-page')
@section('content')
<section class="legal-content">
    <h1>Terms of Use</h1>
    <p class="legal-updated">Last updated: 5 May 2026</p>
    {{-- Each topic is a separate card so interns can edit it easily. --}}
    <div class="legal-section"><h2>Use of CoolClean</h2><p>Customers and drivers agree to use the platform only for lawful laundry pickup and delivery activities.</p></div>
    <div class="legal-section"><h2>Accounts</h2><p>Users must provide accurate information and protect their account credentials. Driver accounts may require administrator approval.</p></div>
    <div class="legal-section"><h2>Bookings and Messages</h2><p>Booking timelines and messages should be used only for service-related communication.</p></div>
    <div class="legal-section"><h2>Payments</h2><p>Customers are responsible for checking booking details and amounts before confirming payment.</p></div>
    <div class="legal-section"><h2>Availability</h2><p>Features may be updated as CoolClean is developed, tested, and improved.</p></div>
    <div class="legal-section"><h2>Contact</h2><p>For questions, visit the <a href="{{ route('contact') }}">Contact Us</a> page.</p></div>
</section>
@endsection
