@extends('layouts.admin')
@section('title', 'Customers')
@section('heading', 'Customers')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Bookings</th><th>Address</th></tr></thead><tbody>
@foreach ($customers as $customer)
<tr><td>{{ $customer->user?->FULL_NAME }}</td><td>{{ $customer->user?->EMAIL }}</td><td>{{ $customer->user?->PHONE }}</td><td>{{ $customer->user?->STATUS }}</td><td>{{ $customer->bookings_count }}</td><td>{{ $customer->DEFAULT_ADDRESS }}</td></tr>
@endforeach
</tbody></table></div><div class="pagination">{{ $customers->links() }}</div>
@endsection
