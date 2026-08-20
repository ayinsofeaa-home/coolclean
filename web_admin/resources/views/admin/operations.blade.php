@extends('layouts.admin')
@section('title', 'Operations')
@section('heading', 'Operation Settings')
@section('content')
<div class="panel">
<form method="POST" action="{{ route('admin.operations.update') }}">@csrf
<p><label>Maximum active jobs per driver<br><input type="number" name="max_active_jobs" min="1" max="20" value="{{ $settings['driver.max-active-jobs'] ?? 5 }}" required></label></p>
<p><label>Booking service fee (RM)<br><input type="number" step="0.01" name="service_fee" value="{{ $settings['pricing.service-fee'] ?? 2 }}" required></label></p>
<p><label>Booking delivery fee (RM)<br><input type="number" step="0.01" name="delivery_fee" value="{{ $settings['pricing.delivery-fee'] ?? 6 }}" required></label></p>
<p><label>Driver service-fee percentage<br><input type="number" step="0.01" name="driver_percentage" value="{{ $settings['earning.driver-percentage'] ?? 20 }}" required></label></p>
<p><label>Driver delivery-fee percentage<br><input type="number" step="0.01" name="driver_delivery_percentage" value="{{ $settings['earning.driver-delivery-fee-percentage'] ?? 100 }}" required></label></p>
<button>Save settings</button>
</form>
</div>
@endsection
