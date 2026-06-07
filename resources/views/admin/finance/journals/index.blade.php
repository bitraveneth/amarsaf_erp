@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="journal"
        title="Journal Entries"
        subtitle="Posted and draft general ledger journals."
    >
        <x-slot:actions>
            <a href="{{ route('admin.journals.create') }}" class="erp-btn-primary">New journal</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-panel method="GET">
        <div class="erp-field">
            <label class="erp-label" for="journal-from">From</label>
            <input id="journal-from" type="date" name="from" value="{{ $from->toDateString() }}" class="erp-input">
        </div>
        <div class="erp-field">
            <label class="erp-label" for="journal-to">To</label>
            <input id="journal-to" type="date" name="to" value="{{ $to->toDateString() }}" class="erp-input">
        </div>
        <div class="erp-field">
            <label class="erp-label" for="journal-status">Status</label>
            <select id="journal-status" name="status" class="erp-select">
                <option value="">All statuses</option>
                @foreach(['draft','posted','reversed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="erp-btn-primary">Filter</button>
    </x-admin.filter-panel>

    <x-admin.table-card title="Journal list">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="is-right">Debit</th>
                    <th class="is-right">Credit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td><a href="{{ route('admin.journals.show', $entry) }}" class="erp-link">{{ $entry->number }}</a></td>
                        <td>{{ $entry->entry_date->format('d M Y') }}</td>
                        <td>{{ str_replace('_', ' ', $entry->journal_type) }}</td>
                        <td>{{ $entry->description }}</td>
                        <td class="is-right">{{ number_format($entry->totalDebit(), 2) }}</td>
                        <td class="is-right">{{ number_format($entry->totalCredit(), 2) }}</td>
                        <td>
                            @if($entry->status === 'posted')
                                <span class="erp-badge-success">{{ ucfirst($entry->status) }}</span>
                            @elseif($entry->status === 'draft')
                                <span class="erp-badge-warning">{{ ucfirst($entry->status) }}</span>
                            @else
                                <span class="erp-badge-neutral">{{ ucfirst($entry->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-admin.empty-state title="No journal entries found" description="Adjust your filters or create a new journal entry." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>

    @if($entries instanceof \Illuminate\Contracts\Pagination\Paginator && $entries->hasPages())
        <div class="mt-4">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
