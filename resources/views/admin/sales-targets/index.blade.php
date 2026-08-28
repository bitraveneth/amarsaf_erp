@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
    $monthKey = $month->format('Y-m');
    $companyRow = $rows->first(fn ($row) => $row['target']->isCompany());
    $agentRows = $rows->filter(fn ($row) => ! $row['target']->isCompany() && $row['target']->agent_id)->values();
    $sellerRows = $rows->filter(fn ($row) => ! $row['target']->isCompany() && $row['target']->employee_id)->values();
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = mb_substr($parts[1] ?? $parts[0] ?? '', $parts[1] ?? null ? 0 : 1, 1);

        return mb_strtoupper($first.$second);
    };
@endphp

<div class="erp-target-shell">
    <x-admin.page-header
        icon="chart"
        eyebrow="Sales"
        title="Sales targets"
        :subtitle="'Who holds how much of the company number for '.$month->format('F Y').'.'"
    >
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.sales-targets.index') }}" class="flex items-center gap-2">
                <label class="sr-only" for="board-month">Month</label>
                <input id="board-month" type="month" name="month" value="{{ $monthKey }}" class="erp-input erp-input--sm w-auto" onchange="this.form.submit()">
            </form>
            <a href="{{ route('admin.sales-targets.create', ['month' => $monthKey]) }}" class="erp-btn-primary">Set / distribute</a>
        </x-slot:actions>
    </x-admin.page-header>

        <section class="erp-target-hero">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <x-admin.hover-hint
                    class="erp-hover-hint--start"
                    title="Company target"
                    text="The month’s company number. Achieved is invoiced net sales for everyone. Hover a person card below to see their split."
                >
                    <p class="erp-stat-label">Company · {{ $month->format('F Y') }}</p>
                    <p class="erp-metric-value mt-2">{{ $ccy }} {{ number_format($summary['company'], 0) }}</p>
                </x-admin.hover-hint>
                @if($companyRow)
                    <span class="erp-badge-brand">Company target</span>
                @endif
            </div>

            <x-dashboard.progress
                class="mt-5"
                label="Company achieved"
                :percent="$summary['progress']"
                :hint="$ccy.' '.number_format($summary['achieved'], 0).' invoiced'"
                :tone="$summary['progress'] >= 100 ? 'success' : 'brand'"
            />

            <div class="erp-target-hero__meta">
                <x-admin.hover-hint title="Allocated" text="Sum of agent and seller targets for this month.">
                    <div class="erp-target-chip">
                        <p class="erp-stat-label">Allocated</p>
                        <p class="erp-metric-value mt-1">{{ $ccy }} {{ number_format($summary['allocated'], 0) }}</p>
                    </div>
                </x-admin.hover-hint>
                <x-admin.hover-hint title="Unassigned" text="Company target minus what you gave to agents and sellers. Set a company number to track leftover.">
                    <div class="erp-target-chip">
                        <p class="erp-stat-label">Unassigned</p>
                        <p @class(['erp-metric-value mt-1', 'text-error-600 dark:text-error-400' => $summary['unassigned'] < -0.5, 'text-orange-600 dark:text-orange-400' => $summary['unassigned'] > 0.5 && $summary['company'] > 0])>
                            {{ $ccy }} {{ number_format($summary['unassigned'], 0) }}
                        </p>
                    </div>
                </x-admin.hover-hint>
                <x-admin.hover-hint title="Achieved" text="Invoiced net sales for the whole company in this month.">
                    <div class="erp-target-chip">
                        <p class="erp-stat-label">Achieved</p>
                        <p class="erp-metric-value mt-1 text-success-600 dark:text-success-400">{{ $ccy }} {{ number_format($summary['achieved'], 0) }}</p>
                    </div>
                </x-admin.hover-hint>
            </div>
        </section>

    @if($rows->isEmpty())
        <div class="erp-target-list px-5 py-12">
            <x-admin.empty-state
                title="No targets for this month"
                description="Set the company number, then split it across agents and sellers."
            >
                <x-slot:action>
                    <a href="{{ route('admin.sales-targets.create', ['month' => $monthKey]) }}" class="erp-btn-primary">Set company target</a>
                </x-slot:action>
            </x-admin.empty-state>
        </div>
    @else
        <div class="erp-target-split">
            <section class="erp-target-list">
                <div class="erp-target-list__head">
                    <x-admin.hover-hint
                        class="erp-hover-hint--start min-w-0"
                        title="Agents"
                        text="Each dealer’s share of the company number. Progress is invoiced net sales for that agent."
                    >
                        <h2 class="erp-h3">Agents</h2>
                        <p class="erp-caption mt-1">{{ $agentRows->count() }} dealers</p>
                    </x-admin.hover-hint>
                </div>
                <div class="space-y-3 p-4">
                    @forelse($agentRows as $row)
                        @php($target = $row['target'])
                        @php($share = $summary['company'] > 0 ? round(((float) $target->target_value / $summary['company']) * 100, 1) : null)
                        <x-admin.hover-hint
                            class="erp-hover-hint--start block"
                            :title="$target->ownerName()"
                            :text="'Still remaining '.$ccy.' '.number_format($row['remaining'], 0).' against this dealer’s target.'"
                        >
                            <article class="erp-target-person-card">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="erp-target-person">
                                        <span class="erp-target-avatar" aria-hidden="true">{{ $initials($target->ownerName()) }}</span>
                                        <div class="min-w-0">
                                            <p class="erp-body-strong truncate">{{ $target->ownerName() }}</p>
                                            <p class="erp-caption">{{ $share === null ? 'Agent' : $share.'% of company' }}</p>
                                        </div>
                                    </div>
                                    <x-admin.action-edit :href="route('admin.sales-targets.edit', $target)" />
                                </div>
                                <x-dashboard.progress
                                    class="mt-4"
                                    label="{{ $ccy }} {{ number_format((float) $target->target_value, 0) }}"
                                    :percent="$row['progress']"
                                    :hint="'Achieved '.$ccy.' '.number_format($row['achieved'], 0)"
                                    :tone="$row['progress'] >= 100 ? 'success' : 'brand'"
                                />
                            </article>
                        </x-admin.hover-hint>
                    @empty
                        <x-admin.empty-state title="No agent targets this month" />
                    @endforelse
                </div>
            </section>

            <section class="erp-target-list">
                <div class="erp-target-list__head">
                    <x-admin.hover-hint
                        class="erp-hover-hint--start min-w-0"
                        title="Sellers"
                        text="Sales employees. Progress uses completed visit-plan agents, then the employee work zone."
                    >
                        <h2 class="erp-h3">Sellers</h2>
                        <p class="erp-caption mt-1">{{ $sellerRows->count() }} people</p>
                    </x-admin.hover-hint>
                </div>
                <div class="space-y-3 p-4">
                    @forelse($sellerRows as $row)
                        @php($target = $row['target'])
                        @php($share = $summary['company'] > 0 ? round(((float) $target->target_value / $summary['company']) * 100, 1) : null)
                        <x-admin.hover-hint
                            class="erp-hover-hint--start block"
                            :title="$target->ownerName()"
                            :text="'Still remaining '.$ccy.' '.number_format($row['remaining'], 0).' against this seller’s target.'"
                        >
                            <article class="erp-target-person-card">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="erp-target-person">
                                        <span class="erp-target-avatar" aria-hidden="true">{{ $initials($target->ownerName()) }}</span>
                                        <div class="min-w-0">
                                            <p class="erp-body-strong truncate">{{ $target->ownerName() }}</p>
                                            <p class="erp-caption">{{ $share === null ? 'Seller' : $share.'% of company' }}</p>
                                        </div>
                                    </div>
                                    <x-admin.action-edit :href="route('admin.sales-targets.edit', $target)" />
                                </div>
                                <x-dashboard.progress
                                    class="mt-4"
                                    label="{{ $ccy }} {{ number_format((float) $target->target_value, 0) }}"
                                    :percent="$row['progress']"
                                    :hint="'Achieved '.$ccy.' '.number_format($row['achieved'], 0)"
                                    :tone="$row['progress'] >= 100 ? 'success' : 'brand'"
                                />
                            </article>
                        </x-admin.hover-hint>
                    @empty
                        <x-admin.empty-state title="No seller targets this month" />
                    @endforelse
                </div>
            </section>
        </div>
    @endif
</div>
@endsection
