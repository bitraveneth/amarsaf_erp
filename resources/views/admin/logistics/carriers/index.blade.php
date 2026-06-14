@extends('layouts.app')

@section('content')
<div class="dash-page space-y-6">
    <x-admin.page-header title="Transport carriers" subtitle="Hired truck, courier, and external freight vendors — managed in Logistics, separate from raw-material suppliers.">
        <x-slot:actions>
            <a href="{{ route('admin.carrier-rate-cards.index') }}" class="erp-btn-secondary">Rate cards</a>
            <a href="{{ route('admin.logistics.carriers.create') }}" class="erp-btn-primary">Add carrier</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">{{ session('status') }}</div>
    @endif

    @if($errors->has('carrier'))
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first('carrier') }}</div>
    @endif

    <div class="erp-table-card">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div>
                <h2 class="erp-table-card-title">Carrier directory</h2>
                <p class="erp-table-card-description">Used on logistics bills and carrier rate cards.</p>
            </div>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Carrier</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="is-right">Rate cards</th>
                        <th class="is-right">Bills</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carriers as $carrier)
                        <tr>
                            <td>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $carrier->name }}</p>
                                @if($carrier->tax_id)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">BIN {{ $carrier->tax_id }}</p>
                                @endif
                            </td>
                            <td>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $carrier->contact_person ?: '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $carrier->phone ?: ($carrier->email ?: '—') }}</p>
                            </td>
                            <td>
                                @if($carrier->is_active)
                                    <span class="inline-flex rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-300">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="is-right">{{ $carrier->carrier_rate_cards_count }}</td>
                            <td class="is-right">{{ $carrier->logistics_bills_count }}</td>
                            <td class="is-right whitespace-nowrap">
                                <a href="{{ route('admin.logistics.carriers.edit', $carrier) }}" class="erp-btn-action">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No transport carriers yet.</p>
                                <a href="{{ route('admin.logistics.carriers.create') }}" class="mt-3 inline-flex erp-btn-primary">Add your first carrier</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($carriers->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">{{ $carriers->links() }}</div>
        @endif
    </div>
</div>
@endsection
