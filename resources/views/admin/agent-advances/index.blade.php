@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
    $agentOptions = $agents->map(fn ($agent) => [
        'id' => $agent->id,
        'name' => $agent->name,
        'meta' => collect([$agent->phone, $agent->area ?: $agent->zone])->filter()->implode(' · '),
    ])->values();
    $statusLabels = [
        'open' => 'Open',
        'partial' => 'Partial',
        'applied' => 'Applied',
    ];
@endphp
<div class="erp-page">
    <x-admin.page-header
        title="Agent advances"
        subtitle="Prepayments waiting to be applied to invoices."
    >
        <x-slot:actions>
            <div
                class="relative w-full sm:w-72"
                x-data="{
                    open: false,
                    query: '',
                    selectedId: @js((string) request('agent_id', '')),
                    agents: @js($agentOptions),
                    get selected() {
                        return this.agents.find((agent) => String(agent.id) === String(this.selectedId)) || null;
                    },
                    get filtered() {
                        const term = this.query.trim().toLowerCase();
                        if (!term) return this.agents;
                        return this.agents.filter((agent) => (agent.name + ' ' + agent.meta).toLowerCase().includes(term));
                    },
                    toggle() {
                        this.open = !this.open;
                        if (this.open) this.$nextTick(() => this.$refs.agentSearch.focus());
                    },
                    go(id) {
                        const url = new URL(@js(route('admin.agent-advances.index')), window.location.origin);
                        if (id) url.searchParams.set('agent_id', id);
                        window.location = url.toString();
                    },
                }"
                @keydown.escape.window="open = false"
            >
                <button type="button" class="erp-input flex items-center justify-between gap-3 text-left" @click="toggle()" :aria-expanded="open.toString()">
                    <span class="min-w-0 truncate" x-text="selected ? selected.name : 'All agents'"></span>
                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-30 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-md dark:border-gray-700 dark:bg-gray-900">
                    <div class="border-b border-gray-100 p-2 dark:border-gray-800">
                        <input x-ref="agentSearch" x-model="query" type="search" class="erp-input" placeholder="Name, phone, or area" aria-label="Search agents">
                    </div>
                    <ul class="max-h-64 overflow-y-auto py-1">
                        <li>
                            <button type="button" class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800" @click="go('')">
                                <span class="erp-body-strong">All agents</span>
                            </button>
                        </li>
                        <template x-for="agent in filtered" :key="agent.id">
                            <li>
                                <button type="button" class="flex w-full flex-col px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800" @click="go(agent.id)">
                                    <span class="erp-body-strong" x-text="agent.name"></span>
                                    <span class="erp-caption" x-show="agent.meta" x-text="agent.meta"></span>
                                </button>
                            </li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-3 py-4 text-center erp-caption">No matching agents.</li>
                    </ul>
                </div>
            </div>
            <a href="{{ route('admin.agent-advances.create', array_filter(['agent_id' => request('agent_id')])) }}" class="erp-btn-primary">Record advance</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Agent advances"
        :cards="[
            [
                'label' => 'Advances',
                'numeric' => number_format($summary['count']),
                'caption' => 'Records in this list',
                'href' => route('admin.agent-advances.index', request()->query()),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Received',
                'numeric' => number_format($summary['amount'], 2),
                'currency' => $currency,
                'caption' => 'Total advances taken',
                'href' => route('admin.agent-advances.index', request()->query()),
                'tone' => 'purple',
                'valueTone' => 'brand',
                'icon' => 'revenue',
            ],
            [
                'label' => 'Applied',
                'numeric' => number_format($summary['applied'], 2),
                'currency' => $currency,
                'caption' => 'Already used on invoices',
                'href' => route('admin.agent-advances.index', request()->query()),
                'tone' => 'orange',
                'valueTone' => 'neutral',
                'icon' => 'receivables',
            ],
            [
                'label' => 'Available',
                'numeric' => number_format($summary['available'], 2),
                'currency' => $currency,
                'caption' => 'Still waiting to apply',
                'href' => route('admin.agent-advances.index', request()->query()),
                'tone' => 'success',
                'valueTone' => 'success',
                'icon' => 'users',
            ],
        ]"
    />

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Date</th>
                        <th class="is-right">Advance</th>
                        <th class="is-right">Applied</th>
                        <th class="is-right">Available</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $advance)
                        @php
                            $badge = match ($advance->status) {
                                'applied' => 'erp-badge-success',
                                'partial' => 'erp-badge-warning',
                                default => 'erp-badge-brand',
                            };
                        @endphp
                        <tr class="cursor-pointer"
                            onclick="if (!event.target.closest('a,button,input,label,select,form')) window.location = this.dataset.href"
                            data-href="{{ route('admin.agent-advances.show', $advance) }}">
                            <td>
                                <a href="{{ route('admin.agent-advances.show', $advance) }}" class="erp-table-link">{{ $advance->agent?->name ?? '—' }}</a>
                                @if($advance->notes)
                                    <p class="erp-caption">{{ \Illuminate\Support\Str::limit($advance->notes, 72) }}</p>
                                @endif
                            </td>
                            <td class="erp-table-cell">{{ $advance->advanced_at->format('d M Y') }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($advance->amount, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($advance->applied_amount, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($advance->available_amount, 2) }}</span></td>
                            <td>
                                <p class="erp-table-cell">{{ $advance->payment_method ? ucfirst(str_replace('_', ' ', $advance->payment_method)) : '—' }}</p>
                                @if($advance->reference)
                                    <p class="erp-caption">{{ $advance->reference }}</p>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.agent-advances.show', $advance) }}" class="{{ $badge }}">{{ $statusLabels[$advance->status] ?? ucfirst($advance->status) }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state title="No agent advances" description="Record a prepayment when an agent pays before the invoice." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($advances->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
