@extends('layouts.public')
@section('title','Privacy Policy')
@section('page-class', 'legal-page')
@section('content')
{{-- This wrapper controls page width and keeps content away from screen edges. --}}
<section class="legal-content">
    <h1>Privacy Policy</h1>
    <p class="legal-updated">Last updated: 5 May 2026</p>
    <div class="legal-section"><h2>Overview</h2><p>CoolClean is a laundry pickup and delivery platform. This policy explains how information is used to manage accounts, bookings, drivers, payments, notifications, and support.</p></div>
    <div class="legal-section"><h2>Information We Collect</h2><ul><li>Account details such as name, email, phone number, password, and user role.</li><li>Pickup and service addresses, including selected map coordinates.</li><li>Booking, payment, timeline, message, rating, and driver information.</li><li>Device notification tokens used for booking and message notifications.</li></ul></div>
    <div class="legal-section"><h2>How We Use Information</h2><p>Information is used for login, bookings, driver assignment, tracking, communication, payments, and administration.</p></div>
    <div class="legal-section"><h2>Security</h2><p>CoolClean uses protected sessions, encrypted passwords, role-based access, and server-side validation. Users should keep their login details private.</p></div>
    <div class="legal-section"><h2>Contact</h2><p>For privacy questions, visit the <a href="{{ route('contact') }}">Contact Us</a> page.</p></div>
</section>
@endsection
