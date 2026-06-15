@extends('layouts.app')

@section('content')
@php
    $lineMax = $byLine->max() ?: 0;
    $shiftMax = $byShift->max() ?: 0;

    $opsCards = [
        [
            'label' => 'Open QC',
            'numeric' => number_format($openQcCount),
            'caption' => 'Runs waiting for quality check',
            'href' => route('admin.production.index'),
            'tone' => 'orange',
            'valueTone' => $openQcCount > 0 ? 'warning' : 'neutral',
            'icon' => 'alert',
        ],
        [
            'label' => 'Awaiting stock',
            'numeric' => number_format($awaitingStockCount),
            'caption' => 'QC approved, not posted to inventory',
            'href' => route('admin.production.pending-receipts'),
            'tone' => 'blue',
            'valueTone' => $awaitingStockCount > 0 ? 'warning' : 'neutral',
            'icon' => 'delivery',
        ],
        [
            'label' => 'Expiring soon',
            'numeric' => number_format($expiringSoonCount),
            'caption' => 'Batches expiring within 60 days',
            'href' => route('admin.batches.index'),
            'tone' => 'error',
            'valueTone' => $expiringSoonCount > 0 ? 'danger' : 'neutral',
            'icon' => 'alert',
        ],
    ];

    $performanceCards = [
        [
            'label' => 'Production runs',
            'numeric' => number_format($totalRuns),
            'caption' => $periodLabel,
            'href' => route('admin.production.index', request()->only(['range', 'from', 'to'])),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Total output',
            'numeric' => number_format($totalQuantity, 0),
            'caption' => number_format($approvedQuantity, 0) . ' QC approved in period',
            'href' => route('admin.production.index', request()->only(['range', 'from', 'to'])),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'QC approval rate',
            'numeric' => number_format($qcApprovalRate, 0) . '%',
            'caption' => $pendingQcInPeriod . ' run' . ($pendingQcInPeriod === 1 ? '' : 's') . ' pending in period',
            'href' => route('admin.production.index', request()->only(['range', 'from', 'to'])),
            'tone' => 'success',
            'valueTone' => $qcApprovalRate >= 90 ? 'neutral' : 'warning',
            'icon' => 'production',
        ],
        [
            'label' => 'Batches created',
            'numeric' => number_format($batchCount),
            'caption' => 'Lots with production date in period',
            'href' => route('admin.batches.index'),
            'tone' => 'purple',
            'valueTone' => 'neutral',
            'icon' => 'delivery',
        ],
    ];

    $qcStatusLabels = [
        'approved' => 'Approved',
        'pending' => 'Pending',
        'rejected' => 'Rejected',
    ];
    $qcStatusTones = [
        'approved' => 'bg-success-50 text-success-700 dark:bg-success-500/20 dark:text-success-300',
        'pending' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
        'rejected' => 'bg-error-50 text-error-700 dark:bg-error-500/20 dark:text-error-300',
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-dashboard.page-header
            title="Manufacturing dashboard"
            subtitle="Operations at a glance, plus period performance when you need it."
        />
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.boms.index') }}" class="erp-btn-secondary">BOMs</a>
            <a href="{{ route('admin.production.create') }}" class="erp-btn-primary">New production run</a>
        </div>
    </div>

    {{-- Zone 1: Operations (live — not affected by reporting period) --}}
    <section class="mt-2 space-y-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <x-admin.order-workflow
                type="manufacturing"
                :step="$flowStep"
                :in-progress="$flowInProgress"
                variant="procurement"
                label="Manufacturing process"
            />
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                @if($productsWithoutActiveBom > 0)
                    <a href="{{ route('admin.boms.create') }}" class="erp-order-btn erp-order-btn--secondary">
                        Set up recipes ({{ $productsWithoutActiveBom }} products)
                    </a>
                @endif
                <a href="{{ route('admin.production.create') }}" class="erp-order-btn erp-order-btn--primary">
                    New production run
                </a>
                @if($openQcCount > 0)
                    <a href="{{ route('admin.production.index') }}" class="erp-order-btn erp-order-btn--secondary">
                        Open QC ({{ $openQcCount }})
                    </a>
                @endif
                @if($awaitingStockCount > 0)
                    <a href="{{ route('admin.production.pending-receipts') }}" class="erp-order-btn erp-order-btn--secondary">
                        Post to stock ({{ $awaitingStockCount }})
                    </a>
                @endif
            </div>
        </div>

        @if($productsNeedingRecipe->isNotEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
                <h2 class="text-sm font-semibold text-amber-950 dark:text-amber-100">Products waiting for an active recipe (step 1)</h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach($productsNeedingRecipe as $product)
                        <li>
                            <a href="{{ route('admin.boms.create', ['product_id' => $product->id]) }}"
                               class="inline-flex rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-500/40 dark:bg-amber-950/40 dark:text-amber-100">
                                {{ $product->sku ? $product->sku . ' — ' : '' }}{{ $product->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-dashboard.snapshot-kpis
            eyebrow="Operations"
            title="What needs attention now"
            description="Live QC backlog, stock posting queue, and batch expiry — always current."
            :cards="$opsCards"
        />

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-dashboard.section-header
                title="Expiring batches"
                description="Stock batches expiring within 60 days — not filtered by reporting period."
                class="mb-4"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.batches.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">All batches</a>
                </x-slot:actions>
            </x-dashboard.section-header>
            <div class="erp-table-card">
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Product</th>
                                <th>Expires</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringSoon->take(5) as $batch)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.batches.show', $batch) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                            {{ $batch->batch_code ?? ('Batch #' . $batch->id) }}
                                        </a>
                                    </td>
                                    <td>{{ $batch->product->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $batch->expiry_date?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-sm text-gray-500">No batches expiring soon.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>

    {{-- Zone 2: Performance (reporting period drives all content below) --}}
    <section class="mt-8 space-y-2 rounded-2xl border border-gray-200/80 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/40 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Performance</p>
                <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">Output in selected period</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-dashboard.period-filter
                    variant="compact"
                    :action="route('admin.manufacturing.dashboard')"
                    :range="$range"
                    :from="request('from', $from->toDateString())"
                    :to="request('to', $to->toDateString())"
                    :range-options="$rangeOptions"
                    :period-label="$periodLabel"
                />
                <a href="{{ route('admin.reports.production', $reportPeriodParams) }}"
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-brand-600 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-brand-400 dark:hover:bg-white/[0.03]">
                    Full report
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>

        <x-dashboard.snapshot-kpis
            :show-header="false"
            :cards="$performanceCards"
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <x-dashboard.section-header
                    title="Output by line"
                    description="Quantity produced per production line in this period."
                    class="mb-4"
                />
                @if($byLine->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No line data for this period.</p>
                @else
                    <dl class="space-y-3">
                        @foreach($byLine as $line => $qty)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <dt class="text-gray-600 dark:text-gray-400">Line {{ $line ?: '—' }}</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($qty, 0) }}</dd>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded-full bg-brand-500 transition-all"
                                         style="width: {{ $lineMax > 0 ? min(100, ($qty / $lineMax) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <x-dashboard.section-header
                    title="Output by shift"
                    description="Shift-level production totals in this period."
                    class="mb-4"
                />
                @if($byShift->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No shift data for this period.</p>
                @else
                    <dl class="space-y-3">
                        @foreach($byShift as $shift => $qty)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <dt class="text-gray-600 dark:text-gray-400">Shift {{ $shift ?: '—' }}</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ number_format($qty, 0) }}</dd>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-full rounded-full bg-brand-500 transition-all"
                                         style="width: {{ $shiftMax > 0 ? min(100, ($qty / $shiftMax) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>
        </div>

        <section>
            <x-dashboard.section-header
                title="Top products"
                description="Highest output in the selected period."
                class="mb-4"
            />
            <div class="erp-table-card">
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="is-right">Qty</th>
                                <th class="is-right">Runs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $row)
                                <tr>
                                    <td class="font-medium text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                                    <td class="is-right erp-table-num">{{ number_format($row['value'], 0) }}</td>
                                    <td class="is-right text-sm text-gray-500 dark:text-gray-400">{{ $row['meta'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-sm text-gray-500">No production runs in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section>
            <x-dashboard.section-header
                title="Recent production runs"
                description="Latest runs recorded in the selected period."
                class="mb-4"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.production.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">View all</a>
                    <a href="{{ route('admin.production.pending-receipts') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Pending receipts</a>
                </x-slot:actions>
            </x-dashboard.section-header>
            <div class="erp-table-card">
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Run</th>
                                <th>Product</th>
                                <th>Line</th>
                                <th class="is-right">Qty</th>
                                <th>QC</th>
                                <th>Date</th>
                                <th class="is-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRuns as $run)
                                @php
                                    $qcKey = $run->qc_status ?? 'pending';
                                    $qcClass = $qcStatusTones[$qcKey] ?? $qcStatusTones['pending'];
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.production.show', $run) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                            {{ $run->order_number ?? ('Run #' . $run->id) }}
                                        </a>
                                    </td>
                                    <td>{{ $run->product->name ?? '—' }}</td>
                                    <td>{{ $run->line ?: '—' }}</td>
                                    <td class="is-right erp-table-num">{{ number_format($run->quantity, 0) }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $qcClass }}">
                                            {{ $qcStatusLabels[$qcKey] ?? ucfirst($qcKey) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $run->created_at->format('d M Y') }}</td>
                                    <td class="is-right">
                                        <a href="{{ $run->repeatCreateUrl() }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                            Run again
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8 text-center text-sm text-gray-500">No production runs in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>
</div>
@endsection
