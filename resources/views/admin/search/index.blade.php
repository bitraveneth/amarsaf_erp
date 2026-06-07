@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="search"
        title="Global Search"
        subtitle="Find products, orders, invoices, agents, and batches across the ERP."
    />

    <x-admin.filter-panel method="GET" action="{{ route('admin.search.index') }}">
        <div class="erp-field min-w-[16rem] flex-[2]">
            <label for="search-query" class="erp-label">Search query</label>
            <input
                id="search-query"
                type="search"
                name="q"
                value="{{ $query }}"
                class="erp-input"
                placeholder="Order reference, invoice number, agent name, SKU, batch code..."
            >
        </div>
        <button type="submit" class="erp-btn-primary">Search</button>
    </x-admin.filter-panel>

    <div class="erp-list-card">
        @forelse($results as $result)
            <div class="erp-list-item">
                <div>
                    <a href="{{ $result['path'] }}" class="erp-link text-base">{{ $result['label'] }}</a>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $result['group'] }}</p>
                </div>
                <a href="{{ $result['path'] }}" class="erp-btn-secondary shrink-0">Open</a>
            </div>
        @empty
            @if($query !== '')
                <x-admin.empty-state title="No results found" description="Try a different keyword, invoice number, or batch code." />
            @else
                <x-admin.empty-state title="Start typing to search" description="Use the field above to search across major ERP records." />
            @endif
        @endforelse
    </div>
</div>
@endsection
