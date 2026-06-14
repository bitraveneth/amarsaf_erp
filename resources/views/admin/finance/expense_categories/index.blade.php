@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Expense category mapping</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Each category links to a ledger in the chart of accounts. Expenses use this mapping when posting journals.
        </p>
    </div>

    @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Category</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Expense ledger</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">If unpaid (payable)</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Default payment</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($categories as $category)
                    <tr>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $category->name }}
                            <p class="text-xs text-gray-500">{{ $category->code }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ $category->account?->code }} · {{ $category->account?->name }}
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                            {{ $category->payableAccount?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-sm capitalize text-gray-700 dark:text-gray-300">
                            {{ $category->default_payment_type }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <details class="inline-block text-left">
                                <summary class="cursor-pointer text-sm font-semibold text-brand-600">Edit</summary>
                                <form method="POST" action="{{ route('admin.expense-categories.update', $category) }}" class="mt-3 w-80 space-y-3 rounded-lg border border-gray-200 bg-white p-4 text-left shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                    @csrf
                                    @method('PATCH')
                                    <div class="erp-field">
                                        <label class="erp-label">Name</label>
                                        <input name="name" value="{{ $category->name }}" class="erp-input" required>
                                    </div>
                                    <div class="erp-field">
                                        <label class="erp-label">Expense ledger</label>
                                        <select name="account_id" class="erp-input" required>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}" @selected($category->account_id === $account->id)>
                                                    {{ $account->code }} · {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="erp-field">
                                        <label class="erp-label">Payable ledger (optional)</label>
                                        <select name="payable_account_id" class="erp-input">
                                            <option value="">—</option>
                                            @foreach($payableAccounts as $account)
                                                <option value="{{ $account->id }}" @selected($category->payable_account_id === $account->id)>
                                                    {{ $account->code }} · {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="erp-field">
                                        <label class="erp-label">Default payment</label>
                                        <select name="default_payment_type" class="erp-input">
                                            @foreach(['bank', 'cash', 'payable'] as $type)
                                                <option value="{{ $type }}" @selected($category->default_payment_type === $type)>{{ ucfirst($type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="erp-field">
                                        <label class="erp-label">Default bank/cash key</label>
                                        <input name="default_payment_account_key" value="{{ $category->default_payment_account_key }}" class="erp-input">
                                    </div>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                                        Active
                                    </label>
                                    <button type="submit" class="erp-btn-primary w-full">Save</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
