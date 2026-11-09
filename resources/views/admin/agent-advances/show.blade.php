@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
    $statusLabels = [
        'open' => 'Open',
        'partial' => 'Partial',
        'applied' => 'Applied',
    ];
    $badge = match ($advance->status) {
        'applied' => 'erp-badge-success',
        'partial' => 'erp-badge-warning',
        default => 'erp-badge-brand',
    };
    $method = $advance->payment_method
        ? ucfirst(str_replace('_', ' ', $advance->payment_method))
        : 'Not recorded';
@endphp
<div class="erp-page">
    <x-admin.page-header
        :title="'Advance #' . $advance->id"
        :subtitle="($advance->agent?->name ?? 'Agent advance') . ' · ' . $advance->advanced_at->format('d M Y')"
    >
        <x-slot:actions>
            <span class="{{ $badge }}">{{ $statusLabels[$advance->status] ?? ucfirst($advance->status) }}</span>
            <a href="{{ route('admin.agent-advances.index', array_filter(['agent_id' => $advance->agent_id])) }}" class="erp-btn-secondary">Agent advances</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Advance"
        :cards="[
            [
                'label' => 'Received',
                'numeric' => number_format((float) $advance->amount, 2),
                'currency' => $currency,
                'caption' => $method . ($advance->reference ? ' · ' . $advance->reference : ''),
                'href' => route('admin.agent-advances.show', $advance),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'revenue',
            ],
            [
                'label' => 'Applied',
                'numeric' => number_format((float) $advance->applied_amount, 2),
                'currency' => $currency,
                'caption' => $advance->applications->count() . ' ' . Str::plural('invoice', $advance->applications->count()),
                'href' => route('admin.agent-advances.show', $advance),
                'tone' => 'orange',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Available',
                'numeric' => number_format($advance->available_amount, 2),
                'currency' => $currency,
                'caption' => 'Still waiting to be applied',
                'href' => route('admin.agent-advances.show', $advance),
                'tone' => 'success',
                'valueTone' => 'success',
                'icon' => 'receivables',
            ],
            [
                'label' => 'Agent history',
                'numeric' => number_format($agentAdvances->count()),
                'caption' => $history->count() . ' movements',
                'href' => $advance->agent ? route('admin.agents.show', $advance->agent) : route('admin.agent-advances.show', $advance),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'users',
            ],
        ]"
    />

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <h2 class="erp-h3">Advance history</h2>
            <p class="erp-caption">
                Every advance received from {{ $advance->agent?->name ?? 'this agent' }}, and each time an amount was applied to an invoice.
                @if($advance->notes)
                    · {{ $advance->notes }}
                @endif
            </p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Advance</th>
                        <th>What happened</th>
                        <th>Detail</th>
                        <th class="is-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                        <tr>
                            <td class="erp-table-cell whitespace-nowrap">{{ $row['at']?->format('d M Y') ?? '—' }}</td>
                            <td>
                                @if($row['current'])
                                    <span class="erp-badge-brand">#{{ $row['advance']->id }}</span>
                                @else
                                    <a href="{{ route('admin.agent-advances.show', $row['advance']) }}" class="erp-table-link">#{{ $row['advance']->id }}</a>
                                @endif
                            </td>
                            <td class="erp-table-cell">{{ $row['event'] }}</td>
                            <td>
                                @if($row['invoice'])
                                    <a href="{{ route('admin.finance.show', $row['invoice']) }}" class="erp-table-link">{{ $row['invoice']->number }}</a>
                                @else
                                    <span class="erp-table-cell">{{ $row['detail'] ?: '—' }}</span>
                                @endif
                            </td>
                            <td class="is-right">
                                <span class="erp-table-num">{{ $row['direction'] === 'out' ? '− ' : '' }}{{ $currency }} {{ number_format($row['amount'], 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state title="No movements" description="This advance has not been received or applied." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <h2 class="erp-h3">Ledger</h2>
            <p class="erp-caption">
                @if($journal)
                    {{ $journal->number }} · {{ ucfirst($journal->status) }}
                @else
                    This advance has no journal entry.
                @endif
            </p>
        </div>
        @if($journal)
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Side</th>
                            <th>Account</th>
                            <th class="is-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->lines as $line)
                            @php
                                $isDebit = (float) $line->debit > 0;
                                $lineAmount = $isDebit ? $line->debit : $line->credit;
                            @endphp
                            <tr>
                                <td class="erp-table-cell">{{ $isDebit ? 'Debit' : 'Credit' }}</td>
                                <td>
                                    @if($line->account)
                                        <a href="{{ route('admin.accounts.show', $line->account) }}" class="erp-table-link">{{ $line->account->code }} {{ $line->account->name }}</a>
                                    @else
                                        <span class="erp-table-cell">Account removed</span>
                                    @endif
                                </td>
                                <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format((float) $lineAmount, 2) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
