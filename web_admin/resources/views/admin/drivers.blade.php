@extends('layouts.admin')
@section('title', 'Drivers')
@section('heading', 'Drivers')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Approval</th><th>Availability</th><th>Vehicle</th><th>Jobs</th><th>Actions</th></tr></thead><tbody>
@foreach ($drivers as $driver)
<tr><td>{{ $driver->user?->FULL_NAME }}</td><td>{{ $driver->user?->EMAIL }}</td><td>{{ $driver->user?->STATUS }}</td><td>{{ $driver->AVAILABILITY_STATUS }}</td><td>{{ $driver->VEHICLE_TYPE }} {{ $driver->VEHICLE_PLATE }}</td><td>{{ $driver->bookings_count }}</td><td>
<form method="POST" action="{{ route('admin.drivers.approve', $driver) }}">@csrf <button>Approve</button></form>
<form method="POST" action="{{ route('admin.drivers.reject', $driver) }}">@csrf <input name="remarks" placeholder="Rejection reason" required><button>Reject</button></form>
</td></tr>
@endforeach
</tbody></table></div><div class="pagination">{{ $drivers->links() }}</div>
@endsection
