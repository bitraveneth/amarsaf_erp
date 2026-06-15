@props([
    'treasury' => [],
])

@php
    $currency = $treasury['currency_code'] ?? config('app.currency', 'BDT');
@endphp

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Cash & payables"
        description="Collections, receivables, supplier bills, and net working capital."
        class="mb-5"
    />

    <div class="dash-performance-widget">
        <div class="dash-treasury-grid">
            <a href="{{ route('admin.finance.index') }}" class="dash-treasury-card">
                <span class="dash-treasury-card__label">MTD collections</span>
                <span class="dash-treasury-card__value">{{ $currency }} {{ number_format((float) ($treasury['collections'] ?? 0), 0) }}</span>
                <span class="dash-treasury-card__caption">{{ number_format((float) ($treasury['collection_rate'] ?? 0), 1) }}% of invoiced sales collected</span>
            </a>
            <a href="{{ route('admin.reports.ar-aging') }}" class="dash-treasury-card">
                <span class="dash-treasury-card__label">Outstanding receivables</span>
                <span class="dash-treasury-card__value">{{ $currency }} {{ number_format((float) ($treasury['outstanding_ar'] ?? 0), 0) }}</span>
                <span class="dash-treasury-card__caption">Open customer balances</span>
            </a>
            <a href="{{ route('admin.reports.ap-aging') }}" class="dash-treasury-card">
                <span class="dash-treasury-card__label">Outstanding payables</span>
                <span class="dash-treasury-card__value">{{ $currency }} {{ number_format((float) ($treasury['outstanding_ap'] ?? 0), 0) }}</span>
                <span class="dash-treasury-card__caption">{{ number_format($treasury['bills_due_week'] ?? 0) }} bill(s) due within 7 days</span>
            </a>
            <a href="{{ route('admin.accounting.dashboard') }}" class="dash-treasury-card dash-treasury-card--accent">
                <span class="dash-treasury-card__label">Net position (AR − AP)</span>
                <span class="dash-treasury-card__value">{{ $currency }} {{ number_format((float) ($treasury['net_position'] ?? 0), 0) }}</span>
                <span class="dash-treasury-card__caption">Receivables minus open supplier bills</span>
            </a>
        </div>
    </div>
</section>
