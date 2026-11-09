@extends('layouts.app')

@section('content')
@php
    $agentOptions = $agents->map(fn ($agent) => [
        'id' => $agent->id,
        'name' => $agent->name,
        'meta' => collect([$agent->phone, $agent->area ?: $agent->zone])->filter()->implode(' · '),
    ])->values();
    $currency = config('app.currency', 'BDT');
@endphp
<div class="erp-page">
    <x-admin.page-header
        title="Record agent advance"
        subtitle="A prepayment that applies to this agent's next invoices."
    >
        <x-slot:actions>
            <a href="{{ route('admin.agent-advances.index') }}" class="erp-btn-secondary">Back to advances</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.agent-advances.store') }}" method="POST" class="erp-form-card max-w-3xl">
        @csrf

        <x-admin.form.section title="Agent" description="Search by name, phone, or area.">
            <div
                class="relative max-w-xl"
                x-data="{
                    open: false,
                    query: '',
                    selectedId: @js((string) old('agent_id', $selectedAgent ?? '')),
                    agents: @js($agentOptions),
                    get selected() {
                        return this.agents.find((agent) => String(agent.id) === String(this.selectedId)) || null;
                    },
                    get filtered() {
                        const term = this.query.trim().toLowerCase();
                        if (!term) {
                            return this.agents;
                        }
                        return this.agents.filter((agent) => (agent.name + ' ' + agent.meta).toLowerCase().includes(term));
                    },
                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            this.$nextTick(() => this.$refs.agentSearch.focus());
                        }
                    },
                    choose(agent) {
                        this.selectedId = String(agent.id);
                        this.query = '';
                        this.open = false;
                    },
                }"
                @keydown.escape.window="open = false"
            >
                <input type="hidden" name="agent_id" :value="selectedId">
                <button type="button" class="erp-input flex items-center justify-between gap-3 text-left" @click="toggle()" :aria-expanded="open.toString()">
                    <span class="min-w-0 truncate" :class="selected ? 'text-gray-900 dark:text-white' : 'text-gray-400'" x-text="selected ? selected.name : 'Search agent'"></span>
                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    class="absolute z-30 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-md dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="border-b border-gray-100 p-2 dark:border-gray-800">
                        <input x-ref="agentSearch" x-model="query" type="search" class="erp-input" placeholder="Name, phone, or area" aria-label="Search agents">
                    </div>
                    <ul class="max-h-64 overflow-y-auto py-1">
                        <template x-for="agent in filtered" :key="agent.id">
                            <li>
                                <button type="button" class="flex w-full flex-col px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800" @click="choose(agent)">
                                    <span class="erp-body-strong text-gray-900 dark:text-white" x-text="agent.name"></span>
                                    <span class="erp-caption" x-show="agent.meta" x-text="agent.meta"></span>
                                </button>
                            </li>
                        </template>
                        <li x-show="filtered.length === 0" class="erp-caption px-3 py-4">No agents match.</li>
                    </ul>
                </div>
                @error('agent_id')
                    <p class="erp-form-error">{{ $message }}</p>
                @enderror
            </div>
        </x-admin.form.section>

        <x-admin.form.section title="Payment" description="Amount received from the agent.">
            <div class="erp-form-grid">
                <x-admin.form.field label="Amount" name="amount" :required="true">
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">{{ $currency }}</span>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="erp-input pl-12" required>
                    </div>
                </x-admin.form.field>

                <x-admin.form.field label="Advance date" name="advanced_at" :required="true">
                    <input id="advanced_at" name="advanced_at" type="date" value="{{ old('advanced_at', now()->format('Y-m-d')) }}" class="erp-input" required>
                </x-admin.form.field>

                <x-admin.form.field label="Payment method" name="payment_method">
                    <select id="payment_method" name="payment_method" class="erp-select">
                        <option value="">Select method</option>
                        @foreach(['cash' => 'Cash', 'bkash' => 'bKash', 'bank_transfer' => 'Bank transfer', 'cheque' => 'Cheque'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.form.field>

                <x-admin.form.field label="Reference" name="reference" hint="Receipt, cheque, or transfer number.">
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="erp-input" maxlength="255">
                </x-admin.form.field>

                <div class="sm:col-span-2">
                    <x-admin.form.field label="Notes" name="notes">
                        <textarea id="notes" name="notes" rows="3" class="erp-textarea">{{ old('notes') }}</textarea>
                    </x-admin.form.field>
                </div>
            </div>
        </x-admin.form.section>

        <div class="erp-form-actions">
            <a href="{{ route('admin.agent-advances.index') }}" class="erp-btn-secondary">Cancel</a>
            <button type="submit" class="erp-btn-primary">Record advance</button>
        </div>
    </form>
</div>
@endsection
