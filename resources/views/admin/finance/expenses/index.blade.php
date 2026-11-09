@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
@endphp
<div class="erp-page">
    <x-admin.page-header
        title="Expenses"
        subtitle="Recorded costs for the selected period."
    >
        <x-slot:actions>
            <x-dashboard.period-filter
                variant="compact"
                :action="route('admin.expenses.index')"
                :range="$range"
                :from="$from->toDateString()"
                :to="$to->toDateString()"
                :range-options="$rangeOptions"
                :period-label="$periodLabel"
            />
            <form method="GET" action="{{ route('admin.expenses.index') }}">
                <input type="hidden" name="range" value="{{ $range }}">
                @if($range === 'custom')
                    <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                    <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                @endif
                <select name="category" class="erp-select erp-select--sm" aria-label="Category" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->code }}" @selected(request('category') === $category->code)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </form>
            <form method="POST" action="{{ route('admin.expenses.sync-ledger') }}">
                @csrf
                <input type="hidden" name="scope" value="missing">
                <input type="hidden" name="redirect" value="{{ route('admin.expenses.index') }}">
                <button type="submit" class="erp-btn-secondary">Sync missing expenses</button>
            </form>
            <a href="{{ route('admin.expense-categories.index') }}" class="erp-btn-secondary">Category mapping</a>
            <a href="{{ route('admin.expenses.create') }}" class="erp-btn-primary">Add expense</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Expenses"
        :cards="[
            [
                'label' => 'Expenses',
                'numeric' => number_format($expenses->total()),
                'caption' => $periodLabel,
                'href' => route('admin.expenses.index', request()->query()),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Total',
                'numeric' => number_format($total, 2),
                'currency' => $currency,
                'caption' => $rangeOptions[$range] ?? 'Period',
                'href' => route('admin.expenses.index', request()->query()),
                'tone' => 'orange',
                'valueTone' => 'warning',
                'icon' => 'receivables',
            ],
        ]"
    />

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="is-right">Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr class="cursor-pointer" @click="if (!$event.target.closest('a,button,input,form')) window.location = @js(route('admin.expenses.edit', $expense))">
                            <td class="erp-table-cell">{{ $expense->date?->format('d M Y') }}</td>
                            <td>
                                <span class="erp-badge-neutral">{{ $expense->expenseCategory?->name ?: ucfirst($expense->category) }}</span>
                            </td>
                            <td class="erp-table-cell">{{ $expense->description ?: '—' }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($expense->amount, 2) }}</span></td>
                            <td class="erp-table-cell">{{ $expense->reference ?: '—' }}</td>
                            <td>
                                @if(($expense->status ?? 'recorded') === 'reviewed')
                                    <span class="erp-badge-success">Reviewed</span>
                                @else
                                    <span class="erp-badge-warning">Recorded</span>
                                @endif
                            </td>
                            <td class="is-right" @click.stop>
                                <x-admin.action-group class="justify-end">
                                    <x-admin.action-edit :href="route('admin.expenses.edit', $expense)" />
                                    <x-admin.action-delete
                                        :action="route('admin.expenses.destroy', $expense)"
                                        confirm="Delete this expense? This cannot be undone."
                                    />
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state title="No expenses" description="Nothing in this period. Change the dates or add an expense.">
                                    <x-slot:action>
                                        <a href="{{ route('admin.expenses.create') }}" class="erp-btn-primary">Add expense</a>
                                    </x-slot:action>
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <x-admin.quick-guide
        title="How to use expenses"
        lead="This list is the operational expense register. New rows should also appear in the general ledger and P&L."
        :steps="[
            ['title' => 'Set the period', 'body' => 'Use the date filter in the header to match the month you are reviewing.'],
            ['title' => 'Add expense', 'body' => 'Opens the form that posts category + payment to the ledger automatically.'],
            ['title' => 'Sync missing expenses', 'body' => 'If the banner says some rows have no voucher, sync posts them in one click.'],
            ['title' => 'Category mapping', 'body' => 'Map each category to the right expense account before staff start recording.'],
        ]"
        :examples="[
            [
                'eyebrow' => 'Tip',
                'title' => 'When P&L looks too low',
                'bullets' => [
                    'Check this list for the same period',
                    'Look for the ledger-gap banner at the top of the app',
                    'Click Sync missing expenses, then refresh Profit & loss',
                ],
            ],
        ]"
        note="Demo data may create expenses without journals. Real saves from Add expense post immediately."
        :open="false"
    />
</div>
@endsection
