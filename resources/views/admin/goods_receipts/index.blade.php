@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Goods receipt notes (GRN)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Posted receipts update material stock automatically.</p>
        </div>
        <a href="{{ route('admin.goods-receipts.create') }}" data-tour="goods-receipts-primary-action" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">New GRN</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">GRN</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Warehouse</th>
                    <th class="px-4 py-3">Received At</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($receipts as $receipt)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $receipt->grn_number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->warehouse->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($receipt->received_at)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($receipt->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-admin.action-group>
                                <x-admin.action-view :href="route('admin.goods-receipts.show', $receipt)" />
                                @if($receipt->status === 'posted')
                                    <form action="{{ route('admin.goods-receipts.reverse', $receipt) }}" method="POST" onsubmit="return confirm('Reverse {{ $receipt->grn_number }}? This will remove its stock only if none of it has been used, transferred, reserved, or adjusted.');" class="inline">
                                        @csrf
                                        <button type="submit" class="erp-btn-action-danger">
                                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 010 12h-3" />
                                            </svg>
                                            Reverse
                                        </button>
                                    </form>
                                @endif
                            </x-admin.action-group>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No GRN posted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $receipts->links() }}
</div>
@endsection
