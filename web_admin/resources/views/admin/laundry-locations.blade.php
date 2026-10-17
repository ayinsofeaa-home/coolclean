@extends('layouts.admin')

@section('title', 'Laundry Locations - CoolClean')

@section('content')
<h1 class="text-3xl font-bold text-slate-800">Laundry Locations</h1>
<p class="text-slate-500 mt-1 mb-6">View self-service laundry locations used by drivers.</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="text-slate-400 uppercase text-xs bg-slate-50 border-b border-slate-100">
                <th class="py-3 px-5 font-semibold">Name</th>
                <th class="py-3 px-5 font-semibold">Address</th>
                <th class="py-3 px-5 font-semibold">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($locations as $location)
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-3 px-5 font-medium text-slate-700">{{ $location->NAME }}</td>
                    <td class="py-3 px-5 text-slate-500">{{ $location->ADDRESS }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full
                            {{ strtoupper($location->STATUS) === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ strtoupper($location->STATUS) === 'ACTIVE' ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                            {{ strtoupper($location->STATUS) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-8 text-center text-slate-400">No laundry locations found in database.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection