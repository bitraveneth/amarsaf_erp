@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M5 9h14M7 13h10M9 17h6" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Accounting dashboard
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        High‑level view of sales, expenses, payroll and cash collection.
                    </p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Period: {{ $periodLabel }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16v2H4zM4 11h10v2H4zM4 16h7v2H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Customer invoices</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalInvoices) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Issued this month</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 5h16v14H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Net sales (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($netSales, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    VAT: {{ number_format($vatTotal, 0) }} · Withholding: {{ number_format($withholdingTotal, 0) }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Collected (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-success-600 dark:text-success-400">
                    {{ number_format($collected, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Customer receipts received this month
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">
                    {{ number_format($outstanding, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Net of receipts, withholding &amp; credit notes
                </p>
            </div>
        </div>
    </div>

    {{-- Expense / payroll / profit --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Operating expenses</h2>
            <p class="text-2xl font-bold text-error-600 dark:text-error-400">
                {{ number_format($totalExpenses, 0) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                From the Expenses module for this month.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Payroll & allowances</h2>
            <p class="text-2xl font-bold text-error-600 dark:text-error-400">
                {{ number_format($totalPayroll, 0) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Sum of salary distributions in this period.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Net profit (rough)</h2>
            <p class="text-2xl font-bold {{ $netProfitEstimate >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                {{ number_format($netProfitEstimate, 0) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Net sales – expenses – payroll (approximate, not GAAP).
            </p>
        </div>
    </div>
</div>
@endsection

