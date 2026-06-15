@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
@endphp

<x-report.page
    eyebrow="Financial statements"
    title="Balance sheet"
    subtitle="What the company owns, owes, and shareholders' equity at a point in time."
    :period="'As of ' . $asOf->format('d M Y')"
>
    <x-slot:actions>
        <x-report.header-actions
            :as-of-action="route('admin.reports.bs')"
            as-of-name="date"
            :as-of="$asOf->toDateString()"
        >
            <x-report.export-actions module="balance-sheet" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Total assets" tone="success" :value="$ccy . ' ' . number_format($total_assets, 2)" hint="Everything the company owns" />
        <x-dashboard.kpi label="Total liabilities" tone="warning" :value="$ccy . ' ' . number_format($total_liabilities, 2)" hint="Everything the company owes" />
        <x-dashboard.kpi label="Total equity" tone="brand" :value="$ccy . ' ' . number_format($total_equity, 2)" hint="Net worth after liabilities" />
        <x-dashboard.kpi label="Accounting equation" :value="'Assets = Liab. + Equity'" hint="Snapshot must balance" />
    </x-slot:kpis>

    <x-dashboard.panel title="Statement of financial position" :subtitle="'As at ' . $asOf->format('d F Y')">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <div class="space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assets</h3>
                <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                    <table class="erp-dash-statement__table w-full">
                        <tbody>
                            @include('admin.finance.partials.hierarchical-rows', ['rows' => $sections['assets']['rows'] ?? []])
                            <tr class="bg-success-50/50 dark:bg-success-500/5 border-t border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">Total assets</td>
                                <td class="px-4 py-4 text-right text-lg font-bold tabular-nums text-success-600 dark:text-success-400">{{ number_format($total_assets, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Liabilities</h3>
                    <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                        <table class="erp-dash-statement__table w-full">
                            <tbody>
                                @include('admin.finance.partials.hierarchical-rows', ['rows' => $sections['liabilities']['rows'] ?? []])
                                <tr class="bg-orange-50/50 dark:bg-orange-500/5">
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">Total liabilities</td>
                                    <td class="px-4 py-4 text-right text-lg font-bold tabular-nums text-orange-600 dark:text-orange-400">{{ number_format($total_liabilities, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Equity</h3>
                    <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-gray-800">
                        <table class="erp-dash-statement__table w-full">
                            <tbody>
                                @include('admin.finance.partials.hierarchical-rows', ['rows' => $sections['equity']['rows'] ?? []])
                                <tr class="bg-brand-50/50 dark:bg-brand-500/5 border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">Total equity</td>
                                    <td class="px-4 py-4 text-right text-lg font-bold tabular-nums text-brand-600 dark:text-brand-400">{{ number_format($total_equity, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-xl border border-brand-100 bg-brand-50/50 px-5 py-4 dark:border-brand-900/30 dark:bg-brand-500/5">
            <div class="flex flex-wrap items-center justify-center gap-4 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Assets <strong class="text-success-600 dark:text-success-400">{{ number_format($total_assets, 2) }}</strong></span>
                <span class="text-gray-400">=</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Liabilities <strong class="text-orange-600 dark:text-orange-400">{{ number_format($total_liabilities, 2) }}</strong></span>
                <span class="text-gray-400">+</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">Equity <strong class="text-brand-600 dark:text-brand-400">{{ number_format($total_equity, 2) }}</strong></span>
            </div>
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
