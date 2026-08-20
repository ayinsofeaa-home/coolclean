@extends('layouts.admin')
@section('title', 'Payouts')
@section('heading', 'Driver Payouts')
@section('content')
<div class="cards">
    <div class="card">Total earned<strong>RM {{ number_format($drivers->sum('total'), 2) }}</strong></div>
    <div class="card">Total paid<strong>RM {{ number_format($drivers->sum('paid'), 2) }}</strong></div>
    <div class="card">Pending payout<strong>RM {{ number_format($drivers->sum('pending'), 2) }}</strong></div>
</div>
<div class="table-wrap"><table><thead><tr><th>Driver</th><th>Vehicle</th><th>Earned</th><th>Paid</th><th>Pending</th><th>Last paid</th><th>Action</th></tr></thead><tbody>
@foreach ($drivers as $summary)
<tr>
    <td>{{ $summary['driver']->user?->FULL_NAME }}</td>
    <td>{{ $summary['driver']->VEHICLE_PLATE }}</td>
    <td>RM {{ number_format($summary['total'], 2) }}</td>
    <td>RM {{ number_format($summary['paid'], 2) }}</td>
    <td>RM {{ number_format($summary['pending'], 2) }}</td>
    <td>{{ $summary['lastPaidAt'] }}</td>
    <td>@if($summary['pending'] > 0)<form method="POST" action="{{ route('admin.payouts.paid', $summary['driver']) }}">@csrf <input name="reference" placeholder="Optional reference"><button>Mark all paid</button></form>@else No pending payout @endif</td>
</tr>
@endforeach
</tbody></table></div>
@endsection
