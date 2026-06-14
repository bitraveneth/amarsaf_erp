@php
    use App\Helpers\Permission;
    $canRecordAdvance = Permission::can(auth()->user(), 'accounting.manage');
    $openCreditModal = $errors->has('credit_limit');
    $openAdvanceModal = $errors->has('amount') || $errors->has('advanced_at') || $errors->has('agent_id');
    $creditBarTone = match ($credit['status']) {
        'exhausted' => 'bg-error-500',
        'warning' => 'bg-warning-500',
        'none' => 'bg-gray-300 dark:bg-gray-600',
        default => 'bg-brand-500',
    };
@endphp

<div x-data="{
    modal: @js($openCreditModal ? 'credit' : ($openAdvanceModal ? 'advance' : null)),
    close() { this.modal = null; },
}" class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Commission &amp; credit</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            How {{ $agent->name }} earns commission and buys on credit from you.
        </p>
    </div>

    {{-- KPI row --}}
    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi
            label="Commission"
            :value="$commercial['commission_label']"
            hint="{{ $commercial['payment_label'] }}"
            tone="brand"
        />
        <x-dashboard.kpi
            label="Credit limit"
            :value="'BDT ' . number_format($credit['credit_limit'], 0)"
            :hint="$credit['status_label']"
            :tone="$credit['status'] === 'exhausted' ? 'danger' : ($credit['status'] === 'warning' ? 'warning' : 'default')"
        />
        <x-dashboard.kpi
            label="Outstanding"
            :value="'BDT ' . number_format($credit['outstanding'], 0)"
            :hint="'Available: BDT ' . number_format($credit['available_credit'], 0)"
            :tone="$credit['outstanding'] > 0 ? 'warning' : 'success'"
        />
        <x-dashboard.kpi
            label="Advance balance"
            :value="'BDT ' . number_format($credit['open_advance'], 0)"
            hint="Unapplied prepayments"
            tone="success"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Commission --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Commission</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">What this agent earns on sales.</p>
                </div>
                <a href="{{ route('admin.agents.pricing.edit', $agent) }}"
                   class="erp-btn-secondary !py-1.5 !px-3 !text-xs shrink-0">
                    Edit
                </a>
            </div>
            <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Rate</dt>
                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">{{ $commercial['commission_label'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Paid</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $commercial['payment_label'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Selling prices</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $commercial['prices_label'] }}</dd>
                </div>
            </dl>
        </section>

        {{-- Credit --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Credit</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">How much this agent can buy on account.</p>
                </div>
                <div class="flex shrink-0 flex-wrap justify-end gap-2">
                    <button type="button" @click="modal = 'credit'" class="erp-btn-secondary !py-1.5 !px-3 !text-xs">
                        Set limit
                    </button>
                    @if($canRecordAdvance)
                        <button type="button" @click="modal = 'advance'" class="erp-btn-primary !py-1.5 !px-3 !text-xs">
                            Give advance
                        </button>
                    @endif
                </div>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Credit used</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            BDT {{ number_format($credit['outstanding'], 0) }}
                            @if($credit['credit_limit'] > 0)
                                <span class="text-gray-400">/ {{ number_format($credit['credit_limit'], 0) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="{{ $creditBarTone }} h-full rounded-full transition-all" style="width: {{ $credit['credit_limit'] > 0 ? $credit['used_pct'] : 0 }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        @if($credit['credit_limit'] > 0)
                            {{ number_format($credit['used_pct'], 0) }}% used ·
                            BDT {{ number_format($credit['available_credit'], 0) }} still available
                        @else
                            Set a credit limit to track how much this agent can owe you.
                        @endif
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Credit limit</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">BDT {{ number_format($credit['credit_limit'], 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Advance on hand</dt>
                        <dd class="mt-1 text-lg font-semibold text-success-700 dark:text-success-400">BDT {{ number_format($credit['open_advance'], 0) }}</dd>
                    </div>
                </dl>

                @if($agent->withholding_rate)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Withholding tax: {{ number_format($agent->withholding_rate, 2) }}% on invoices.
                    </p>
                @endif
            </div>
        </section>
    </div>

    {{-- Recent advances --}}
    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent advances</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Prepayments from this agent — auto-applied to future invoices.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(Route::has('admin.agents.ledger.show'))
                    <a href="{{ route('admin.agents.ledger.show', $agent) }}" class="erp-btn-secondary !py-1.5 !px-3 !text-xs">View ledger</a>
                @endif
                @if(Route::has('admin.agent-advances.index'))
                    <a href="{{ route('admin.agent-advances.index', ['agent_id' => $agent->id]) }}" class="erp-btn-secondary !py-1.5 !px-3 !text-xs">All advances</a>
                @endif
            </div>
        </div>

        @if($credit['recent_advances']->isEmpty())
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No advances recorded yet.</p>
                @if($canRecordAdvance)
                    <button type="button" @click="modal = 'advance'" class="mt-3 erp-btn-primary !py-1.5 !px-3 !text-xs">
                        Record first advance
                    </button>
                @endif
            </div>
        @else
            <div class="erp-table-wrap">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Method</th>
                            <th class="is-right">Amount</th>
                            <th class="is-right">Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credit['recent_advances'] as $advance)
                            <tr>
                                <td>{{ $advance->advanced_at?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $advance->reference ?: ('ADV-' . $advance->id) }}</td>
                                <td>{{ $advance->payment_method ? str_replace('_', ' ', ucfirst($advance->payment_method)) : '—' }}</td>
                                <td class="is-right erp-table-num">BDT {{ number_format($advance->amount, 0) }}</td>
                                <td class="is-right erp-table-num">BDT {{ number_format($advance->available_amount, 0) }}</td>
                                <td>
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium',
                                        'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-300' => $advance->status === 'open',
                                        'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300' => $advance->status === 'partial',
                                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' => $advance->status === 'closed',
                                    ])>
                                        {{ ucfirst($advance->status ?? 'open') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Modals --}}
    <template x-teleport="body">
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/40 p-5"
             @keydown.escape.window="close()">
            {{-- Credit limit --}}
            <div x-show="modal === 'credit'" @click.outside="close()"
                 class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <form action="{{ route('admin.agents.credit.update', $agent) }}" method="POST" class="p-6">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Set credit limit</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maximum outstanding balance this agent can carry on account.</p>
                    <div class="mt-5">
                        <label for="modal_credit_limit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Credit limit (BDT)</label>
                        <div class="relative max-w-xs">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">BDT</span>
                            <input type="number" id="modal_credit_limit" name="credit_limit" step="0.01" min="0"
                                   value="{{ old('credit_limit', $agent->credit_limit) }}"
                                   placeholder="500000"
                                   class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-12 pr-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Currently outstanding: BDT {{ number_format($credit['outstanding'], 0) }}
                        </p>
                        @error('credit_limit')
                            <p class="mt-1 text-xs text-error-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="close()" class="erp-btn-secondary">Cancel</button>
                        <button type="submit" class="erp-btn-primary">Save</button>
                    </div>
                </form>
            </div>

            {{-- Advance --}}
            @if($canRecordAdvance)
            <div x-show="modal === 'advance'" @click.outside="close()"
                 class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <form action="{{ route('admin.agent-advances.store') }}" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                    <input type="hidden" name="redirect" value="commission">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Give advance</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Record a prepayment from {{ $agent->name }}. It will apply to their next invoices.</p>
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="modal_advance_amount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (BDT)</label>
                            <input type="number" id="modal_advance_amount" name="amount" step="0.01" min="0.01"
                                   value="{{ old('amount') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('amount')
                                <p class="mt-1 text-xs text-error-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="modal_advance_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <input type="date" id="modal_advance_date" name="advanced_at"
                                   value="{{ old('advanced_at', now()->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('advanced_at')
                                <p class="mt-1 text-xs text-error-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="modal_advance_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment method</label>
                            <select id="modal_advance_method" name="payment_method"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select method</option>
                                @foreach(['cash' => 'Cash', 'bkash' => 'bKash', 'bank_transfer' => 'Bank transfer', 'cheque' => 'Cheque'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal_advance_reference" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                            <input type="text" id="modal_advance_reference" name="reference" value="{{ old('reference') }}"
                                   placeholder="Receipt no., txn ID…"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="close()" class="erp-btn-secondary">Cancel</button>
                        <button type="submit" class="erp-btn-primary">Record advance</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </template>
</div>
