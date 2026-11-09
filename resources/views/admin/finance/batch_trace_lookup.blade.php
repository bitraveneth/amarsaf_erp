@extends('layouts.app')

@section('content')
<x-report.page
    eyebrow="Inventory reports"
    title="Batch traceability"
    subtitle="Search a batch code, then open the full upstream and downstream trail."
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <form method="GET" action="{{ route('admin.reports.batch-trace') }}" class="flex flex-wrap items-center gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    class="erp-input erp-input--sm w-52 sm:w-64"
                    placeholder="Batch code…"
                    aria-label="Search batch code"
                >
                <button type="submit" class="erp-btn-primary">Search</button>
                @if(filled($query))
                    <a href="{{ route('admin.reports.batch-trace') }}" class="erp-btn-secondary">Clear</a>
                @endif
            </form>
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Batches shown"
            :value="(string) $batches->count()"
            :hint="filled($query) ? 'Matching “' . $query . '”' : 'Recent batches'"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Matching batches</h2>
                <p class="erp-caption">Open a batch to see production, GRN, delivery, and invoice links</p>
            </div>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Product</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr
                            class="cursor-pointer"
                            onclick="if (!event.target.closest('a,button')) window.location = this.dataset.href"
                            data-href="{{ route('admin.reports.batch-trace.show', $batch) }}"
                        >
                            <td>
                                <a href="{{ route('admin.reports.batch-trace.show', $batch) }}" class="erp-table-link">{{ $batch->batch_code }}</a>
                            </td>
                            <td class="erp-table-cell">{{ $batch->product?->name ?? 'Unknown product' }}</td>
                            <td class="is-right">
                                <a href="{{ route('admin.reports.batch-trace.show', $batch) }}" class="erp-btn-secondary !px-3 !py-1.5 !text-sm">Open trace</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-admin.empty-state
                                    title="No batches found"
                                    description="Try another batch code, or create batches through production or GRN."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-report.page>
@endsection
