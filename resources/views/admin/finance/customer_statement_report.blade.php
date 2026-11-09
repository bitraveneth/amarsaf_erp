@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $summary = $summary ?? ['movements' => 0, 'invoiced' => 0, 'collected' => 0, 'credits' => 0];
    $agentOptions = ($agents ?? collect())->map(fn ($a) => [
        'id' => $a->id,
        'label' => $a->name,
        'meta' => trim(collect([$a->area, $a->phone])->filter()->implode(' · ')),
    ]);
    $typeBadge = [
        'invoice' => 'erp-badge-brand',
        'receipt' => 'erp-badge-success',
        'credit' => 'erp-badge-warning',
        'advance' => 'erp-badge-brand',
    ];
@endphp

<x-report.page
    eyebrow="Accounting reports"
    title="Customer statement"
    subtitle="Opening balance, invoices, receipts, credits, and advances for one agent."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.customer-statement')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.party-filter
                :action="route('admin.reports.customer-statement')"
                name="agent_id"
                label="Agent"
                :options="$agentOptions"
                :selected-id="$agentId"
                :selected-label="$agent?->name ?? 'Choose agent'"
            />
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="customer-statement" :from="$from" :to="$to" :export-query="array_filter(['agent_id' => $agentId])" />
        </x-report.header-actions>
    </x-slot:actions>

    @if($agent)
        <x-slot:kpis>
            <x-dashboard.kpi label="Opening" :value="$ccy . ' ' . number_format($opening, 0)" hint="Due before period" />
            <x-dashboard.kpi label="Invoiced" :value="$ccy . ' ' . number_format($summary['invoiced'], 0)" :hint="$summary['movements'] . ' movements'" />
            <x-dashboard.kpi label="Collected" tone="success" :value="$ccy . ' ' . number_format($summary['collected'], 0)" :hint="($summary['credits'] > 0 ? 'Credits ' . $ccy . ' ' . number_format($summary['credits'], 0) : 'Receipts and advances')" />
            <x-dashboard.kpi label="Closing" tone="warning" :value="$ccy . ' ' . number_format($closing, 0)" hint="Still receivable" />
        </x-slot:kpis>

        <div class="erp-table-card">
            <div class="erp-table-card-header">
                <div>
                    <h2 class="erp-h3">{{ $agent->name }}</h2>
                    <p class="erp-caption">
                        {{ collect([$agent->area, $agent->phone, $agent->email ?? null])->filter()->implode(' · ') ?: 'Customer statement' }}
                        · {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
                <p class="erp-caption">{{ number_format($summary['movements']) }} lines</p>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 8rem">
                        <col style="width: 8.5rem">
                        <col style="width: 11rem">
                        <col>
                        <col style="width: 9.5rem">
                        <col style="width: 9.5rem">
                        <col style="width: 10rem">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Detail</th>
                            <th class="is-right">Debit</th>
                            <th class="is-right">Credit</th>
                            <th class="is-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="whitespace-nowrap">{{ $from->format('d M Y') }}</td>
                            <td><span class="erp-badge-brand">Opening</span></td>
                            <td colspan="2"><span class="erp-caption">Balance brought forward</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($opening, 2) }}</span></td>
                        </tr>

                        @forelse($rows as $row)
                            @php
                                $badge = $typeBadge[$row['type_key'] ?? ''] ?? 'erp-badge-brand';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ $row['date'] instanceof \Illuminate\Support\Carbon ? $row['date']->format('d M Y') : '—' }}</td>
                                <td><span class="{{ $badge }}">{{ $row['type'] }}</span></td>
                                <td>
                                    @if(! empty($row['href']))
                                        <a href="{{ $row['href'] }}" class="erp-table-link font-mono">{{ $row['reference'] }}</a>
                                    @else
                                        <span class="font-mono text-sm">{{ $row['reference'] }}</span>
                                    @endif
                                </td>
                                <td class="min-w-0">
                                    <p class="truncate text-sm text-gray-700 dark:text-gray-200">{{ $row['detail'] ?: '—' }}</p>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ number_format($row['balance'] ?? 0, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-admin.empty-state title="No activity in this period" description="Invoices, receipts, credits, and advances for this agent will appear here." />
                                </td>
                            </tr>
                        @endforelse

                        <tr>
                            <td class="whitespace-nowrap">{{ $to->format('d M Y') }}</td>
                            <td><span class="erp-badge-warning">Closing</span></td>
                            <td colspan="2"><span class="erp-caption">Balance carried forward</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($closing, 2) }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="erp-report-prompt text-center">
            <p class="erp-report-prompt__title">Choose an agent to open the statement</p>
            <p class="erp-report-prompt__text">
                Search below (or use Agent in the header). The statement shows opening balance, invoices, receipts, credits, advances, and a running balance for the selected period.
            </p>

            <div class="mx-auto mt-6 max-w-xl text-left">
                <x-report.party-filter
                    variant="hero"
                    :action="route('admin.reports.customer-statement')"
                    name="agent_id"
                    label="Agent"
                    :options="$agentOptions"
                    :selected-id="$agentId"
                    :selected-label="$agent?->name ?? 'Choose agent'"
                />
            </div>

            @if($agents->isNotEmpty())
                <div class="erp-report-prompt__picks">
                    @foreach($agents->take(8) as $quickAgent)
                        <a
                            href="{{ route('admin.reports.customer-statement', array_filter([
                                'agent_id' => $quickAgent->id,
                                'range' => $range,
                                'from' => request('from', $from->toDateString()),
                                'to' => request('to', $to->toDateString()),
                            ])) }}"
                            class="erp-report-prompt__pick"
                        >
                            <span class="truncate">{{ $quickAgent->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <x-admin.quick-guide
        title="How to read a customer statement"
        lead="Pick one agent, then review opening balance, period activity, and closing receivable."
        :steps="[
            ['title' => 'Choose an agent', 'body' => 'Use the search box on this page or Agent in the header.'],
            ['title' => 'Set the period', 'body' => 'Opening is everything due before From; lines are activity inside the range.'],
            ['title' => 'Follow the running balance', 'body' => 'Debits increase what they owe; credits (receipts, advances, credits) reduce it.'],
            ['title' => 'Export or print', 'body' => 'Use export actions for PDF/CSV with the same agent and dates.'],
        ]"
        note="No agent selected means no statement — the picker is required because balances are per customer."
        :open="false"
    />
</x-report.page>
@endsection
