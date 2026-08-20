@extends('layouts.public')
@section('title','CoolClean')
@section('content')
{{-- Hero explains the product and gives visitors two clear next actions. --}}
<section class="marketing-hero"><div class="marketing-copy"><h1>Laundry pickup and delivery made simple.</h1><p>CoolClean helps busy customers book laundry service, pay online, track progress, and receive clean clothes at their doorstep.</p><div class="actions"><a class="button" href="{{ route('login') }}">Admin Login</a><a class="button secondary" href="#how-it-works">How It Works</a></div></div><div class="logo-showcase"><img src="{{ asset('images/coolclean-logo-transparent.png') }}" alt="CoolClean laundry basket logo"></div></section>

{{-- Repeated cards use one CSS component, which keeps the page consistent. --}}
<section id="services" class="feature-strip"><div class="feature"><strong>Wash</strong><span>Everyday laundry pickup, washing, and return delivery.</span></div><div class="feature"><strong>Dry</strong><span>Drying service with progress updates from driver to customer.</span></div><div class="feature"><strong>Folding</strong><span>A convenient finishing service for customer bookings.</span></div><div class="feature"><strong>Ironing</strong><span>Professional ironing as an optional laundry add-on.</span></div></section>
<section id="how-it-works" class="feature-strip"><div class="feature"><strong>Book From Mobile</strong><span>Select services, pickup address, and preferred time.</span></div><div class="feature"><strong>Driver Pickup</strong><span>An approved driver accepts and collects the laundry.</span></div><div class="feature"><strong>Status Tracking</strong><span>Follow washing, drying, return, and completion.</span></div><div class="feature"><strong>Admin Monitoring</strong><span>Admins monitor the complete operation centrally.</span></div></section>
<section id="track-order" class="feature-strip single-row"><div class="feature"><strong>Track Order</strong><span>Customers track bookings in the mobile app while administrators monitor every stage.</span></div></section>
@endsection
