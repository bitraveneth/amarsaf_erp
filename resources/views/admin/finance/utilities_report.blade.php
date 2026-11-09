@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
@endphp

<x-report.page
    eyebrow="Cost reports"
    title="Utilities & electricity"
    subtitle="Electricity and utility bills recorded under the utilities expense category."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.utilities')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="costs" />
            <x-report.export-actions module="utilities-report" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Utilities spend"
            :value="$ccy . ' ' . number_format($total, 2)"
            :hint="$lines->count() . ' ' . \Illuminate\Support\Str::plural('line', $lines->count())"
        />
        <x-dashboard.kpi
            label="Period"
            :value="$periodLabel"
            hint="Change range in the header"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Utility expense lines</h2>
                <p class="erp-caption">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
            </div>
            <p class="erp-caption">{{ number_format($lines->count()) }} rows</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th class="is-right">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                        @php
                            $status = strtolower((string) $line->status);
                            $statusClass = match ($status) {
                                'reviewed', 'paid' => 'erp-badge-success',
                                'recorded' => 'erp-badge-brand',
                                default => 'erp-badge-warning',
                            };
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">{{ $line->date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $line->description ?: '—' }}</td>
                            <td><span class="font-mono text-sm">{{ $line->reference ?: '—' }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format((float) $line->amount, 2) }}</span></td>
                            <td><span class="{{ $statusClass }}">{{ ucfirst($line->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state
                                    title="No utility expenses in this period"
                                    description="Expenses tagged under utilities will appear here. Try another date range or record a utilities expense."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($lines->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3" class="is-right font-semibold text-gray-900 dark:text-white">Total</td>
                            <td class="is-right"><span class="erp-table-num font-semibold">{{ number_format($total, 2) }}</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-report.page>
@endsection