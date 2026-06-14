@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header
        title="Agents"
        subtitle="Credit limit, commission rate, and unapplied advance per agent."
    >
        <x-slot:actions>
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.agents.index') }}" class="flex gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search agents..."
                        aria-label="Search agents"
                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <button type="submit" 
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Search
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.agents.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Clear
                    </a>
                @endif
            </form>
            <a href="{{ route('admin.agents.create') }}" 
               data-tour="agents-primary-action"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Agent
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if($agents->isNotEmpty())
        <div class="erp-table-card">
            <div class="erp-table-wrap">
                <table class="erp-table erp-table--agents w-full min-w-[68rem]">
                    <thead>
                        <tr>
                            <th class="w-[22%]">Agent</th>
                            <th class="w-[14%]">Zone</th>
                            <th class="is-right w-[10%]">Advance</th>
                            <th class="is-right w-[11%]">Credit limit</th>
                            <th class="w-[9%]">Status</th>
                            <th class="w-[10%]">Commission</th>
                            <th class="is-right w-[24%]">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                            <tr>
                                <td>
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                                            <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                {{ substr($agent->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="erp-body-strong truncate" title="{{ $agent->name }}">
                                                {{ $agent->name }}
                                            </p>
                                            @if($agent->code)
                                                <p class="erp-caption truncate" title="{{ $agent->code }}">
                                                    {{ $agent->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($agent->area || $agent->zone)
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $agent->area }}">{{ $agent->area ?? '—' }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $agent->zone }}">{{ $agent->zone ?? '—' }}</p>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="is-right whitespace-nowrap">
                                    @php $advanceBalance = (float) ($agent->open_advance_balance ?? 0); @endphp
                                    @if($advanceBalance > 0)
                                        <a href="{{ route('admin.agents.ledger.show', $agent) }}"
                                           class="erp-table-num text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                                           title="View ledger">
                                            BDT {{ number_format($advanceBalance, 0) }}
                                        </a>
                                    @else
                                        <span class="erp-table-num text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="is-right whitespace-nowrap">
                                    <span class="erp-table-num">
                                        BDT {{ number_format($agent->credit_limit ?? 0, 0) }}
                                    </span>
                                </td>
                                <td>
                                    @if($agent->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Suspended
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php $rules = $agent->commissions ?? collect(); @endphp
                                    @if($rules->isEmpty())
                                        <a href="{{ route('admin.agents.show', ['agent' => $agent, 'tab' => 'commission']) }}"
                                           class="text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                            Not set
                                        </a>
                                    @else
                                        @php $primaryRule = $rules->first(); @endphp
                                        <a href="{{ route('admin.agents.show', ['agent' => $agent, 'tab' => 'commission']) }}"
                                           class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-medium text-brand-700 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300">
                                            @if($primaryRule->type === 'percentage')
                                                {{ rtrim(rtrim(number_format($primaryRule->value, 2), '0'), '.') }}%
                                            @else
                                                BDT {{ number_format($primaryRule->value, 0) }}
                                            @endif
                                            @if($rules->count() > 1)
                                                <span class="opacity-70">+{{ $rules->count() - 1 }}</span>
                                            @endif
                                        </a>
                                    @endif
                                </td>
                                <td class="is-right whitespace-nowrap py-3">
                                    <x-admin.action-group class="flex-nowrap justify-end">
                                        <x-admin.action-view :href="route('admin.agents.show', $agent)" />
                                        <a href="{{ route('admin.agents.show', ['agent' => $agent, 'tab' => 'commission']) }}"
                                           class="erp-btn-primary !px-2.5 !py-1.5 !text-xs"
                                           title="Commission &amp; credit for this agent">
                                            Commission
                                        </a>
                                        <x-admin.action-edit :href="route('admin.agents.edit', $agent)" />
                                        <x-admin.action-delete
                                            :action="route('admin.agents.destroy', $agent)"
                                            confirm="Delete this agent? This action cannot be undone."
                                        />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $agents->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No agents found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                @if(request()->filled('q'))
                    No agents match your search criteria. Try a different search term.
                @else
                    No agents registered yet. Add your first agent to start building your network.
                @endif
            </p>
            <a href="{{ route('admin.agents.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Agent
            </a>
        </div>
    @endif
</div>
@endsection
