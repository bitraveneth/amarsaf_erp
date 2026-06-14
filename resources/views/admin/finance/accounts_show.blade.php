@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $account->code }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $account->name }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $account->breadcrumb() }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.accounts.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Back to chart
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.accounts.edit', $account)" />
                <x-admin.action-delete
                    :action="route('admin.accounts.destroy', $account)"
                    :confirm="'Delete account ' . $account->code . ' — ' . $account->name . '? This cannot be undone.'"
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Kind</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $account->is_group ? 'Group' : 'Ledger' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</p>
            <p class="mt-1 text-sm font-semibold capitalize text-gray-900 dark:text-white">{{ $account->type }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $account->is_active ? 'Active' : 'Inactive' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Postable</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $account->isPostable() ? 'Yes' : 'No' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Account details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Slug</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $account->slug ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Report section</p>
                <p class="mt-1 text-sm capitalize text-gray-900 dark:text-white">{{ $account->report_root ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Parent</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    @if($account->parent)
                        <a href="{{ route('admin.accounts.show', $account->parent) }}" class="erp-link">{{ $account->parent->code }} — {{ $account->parent->name }}</a>
                    @else
                        Root level
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Level</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $account->level }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Path</p>
                <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ $account->path ?: '—' }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Ledger activity</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Journal lines</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($journalLineCount) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Legacy ledger lines</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($legacyLineCount) }}</p>
                </div>
            </div>
            @if(!$account->is_group)
                <p class="mt-4">
                    <a href="{{ route('admin.reports.general-ledger', ['account_id' => $account->id]) }}" class="erp-link">Open general ledger</a>
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Roll-up balance</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Debits</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($balance['debit'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Credits</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($balance['credit'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($balance['balance'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($account->is_group && $account->children->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Child accounts</h2>
            <div class="mt-4 erp-table-wrap">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Kind</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account->children as $child)
                            <tr>
                                <td class="font-mono">{{ $child->code }}</td>
                                <td>{{ $child->name }}</td>
                                <td>{{ $child->is_group ? 'Group' : 'Ledger' }}</td>
                                <td class="is-right">
                                    <x-admin.action-group class="justify-end">
                                        <x-admin.action-view :href="route('admin.accounts.show', $child)" />
                                        <x-admin.action-edit :href="route('admin.accounts.edit', $child)" />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
