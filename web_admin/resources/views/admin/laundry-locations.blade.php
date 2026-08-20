@extends('layouts.admin')
@section('title', 'Laundry Locations')
@section('heading', 'Laundry Locations')
@section('content')
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Address</th><th>Coordinates</th><th>Status</th></tr></thead><tbody>
@foreach ($locations as $location)<tr><td>{{ $location->NAME }}</td><td>{{ $location->ADDRESS }}</td><td>{{ $location->LATITUDE }}, {{ $location->LONGITUDE }}</td><td>{{ $location->STATUS }}</td></tr>@endforeach
</tbody></table></div>
@endsection
