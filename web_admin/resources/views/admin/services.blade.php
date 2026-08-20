@extends('layouts.admin')
@section('title', 'Services')
@section('heading', 'Laundry Services')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Description</th><th>Price / status</th></tr></thead><tbody>
@foreach ($services as $service)<tr><td>{{ $service->SERVICE_NAME }}</td><td>{{ $service->DESCRIPTION }}</td><td><form method="POST" action="{{ route('admin.services.update', $service) }}">@csrf <input type="number" step="0.01" name="price" value="{{ $service->PRICE }}" required><select name="status"><option @selected($service->STATUS === 'ACTIVE')>ACTIVE</option><option @selected($service->STATUS === 'INACTIVE')>INACTIVE</option></select><button>Save</button></form></td></tr>@endforeach
</tbody></table></div>
@endsection
