@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="batch"
        title="Batch Traceability"
        subtitle="Search finished-goods or material batches and open a full upstream/downstream trace."
    />

    <x-admin.filter-panel method="GET" action="{{ route('admin.reports.batch-trace') }}">
        <div class="erp-field min-w-[16rem] flex-[2]">
            <label for="batch-query" class="erp-label">Batch code</label>
            <input id="batch-query" type="search" name="q" value="{{ $query }}" class="erp-input" placeholder="Search batch code...">
        </div>
        <button type="submit" class="erp-btn-primary">Search</button>
    </x-admin.filter-panel>

    <div class="erp-list-card">
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
</div>
@endsection
