@extends('layouts.app')

@section('content')
@php
    $lineMax = $byLine->max() ?: 0;
    $shiftMax = $byShift->max() ?: 0;

    $snapshotCards = [
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
            'caption' => number_format($approvedQuantity, 0) . ' QC approved',
            'href' => route('admin.production.index', request()->only(['range', 'from', 'to'])),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Pending QC',
            'numeric' => number_format($pendingQcCount),
            'caption' => number_format($qcApprovalRate, 0) . '% approval rate',
            'href' => route('admin.production.index', request()->only(['range', 'from', 'to'])),
            'tone' => 'orange',
            'valueTone' => $pendingQcCount > 0 ? 'warning' : 'neutral',
            'icon' => 'alert',
        ],
        [
            'label' => 'Batches / expiry',
            'numeric' => number_format($batchCount),
            'caption' => $expiringSoon->count() . ' expiring in 60 days',
            'href' => route('admin.batches.index'),
            'tone' => 'success',
            'valueTone' => $expiringSoon->count() > 0 ? 'warning' : 'neutral',
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
            subtitle="Production runs, approved output, batch activity, and QC backlog."
        />
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.boms.index') }}" class="erp-btn-secondary">BOMs</a>
            <a href="{{ route('admin.production.create') }}" class="erp-btn-primary">New production run</a>
        </div>
    </div>

    <x-dashboard.period-filter
        class="mt-2"
        :action="route('admin.manufacturing.dashboard')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <section class="mt-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
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
            @if($pendingQcCount > 0)
                <a href="{{ route('admin.production.index') }}" class="erp-order-btn erp-order-btn--secondary">
                    QC backlog ({{ $pendingQcCount }})
                </a>
            @endif
            @if($awaitingStockCount > 0)
                <a href="{{ route('admin.production.pending-receipts') }}" class="erp-order-btn erp-order-btn--secondary">
                    Post to stock ({{ $awaitingStockCount }})
                </a>
            @endif
        </div>
    </section>

    @if($productsNeedingRecipe->isNotEmpty())
        <section class="mt-2 rounded-2xl border border-amber-200 bg-amber-50/80 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
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
        </section>
    @endif

    <x-dashboard.snapshot-kpis
        class="mt-2"
        eyebrow="Manufacturing snapshot"
        title="Output in selected period"
        description="Runs, quantity produced, QC backlog, and batch activity."
        :cards="$snapshotCards"
    />

    <div class="mt-2 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-dashboard.section-header
                title="Output by line"
                description="Quantity produced per production line."
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
                description="Shift-level production totals."
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

    <div class="mt-2 grid grid-cols-1 gap-6 lg:grid-cols-2">
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
                title="Expiring batches"
                description="Stock batches expiring within 60 days."
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
    </div>

    <section class="mt-2">
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
</div>
@endsection
