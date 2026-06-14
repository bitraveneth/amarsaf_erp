@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Carrier rate cards" subtitle="Freight rates for hired transport — per trip, crate, km, or monthly.">
        <x-slot:actions>
            <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-btn-secondary">Carriers</a>
            <a href="{{ route('admin.carrier-rate-cards.create') }}" class="erp-btn-primary">Add rate card</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))<div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800">{{ session('status') }}</div>@endif

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead><tr><th>Carrier</th><th>Route</th><th>Unit</th><th class="is-right">Rate</th><th>Status</th><th class="is-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr>
                            <td>{{ $card->transportCarrier->name }}</td>
                            <td>{{ $card->deliveryRoute->name ?? 'All routes' }}</td>
                            <td>{{ $card->unitLabel() }}</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($card->rate, 2) }}</td>
                            <td>{{ $card->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="is-right"><a href="{{ route('admin.carrier-rate-cards.edit', $card) }}" class="erp-btn-action">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">No rate cards yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cards->hasPages())<div class="px-6 py-4">{{ $cards->links() }}</div>@endif
    </div>
</div>
@endsection
