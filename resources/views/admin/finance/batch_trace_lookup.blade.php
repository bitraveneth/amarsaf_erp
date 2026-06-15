@extends('layouts.app')

@section('content')
<x-report.page
    eyebrow="Inventory reports"
    title="Batch traceability"
    subtitle="Search finished-goods or material batches and open a full upstream/downstream trace."
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <form method="GET" action="{{ route('admin.reports.batch-trace') }}" class="flex items-center gap-2">
                <input type="search" name="q" value="{{ $query }}" class="erp-input !h-9 w-44 sm:w-52" placeholder="Batch code…">
                <button type="submit" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Search</button>
            </form>
        </x-report.header-actions>
    </x-slot:actions>

    <x-dashboard.panel title="Matching batches" subtitle="Open a batch to see production, GRN, delivery, and invoice links.">
        <div class="erp-list-card !border-0 !shadow-none">
            @forelse($batches as $batch)
                <div class="erp-list-item">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $batch->batch_code }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $batch->product?->name ?? 'Unknown product' }}</p>
                    </div>
                    <a href="{{ route('admin.reports.batch-trace.show', $batch) }}" class="erp-btn-secondary shrink-0">Open trace</a>
                </div>
            @empty
                <x-admin.empty-state title="No batches found" description="Try another batch code or create batches through production or GRN." />
            @endforelse
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
