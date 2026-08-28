@extends('layouts.app')

@section('content')
@php
    $monthKey = $month->format('Y-m');
    $initials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = mb_substr($parts[1] ?? $parts[0] ?? '', $parts[1] ?? null ? 0 : 1, 1);

        return mb_strtoupper($first.$second);
    };
@endphp

<div
    class="erp-target-shell"
    x-data="{
        company: {{ (float) old('company_target', $companyTarget?->target_value ?? 0) }},
        agents: {{ \Illuminate\Support\Js::from($agents->mapWithKeys(fn ($a) => [$a->id => (float) old('agent_targets.'.$a->id, $agentAmounts[$a->id] ?? 0)])->all()) }},
        sellers: {{ \Illuminate\Support\Js::from($employees->mapWithKeys(fn ($e) => [$e->id => (float) old('employee_targets.'.$e->id, $employeeAmounts[$e->id] ?? 0)])->all()) }},
        agentQuery: '',
        sellerQuery: '',
        get allocated() {
            const sum = (obj) => Object.values(obj).reduce((t, n) => t + (Number(n) || 0), 0);
            return sum(this.agents) + sum(this.sellers);
        },
        get gap() { return (Number(this.company) || 0) - this.allocated; },
        get fill() {
            const company = Number(this.company) || 0;
            if (company <= 0) return 0;
            return Math.min(100, Math.max(0, (this.allocated / company) * 100));
        },
        format(n) {
            return 'BDT ' + (Number(n) || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
        },
        gapClass() {
            if (Math.abs(this.gap) < 0.5) return '';
            return this.gap < 0 ? 'text-error-600 dark:text-error-400' : 'text-orange-600 dark:text-orange-400';
        },
        gapHint() {
            if (Math.abs(this.gap) < 0.5) return 'Fully distributed';
            return this.gap < 0 ? 'Over the company number' : 'Still unassigned';
        },
        matches(name, query) {
            const q = (query || '').trim().toLowerCase();
            if (!q) return true;
            return (name || '').toLowerCase().includes(q);
        }
    }"
>
    <x-admin.page-header
        icon="chart"
        eyebrow="Sales"
        title="Set company target"
        :subtitle="'Company number for '.$month->format('F Y').', then split it across agents and sellers.'"
    >
        <x-slot:actions>
            <form method="GET" action="{{ route('admin.sales-targets.create') }}" class="flex items-center gap-2">
                <label class="sr-only" for="target-month">Month</label>
                <input id="target-month" type="month" name="month" value="{{ $monthKey }}" class="erp-input erp-input--sm w-auto" onchange="this.form.submit()">
            </form>
            <a href="{{ route('admin.sales-targets.index', ['month' => $monthKey]) }}" class="erp-btn-secondary">Board</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.sales-targets.store') }}" method="POST" class="space-y-5">
        @csrf
        <input type="hidden" name="month" value="{{ $monthKey }}">

            <section class="erp-target-hero">
                <x-admin.hover-hint
                    class="erp-hover-hint--start"
                    title="Company target"
                    text="The month’s company number. Split it to agents and sellers below. You can save even if the split is not exact — leftover stays unassigned."
                >
                    <p class="erp-stat-label">Company target</p>
                    <div class="relative mt-3">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">BDT</span>
                        <input
                            id="company_target"
                            name="company_target"
                            type="number"
                            step="0.01"
                            min="0"
                            x-model.number="company"
                            value="{{ old('company_target', $companyTarget?->target_value ?? '') }}"
                            class="erp-target-hero__input"
                            placeholder="0"
                        >
                    </div>
                </x-admin.hover-hint>
                @error('company_target')
                    <p class="erp-form-error mt-2">{{ $message }}</p>
                @enderror

                <div class="erp-dash-progress mt-5">
                    <div class="erp-dash-progress__head">
                        <span class="erp-dash-progress__label">Distributed</span>
                        <span class="erp-dash-progress__value" x-text="Math.round(fill) + '%'"></span>
                    </div>
                    <div class="erp-dash-progress__track">
                        <div class="erp-dash-progress__fill" :style="'width:' + fill + '%'"></div>
                    </div>
                </div>

                <div class="erp-target-hero__meta">
                    <x-admin.hover-hint class="h-full" title="Allocated" text="Sum of every agent and seller amount on this page.">
                        <div class="erp-target-chip h-full">
                            <p class="erp-stat-label">Allocated</p>
                            <p class="erp-metric-value mt-1" x-text="format(allocated)"></p>
                        </div>
                    </x-admin.hover-hint>
                    <x-admin.hover-hint title="Unassigned" text="Company target minus allocations. Negative means you gave out more than the company number — you can still save.">
                        <div class="erp-target-chip">
                            <p class="erp-stat-label">Unassigned</p>
                            <p class="erp-metric-value mt-1" :class="gapClass()" x-text="format(gap)"></p>
                        </div>
                    </x-admin.hover-hint>
                    <x-admin.hover-hint title="Status" text="A perfect match is optional. Leftover is simply not given to anyone yet.">
                        <div class="erp-target-chip">
                            <p class="erp-stat-label">Status</p>
                            <p class="erp-body-strong mt-2" x-text="gapHint()"></p>
                        </div>
                    </x-admin.hover-hint>
                </div>
            </section>

        <div class="erp-target-split">
            <section class="erp-target-list">
                <div class="erp-target-list__head">
                    <x-admin.hover-hint
                        class="erp-hover-hint--start min-w-0"
                        title="Agents"
                        text="Each dealer’s share of the company number. Achievement is invoiced net sales for that agent. Leave 0 to clear that person’s target."
                    >
                        <h2 class="erp-h3">Agents</h2>
                        <p class="erp-caption mt-1">{{ $agents->count() }} dealers · leave 0 to clear</p>
                    </x-admin.hover-hint>
                    <input type="search" x-model="agentQuery" placeholder="Find agent" class="erp-target-search" aria-label="Find agent">
                </div>
                    <div class="erp-target-list__body">
                        @forelse($agents as $agent)
                            <div class="erp-target-row" x-show="matches(@js($agent->name), agentQuery)" x-cloak>
                                <div class="erp-target-person">
                                    <span class="erp-target-avatar" aria-hidden="true">{{ $initials($agent->name) }}</span>
                                    <div class="min-w-0">
                                        <p class="erp-body-strong truncate">{{ $agent->name }}</p>
                                        @if($agent->zone)
                                            <p class="erp-caption truncate">{{ $agent->zone }}</p>
                                        @endif
                                    </div>
                                </div>
                                <label class="erp-target-amount">
                                    <span class="sr-only">Target for {{ $agent->name }}</span>
                                    <input
                                        type="number"
                                        name="agent_targets[{{ $agent->id }}]"
                                        step="0.01"
                                        min="0"
                                        x-model.number="agents[{{ $agent->id }}]"
                                        value="{{ old('agent_targets.'.$agent->id, $agentAmounts[$agent->id] ?? '') }}"
                                        class="erp-input erp-input--sm text-right"
                                    >
                                </label>
                            </div>
                        @empty
                            <div class="px-5 py-10">
                                <x-admin.empty-state title="No agents found" />
                            </div>
                        @endforelse
                    </div>
                </section>

            <section class="erp-target-list">
                <div class="erp-target-list__head">
                    <x-admin.hover-hint
                        class="erp-hover-hint--start min-w-0"
                        title="Sellers"
                        text="Sales employees. Achievement uses completed visit-plan agents, then the employee work zone. Leave 0 to clear that person’s target."
                    >
                        <h2 class="erp-h3">Sellers</h2>
                        <p class="erp-caption mt-1">{{ $employees->count() }} people · leave 0 to clear</p>
                    </x-admin.hover-hint>
                    <input type="search" x-model="sellerQuery" placeholder="Find seller" class="erp-target-search" aria-label="Find seller">
                </div>
                    <div class="erp-target-list__body">
                        @forelse($employees as $employee)
                            <div class="erp-target-row" x-show="matches(@js($employee->name), sellerQuery)" x-cloak>
                                <div class="erp-target-person">
                                    <span class="erp-target-avatar" aria-hidden="true">{{ $initials($employee->name) }}</span>
                                    <div class="min-w-0">
                                        <p class="erp-body-strong truncate">{{ $employee->name }}</p>
                                        <p class="erp-caption truncate">{{ $employee->job_position ?: $employee->department ?: 'Sales' }}{{ $employee->work_zone ? ' · '.$employee->work_zone : '' }}</p>
                                    </div>
                                </div>
                                <label class="erp-target-amount">
                                    <span class="sr-only">Target for {{ $employee->name }}</span>
                                    <input
                                        type="number"
                                        name="employee_targets[{{ $employee->id }}]"
                                        step="0.01"
                                        min="0"
                                        x-model.number="sellers[{{ $employee->id }}]"
                                        value="{{ old('employee_targets.'.$employee->id, $employeeAmounts[$employee->id] ?? '') }}"
                                        class="erp-input erp-input--sm text-right"
                                    >
                                </label>
                            </div>
                        @empty
                            <div class="px-5 py-10">
                                <x-admin.empty-state title="No sales employees found" description="Add a sales job title or department on the employee record." />
                            </div>
                        @endforelse
                    </div>
                </section>
        </div>

        <div class="erp-form-actions rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-caption mr-auto">A perfect split is optional — leftover stays unassigned.</p>
            <a href="{{ route('admin.sales-targets.index', ['month' => $monthKey]) }}" class="erp-btn-secondary">Cancel</a>
            <button type="submit" class="erp-btn-primary">Save allocations</button>
        </div>
    </form>
</div>
@endsection
