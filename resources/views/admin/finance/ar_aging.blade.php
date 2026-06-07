@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="chart"
        title="Accounts Receivable Aging"
        subtitle="Open customer balances by days past due."
    >
        <x-slot:actions>
            @include('admin.finance.partials.print_button')
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-panel method="GET">
        <div class="erp-field">
            <label class="erp-label" for="ar-as-of">As of</label>
            <input id="ar-as-of" type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="erp-input">
        </div>
        <button type="submit" class="erp-btn-primary">Apply</button>
    </x-admin.filter-panel>

    <div class="erp-stat-grid">
        <x-admin.stat-card label="Sub-ledger outstanding" :value="number_format($subledgerTotal, 2)" />
        <x-admin.stat-card label="GL Accounts Receivable" :value="number_format($glBalance, 2)" />
        <x-admin.stat-card
            label="Variance"
            :value="($variance >= 0 ? '+' : '') . number_format($variance, 2)"
            :tone="abs($variance) < 0.01 ? 'success' : 'warning'"
        />
    </div>

    <x-admin.table-card title="Aging summary">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Aging bucket</th>
                    @foreach($bucketLabels as $label)
                        <th class="is-right">{{ $label }}</th>
                    @endforeach
                    <th class="is-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="font-medium text-gray-900 dark:text-white">All open invoices</td>
                    @foreach($bucketLabels as $key => $label)
                        <td class="is-right">{{ number_format($buckets[$key] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="is-right font-semibold">{{ number_format($subledgerTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-admin.table-card>

    <x-admin.table-card title="By agent">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    @foreach($bucketLabels as $label)
                        <th class="is-right">{{ $label }}</th>
                    @endforeach
                    <th class="is-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byAgent as $group)
                    <tr>
                        <td>{{ $group['name'] }}</td>
                        @foreach($bucketLabels as $key => $label)
                            <td class="is-right">{{ number_format($group['buckets'][$key] ?? 0, 2) }}</td>
                        @endforeach
                        <td class="is-right font-semibold">{{ number_format($group['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($bucketLabels) + 2 }}"><x-admin.empty-state title="No open receivables" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>

    <x-admin.table-card title="Open invoices">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Agent</th>
                    <th>Due</th>
                    <th class="is-right">Days past due</th>
                    <th>Bucket</th>
                    <th class="is-right">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-mono">{{ $row['invoice']->number }}</td>
                        <td>{{ $row['agent']?->name ?? '—' }}</td>
                        <td>{{ $row['due_at']?->format('d M Y') ?? '—' }}</td>
                        <td class="is-right">{{ max(0, $row['days_past_due']) }}</td>
                        <td>{{ $bucketLabels[$row['bucket']] ?? $row['bucket'] }}</td>
                        <td class="is-right font-medium">{{ number_format($row['outstanding'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-admin.empty-state title="No open receivables" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>
</div>
@endsection
