@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $typeHint = ($byType ?? collect())->take(3)->map(fn ($count, $type) => ucfirst(str_replace('_', ' ', $type)) . ' ' . $count)->implode(' · ');
@endphp

<x-report.page
    eyebrow="Accounting reports"
    title="Journal register"
    subtitle="Posted vouchers with ledger lines for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.journal-register')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="accountant" />
            <a href="{{ route('admin.journals.index') }}" class="erp-btn-secondary">All journals</a>
            <x-report.export-actions module="journal-register" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Posted journals" :value="number_format($entries->count())" :hint="$typeHint ?: 'In selected period'" />
        <x-dashboard.kpi label="Ledger lines" :value="number_format($lineCount ?? 0)" hint="Across all vouchers" />
        <x-dashboard.kpi label="Total debit" :value="$ccy . ' ' . number_format($debitTotal ?? 0, 0)" hint="Period movement" />
        <x-dashboard.kpi label="Total credit" :value="$ccy . ' ' . number_format($creditTotal ?? 0, 0)" hint="Must match debit" />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Posted vouchers</h2>
                <p class="erp-caption">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} · Click a journal to open the voucher</p>
            </div>
            <p class="erp-caption">{{ number_format($entries->count()) }} journals</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="table-layout: fixed; width: 100%;">
                <colgroup>
                    <col style="width: 8rem">
                    <col style="width: 9rem">
                    <col style="width: 8rem">
                    <col>
                    <col style="width: 8rem">
                    <col style="width: 10rem">
                    <col style="width: 10rem">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Journal</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Lines</th>
                        <th class="is-right">Debit</th>
                        <th class="is-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $debit = (float) $entry->lines->sum('debit');
                            $credit = (float) $entry->lines->sum('credit');
                            $accounts = $entry->lines
                                ->map(fn ($line) => $line->account?->code)
                                ->filter()
                                ->unique()
                                ->take(3)
                                ->implode(', ');
                            $typeLabel = $entry->journal_type
                                ? ucfirst(str_replace('_', ' ', $entry->journal_type))
                                : 'Other';
                        @endphp
                        <tr
                            class="cursor-pointer"
                            onclick="if (!event.target.closest('a,button')) window.location = this.dataset.href"
                            data-href="{{ route('admin.journals.show', $entry) }}"
                        >
                            <td class="whitespace-nowrap">{{ $entry->entry_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.journals.show', $entry) }}" class="erp-table-link font-mono">{{ $entry->number ?? ('#' . $entry->id) }}</a>
                            </td>
                            <td><span class="erp-badge-brand">{{ $typeLabel }}</span></td>
                            <td>
                                <p class="truncate text-sm text-gray-900 dark:text-white">{{ $entry->description ?: '—' }}</p>
                                @if($accounts !== '')
                                    <p class="erp-caption truncate">{{ $accounts }}{{ $entry->lines->count() > 3 ? '…' : '' }}</p>
                                @endif
                            </td>
                            <td>{{ $entry->lines->count() }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($debit, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($credit, 2) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state title="No posted journals in this period" description="Widen the date range or post journals from expenses, bills, or payroll." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($entries->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="5" class="font-medium text-gray-900 dark:text-white">Period total</td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($debitTotal ?? 0, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($creditTotal ?? 0, 2) }}</span></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-report.page>
@endsection
