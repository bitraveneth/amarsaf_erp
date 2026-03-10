@extends('layouts.app')

@section('content')
@php
    $collectionRate = $netSales > 0 ? ($collected / $netSales) * 100 : 0;
    $costBase = $totalExpenses + $totalPayroll;
    $cashGap = $collected - $costBase;
    $profitTone = $netProfitEstimate >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-error-600 dark:text-error-400';
    $gapTone = $cashGap >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-orange-600 dark:text-orange-400';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Accounting</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Simple month-to-date view of invoicing, cash collection, expenses, payroll, and rough profit.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current period</div>
            <div class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ $periodLabel }}</div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[1.3fr,0.7fr]">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Month summary</div>
                    <div class="mt-3 text-4xl font-semibold text-gray-900 dark:text-white">
                        BDT {{ number_format($netSales, 0) }}
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Net sales from invoices issued this month</div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Collected</div>
                        <div class="mt-2 text-xl font-semibold text-emerald-600 dark:text-emerald-400">
                            BDT {{ number_format($collected, 0) }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</div>
                        <div class="mt-2 text-xl font-semibold text-orange-600 dark:text-orange-400">
                            BDT {{ number_format($outstanding, 0) }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoices</div>
                        <div class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalInvoices) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-900 to-gray-800 p-6 text-white shadow-theme-xs dark:border-gray-700">
            <div class="text-xs font-semibold uppercase tracking-wide text-white/60">Profit snapshot</div>
            <div class="mt-3 text-4xl font-semibold">
                BDT {{ number_format($netProfitEstimate, 0) }}
            </div>
            <p class="mt-2 text-sm text-white/70">Net sales minus expenses and payroll.</p>

            <div class="mt-6 grid gap-3">
                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <span class="text-sm text-white/70">Operating expenses</span>
                    <strong class="text-sm">BDT {{ number_format($totalExpenses, 0) }}</strong>
                </div>
                <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                    <span class="text-sm text-white/70">Payroll and allowances</span>
                    <strong class="text-sm">BDT {{ number_format($totalPayroll, 0) }}</strong>
                </div>
            </div>
        </section>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Collection rate</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">
                {{ number_format($collectionRate, 1) }}%
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Collected amount compared with current month net sales.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">VAT tracked</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">
                BDT {{ number_format($vatTotal, 0) }}
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">VAT attached to invoices in this period.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Withholding tracked</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">
                BDT {{ number_format($withholdingTotal, 0) }}
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Expected deduction recorded against current month invoices.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cash after costs</div>
            <div class="mt-3 text-3xl font-semibold {{ $gapTone }}">
                BDT {{ number_format($cashGap, 0) }}
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Collected cash minus expenses and payroll for the same period.</p>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Sales and receivables</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Straight view of billing and cash movement.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Net sales</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Invoice value before VAT</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-gray-900 dark:text-white">BDT {{ number_format($netSales, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Collected receipts</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cash received this month</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-emerald-600 dark:text-emerald-400">BDT {{ number_format($collected, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Outstanding balance</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">After receipts, withholding, and credit notes</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-orange-600 dark:text-orange-400">BDT {{ number_format($outstanding, 0) }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Cost and result</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Simple cost picture for the same period.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Operating expenses</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">From the Expenses module</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-error-600 dark:text-error-400">BDT {{ number_format($totalExpenses, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Payroll and allowances</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Salary distribution total</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-error-600 dark:text-error-400">BDT {{ number_format($totalPayroll, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Net profit estimate</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Approximate management view only</div>
                    </div>
                    <div class="text-right text-lg font-semibold {{ $profitTone }}">BDT {{ number_format($netProfitEstimate, 0) }}</div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
