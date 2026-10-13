@extends('layouts.admin')

@section('title', 'Services - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Service Management</h1>
<p class="text-slate-500 mt-1 mb-6">View laundry service types and prices.</p>

@if (session('status'))
    <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-50 text-red-600 text-sm p-3 rounded-lg mb-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="bg-white rounded-xl shadow p-6 mb-6">
    <h2 class="text-lg font-bold text-slate-800 mb-2">Earning Settings</h2>
    <p class="text-sm text-slate-500 mb-4">
        Customer sees laundry fee, service fee, and delivery fee. Driver earns delivery fee share plus a percentage of the service fee.
    </p>

    <form method="POST" action="{{ route('admin.services.settings') }}" class="flex flex-wrap items-end gap-4">
        @csrf
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Service fee (RM)</label>
            <input type="number" step="0.01" name="service_fee" value="{{ old('service_fee', $serviceFee) }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Delivery fee (RM)</label>
            <input type="number" step="0.01" name="delivery_fee" value="{{ old('delivery_fee', $deliveryFee) }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Driver service fee share (%)</label>
            <input type="number" step="0.01" name="driver_service_fee_share" value="{{ old('driver_service_fee_share', $driverServiceShare) }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Driver delivery fee share (%)</label>
            <input type="number" step="0.01" name="driver_delivery_fee_share" value="{{ old('driver_delivery_fee_share', $driverDeliveryShare) }}"
                   class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit"
                class="bg-slate-900 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
            Save Pricing Settings
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Service</th>
                <th class="py-3 px-5 font-semibold">Description</th>
                <th class="py-3 px-5 font-semibold">Price</th>
                <th class="py-3 px-5 font-semibold">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($services as $service)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5 font-medium text-slate-700">{{ $service->SERVICE_NAME }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $service->DESCRIPTION }}</td>
                    <td class="py-3 px-5 text-slate-700">RM {{ number_format($service->PRICE, 2) }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($service->STATUS) === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ strtoupper($service->STATUS) === 'ACTIVE' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                            {{ strtoupper($service->STATUS) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-8 text-center text-slate-400">No services found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection