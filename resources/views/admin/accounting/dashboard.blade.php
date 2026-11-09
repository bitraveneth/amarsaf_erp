@extends('layouts.app')

@section('content')
@php
    $money = fn ($amount) => $currencyCode.' '.number_format((float) $amount, 0);
    $estimateClass = $netProfitEstimate > 0
        ? 'erp-dash-estimate__value--success'
        : ($netProfitEstimate < 0 ? 'erp-dash-estimate__value--danger' : '');
@endphp

<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Accounting"
        title="Accounting dashboard"
        subtitle="Quick view from invoices, expenses, and payroll — not the formal income statement."
    >
        <x-slot:actions>
            <x-dashboard.period-filter
                variant="compact"
                :action="route('admin.accounting.dashboard')"
                :range="$range"
                :from="request('from', $from->toDateString())"
                :to="request('to', $to->toDateString())"
                :range-options="$rangeOptions"
                :period-label="$periodLabel"
            />
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi
            label="Net profit (period)"
            :tone="$netProfitEstimate >= 0 ? 'success' : 'danger'"
            :value="$currencyCode . ' ' . number_format($netProfitEstimate, 0)"
            :hint="'For ' . $periodLabel"
        />
        <x-dashboard.kpi
            label="Today only"
            :tone="$todayProfit >= 0 ? 'brand' : 'warning'"
            :value="$currencyCode . ' ' . number_format($todayProfit, 0)"
            :hint="'Today\'s sales ' . $currencyCode . ' ' . number_format($todaySales, 0) . ' − today\'s costs'"
        />
        <x-dashboard.kpi
            label="Sales (this period)"
            :value="$currencyCode . ' ' . number_format($netSales, 0)"
            :hint="number_format($totalInvoices) . ' invoices issued · ' . $periodLabel"
        />
        <x-dashboard.kpi
            label="Due to suppliers"
            tone="warning"
            :value="$currencyCode . ' ' . number_format($dueBills, 0)"
            :hint="number_format($dueBillCount) . ' bills still unpaid' . ($overdueBillCount > 0 ? ' · ' . number_format($overdueBillCount) . ' overdue' : '')"
        />
    </div>

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi
            label="Still owed by customers"
            tone="warning"
            :value="$currencyCode . ' ' . number_format($outstanding, 0)"
            hint="Open invoices up to today — not only this period’s sales"
        />
        <x-dashboard.kpi
            label="Cash collected"
            tone="success"
            :value="$currencyCode . ' ' . number_format($collected, 0)"
            :hint="number_format($collectionRate, 1) . '% of period sales · money actually received'"
        />
        <x-dashboard.kpi
            label="Expenses (period)"
            tone="danger"
            :value="$currencyCode . ' ' . number_format($totalExpenses, 0)"
            hint="Operating costs, gifts, and campaigns in this period"
        />
        <x-dashboard.kpi
            label="Payroll (period)"
            :value="$currencyCode . ' ' . number_format($totalPayroll, 0)"
            hint="Salary distributions in this period"
        />
    </div>

    <section
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        x-data="{ open: false }"
    >
        <button
            type="button"
            class="flex w-full items-center justify-between gap-4 px-4 py-3.5 text-left transition hover:bg-gray-50/80 sm:px-5 dark:hover:bg-white/[0.02]"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="accounting-quick-tips"
        >
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Quick tips</p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        <span x-show="!open">Why these cards don’t always match · click to open</span>
                        <span x-show="open" x-cloak>Short guide to the numbers above</span>
                    </p>
                </div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <span x-text="open ? 'Hide' : 'Show'"></span>
                <svg
                    class="h-4 w-4 transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </span>
        </button>

        <div
            id="accounting-quick-tips"
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="border-t border-gray-100 px-4 pb-4 pt-3 sm:px-5 sm:pb-5 dark:border-gray-800"
        >
            <div class="grid gap-3 md:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-700 dark:text-brand-300">1 · Profit</p>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Period vs today</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                        <span class="font-medium text-gray-800 dark:text-gray-100">Net profit</span> covers the whole filter range (sales − costs − payroll).
                        <span class="font-medium text-gray-800 dark:text-gray-100">Today</span> is only calendar today, and it does not subtract payroll.
                    </p>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-700 dark:text-brand-300">2 · Money in</p>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Sales · owed · collected</h3>
                    <ul class="mt-1.5 space-y-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                        <li><span class="font-medium text-gray-800 dark:text-gray-100">Sales</span> — invoices issued in this period</li>
                        <li><span class="font-medium text-gray-800 dark:text-gray-100">Still owed</span> — all unpaid balances (older invoices too)</li>
                        <li><span class="font-medium text-gray-800 dark:text-gray-100">Collected</span> — cash received this period</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-700 dark:text-brand-300">3 · Books</p>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">This page vs P&amp;L</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                        The breakdown below matches <span class="font-medium text-gray-800 dark:text-gray-100">Net profit</span>.
                        The
                        <a href="{{ route('admin.reports.pl', ['range' => $range, 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="erp-link">Income statement</a>
                        uses posted journals only, so it can look different.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <x-dashboard.panel title="Cash and invoice trend" subtitle="Bars: invoices issued vs cash collected in the same period">
        <x-dashboard.bar-chart
            :labels="$chartLabels"
            :values="$chartRevenue"
            :secondary="$chartCollections"
            :currency="$currencyCode"
        />
    </x-dashboard.panel>

    <x-dashboard.panel
        title="How net profit is calculated"
        subtitle="Same total as “Net profit (period)” above — shown as a simple breakdown."
    >
        <div class="erp-dash-estimate">
            <div class="erp-dash-estimate__step">
                <p class="erp-dash-estimate__label">Sales</p>
                <p class="erp-dash-estimate__value">{{ $money($netSales) }}</p>
                <p class="erp-dash-estimate__hint">Invoices in this period</p>
            </div>
            <div class="erp-dash-estimate__step">
                <p class="erp-dash-estimate__label">Expenses</p>
                <p class="erp-dash-estimate__value {{ $totalExpenses > 0 ? 'erp-dash-estimate__value--out' : '' }}">− {{ $money($totalExpenses) }}</p>
                <p class="erp-dash-estimate__hint">Costs, gifts, campaigns</p>
            </div>
            <div class="erp-dash-estimate__step">
                <p class="erp-dash-estimate__label">Payroll</p>
                <p class="erp-dash-estimate__value {{ $totalPayroll > 0 ? 'erp-dash-estimate__value--out' : '' }}">− {{ $money($totalPayroll) }}</p>
                <p class="erp-dash-estimate__hint">Salary distributions</p>
            </div>
            <div class="erp-dash-estimate__step erp-dash-estimate__step--result">
                <p class="erp-dash-estimate__label">Net profit</p>
                <p class="erp-dash-estimate__value {{ $estimateClass }}">{{ $money($netProfitEstimate) }}</p>
                <p class="erp-dash-estimate__hint">Matches the Net profit card</p>
            </div>
        </div>
    </x-dashboard.panel>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Invoices & customers" subtitle="What was billed and what is still unpaid">
            <div class="space-y-3">
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Sales this period</div>
                        <div class="erp-dash-cost-row__hint">New invoices issued</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($netSales, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">VAT on those invoices</div>
                        <div class="erp-dash-cost-row__hint">Tracked for tax</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($vatTotal, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Withholding expected</div>
                        <div class="erp-dash-cost-row__hint">Customer tax hold</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($withholdingTotal, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Still owed by customers</div>
                        <div class="erp-dash-cost-row__hint">All open invoices, including older ones</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-orange-600 dark:!text-orange-400">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</div>
                </div>
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Cash in vs costs out" subtitle="Money received this period versus spend">
            <div class="space-y-3">
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Cash collected</div>
                        <div class="erp-dash-cost-row__hint">Receipts banked in the period</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-success-600 dark:!text-success-400">{{ $currencyCode }} {{ number_format($collected, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Operating expenses</div>
                        <div class="erp-dash-cost-row__hint">Spend recorded in the period</div>
                    </div>
                    <div class="erp-dash-cost-row__value">{{ $currencyCode }} {{ number_format($totalExpenses, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Payroll</div>
                        <div class="erp-dash-cost-row__hint">Salary distributions</div>
                    </div>
                    <div class="erp-dash-cost-row__value">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Cash after costs</div>
                        <div class="erp-dash-cost-row__hint">Collected − expenses − payroll</div>
                    </div>
                    <div class="erp-dash-cost-row__value {{ $cashGap >= 0 ? '!text-success-600 dark:!text-success-400' : '!text-orange-600 dark:!text-orange-400' }}">{{ $currencyCode }} {{ number_format($cashGap, 0) }}</div>
                </div>
            </div>
        </x-dashboard.panel>
    </div>
</div>
@endsection
