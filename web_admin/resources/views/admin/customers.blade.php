@extends('layouts.admin')

@section('title', 'Customers - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Customer Management</h1>
<p class="text-slate-500 mt-1 mb-6">View all registered customers and their details.</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Name</th>
                <th class="py-3 px-5 font-semibold">Email</th>
                <th class="py-3 px-5 font-semibold">Phone</th>
                <th class="py-3 px-5 font-semibold">Default Address</th>
                <th class="py-3 px-5 font-semibold">City</th>
                <th class="py-3 px-5 font-semibold">State</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($customers as $customer)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center text-xs font-bold uppercase">
                                {{ substr($customer->user->FULL_NAME ?? '-', 0, 1) }}
                            </div>
                            <span class="font-medium text-slate-700">{{ $customer->user->FULL_NAME ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-5 text-slate-500">{{ $customer->user->EMAIL ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $customer->user->PHONE ?? '-' }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $customer->DEFAULT_ADDRESS }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $customer->DEFAULT_CITY }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $customer->DEFAULT_STATE }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400">No customers found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection