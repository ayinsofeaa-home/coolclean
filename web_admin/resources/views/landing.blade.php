@extends('layouts.guest')

@section('title', 'CoolClean')

@section('content')
<div style="background-image: url('{{ asset('images/bg_4.jpeg') }}'); background-size: cover; background-position: center; background-attachment: fixed;" class="min-h-screen">
<div class="max-w-6xl mx-auto px-6 py-16">

    {{-- Hero --}}
    <div class="text-center mb-16">
        <img src="{{ asset('images/logo.png') }}" alt="CoolClean" class="w-20 h-20 object-contain mx-auto mb-4">
        <h1 class="text-4xl font-bold text-blue-600 mb-4">CoolClean</h1>
        <p class="text-slate-600 mb-8">Book laundry pickup, track your order, and let CoolClean handle the rest.</p>
       <a href="{{ route('login') }}"
   class="bg-[#4A5A7A] hover:bg-[#3b4862] text-white px-6 py-3 rounded-lg font-semibold transition">
    Admin Login
        </a>
    </div>

    {{-- Services --}}
    <h2 class="text-xl font-bold text-slate-800 mb-4">Our Services</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-12">
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Wash</h3>
            <p class="text-sm text-slate-500">Everyday laundry pickup, washing, and return delivery for customers.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Dry</h3>
            <p class="text-sm text-slate-500">Drying service for paid bookings with progress updates from driver to customer.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Folding</h3>
            <p class="text-sm text-slate-500">Visible in the app as a planned service and currently marked unavailable.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Ironing</h3>
            <p class="text-sm text-slate-500">Visible in the app as a planned service and currently marked unavailable.</p>
        </div>
    </div>

    {{-- How it works --}}
    <h2 class="text-xl font-bold text-slate-800 mb-4">How It Works</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Book From Mobile</h3>
            <p class="text-sm text-slate-500">Customer selects service, pickup address, and preferred time.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Driver Pickup</h3>
            <p class="text-sm text-slate-500">Driver accepts the paid booking and collects the clothes.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Status Tracking</h3>
            <p class="text-sm text-slate-500">Customer follows washing, drying, returning, and completed status.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold text-slate-800 mb-2">Admin Monitoring</h3>
            <p class="text-sm text-slate-500">Admin monitors bookings, drivers, customers, services, and reports.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-bold text-slate-800 mb-2">Track Order</h3>
        <p class="text-sm text-slate-500">Customers can track paid bookings in the mobile app while admins monitor every status from the web dashboard.</p>
    </div>

</div>
</div>
@endsection