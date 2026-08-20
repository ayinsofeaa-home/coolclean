@extends('layouts.public')
@section('title','Contact Us')
@section('page-class', 'legal-page')
@section('content')
<section class="legal-content">
    <h1>Contact Us</h1>
    <p class="legal-updated">CoolClean support and contact information</p>
    <div class="legal-section">
        <h2>Support</h2>
        <p>For account, booking, driver approval, timeline, message, or notification questions, contact the CoolClean administrator.</p>
        <div class="contact-list">
            <div class="contact-item"><strong>Email</strong><a href="mailto:coolclean@yopmail.com">coolclean@yopmail.com</a></div>
            <div class="contact-item"><strong>Project</strong><span>CoolClean laundry pickup and delivery system</span></div>
            <div class="contact-item"><strong>Availability</strong><span>Support is handled manually by the CoolClean team.</span></div>
        </div>
    </div>
    <div class="legal-section"><h2>Before Contacting</h2><p>Please include your registered email, user type, booking reference if available, and a short issue description.</p></div>
</section>
@endsection
