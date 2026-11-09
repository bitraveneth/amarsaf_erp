@extends('layouts.app')

@section('content')
@php
    $kindMeta = [
        'bank' => [
            'label' => 'Bank',
            'tone' => 'brand',
            'hint' => 'Current and savings accounts',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5l9-6 9 6M4.5 10.5V19.5h15V10.5M8.25 19.5v-6h3v6m4.5 0v-6h3v6"/>',
        ],
        'mobile' => [
            'label' => 'Mobile',
            'tone' => 'success',
            'hint' => 'bKash, Nagad and wallets',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 1.5h3m-7.5 3h12a1.5 1.5 0 011.5 1.5v15a1.5 1.5 0 01-1.5 1.5h-12A1.5 1.5 0 016 21V6A1.5 1.5 0 017.5 4.5zM9 18h6"/>',
        ],
        'cash' => [
            'label' => 'Cash',
            'tone' => 'warning',
            'hint' => 'Cash in hand and petty cash',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 9h19.5M4.5 9V18a1.5 1.5 0 001.5 1.5h12A1.5 1.5 0 0019.5 18V9M12 12.75a2.25 2.25 0 110 4.5 2.25 2.25 0 010-4.5zM3.75 6.75h16.5"/>',
        ],
    ];
@endphp

<div
    class="erp-page pay-methods"
    x-data="{
        tab: @js($activeCode ?? ''),
        showAddCategory: false,
        editingCategory: null,
        addingAccount: false,
        editingInstrument: null,
        openAdd() { this.addingAccount = true; this.editingInstrument = null; this.editingCategory = null; },
        openEditInstrument(id) { this.editingInstrument = id; this.addingAccount = false; this.editingCategory = null; },
        openEditCategory(id) { this.editingCategory = id; this.addingAccount = false; this.editingInstrument = null; },
        closePanels() { this.addingAccount = false; this.editingInstrument = null; this.editingCategory = null; this.showAddCategory = false; }
    }"
>
    <x-admin.page-header
        title="Payment methods"
        subtitle="Set up Bank, Mobile, and Cash accounts. Each account creates a ledger staff can pay from."
    >
        <x-slot:actions>
            <a href="{{ route('admin.accounts.index') }}" class="erp-btn-secondary">Chart of accounts</a>
            <a href="{{ route('admin.expenses.index') }}" class="erp-btn-secondary">Expenses</a>
            <button type="button" class="erp-btn-primary" @click="showAddCategory = true; addingAccount = false; editingInstrument = null; editingCategory = null">
                Add category
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-100">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-admin.stat-card label="Categories" :value="(string) $stats['categories']" hint="Bank · Mobile · Cash" />
        <x-admin.stat-card label="Accounts" :value="(string) $stats['accounts']" hint="Payment instruments" tone="brand" />
        <x-admin.stat-card label="Active" :value="(string) $stats['active']" hint="Selectable on expenses" tone="success" />
        <x-admin.stat-card label="Linked ledgers" :value="(string) $stats['linked']" hint="Posted to chart of accounts" />
    </div>

    @if($categories->isEmpty())
        <div class="erp-table-card p-10 text-center">
            <p class="erp-h3">No payment categories yet</p>
            <p class="erp-caption mt-2">Start with Bank, Mobile banking, or Cash. Accounts you add under them create ledgers automatically.</p>
            <button type="button" class="erp-btn-primary mt-5" @click="showAddCategory = true">Add first category</button>
        </div>
    @else
        <div class="pay-methods__shell">
            <div class="pay-methods__tabs" role="tablist">
                @foreach($categories as $category)
                    @php $meta = $kindMeta[$category->kind] ?? $kindMeta['bank']; @endphp
                    <button
                        type="button"
                        role="tab"
                        class="pay-methods__tab"
                        :class="tab === @js($category->code) && 'is-active'"
                        @click="tab = @js($category->code); closePanels()"
                    >
                        <span class="pay-methods__tab-icon pay-methods__tab-icon--{{ $meta['tone'] }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $meta['icon'] !!}</svg>
                        </span>
                        <span class="pay-methods__tab-copy">
                            <span class="pay-methods__tab-name">{{ $category->name }}</span>
                            <span class="pay-methods__tab-meta">{{ $category->instruments->count() }} {{ Str::plural('account', $category->instruments->count()) }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            @foreach($categories as $category)
                @php $meta = $kindMeta[$category->kind] ?? $kindMeta['bank']; @endphp
                <div class="pay-methods__panel" x-show="tab === @js($category->code)" x-cloak>
                    <div class="pay-methods__panel-head">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="erp-h3">{{ $category->name }}</h2>
                                <span class="erp-po-status erp-po-status--{{ $meta['tone'] }}">{{ $meta['label'] }}</span>
                                @unless($category->is_active)
                                    <span class="erp-po-status erp-po-status--warning">Inactive</span>
                                @endunless
                            </div>
                            <p class="erp-caption mt-1">
                                New accounts post under
                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $category->parentAccount?->code }} · {{ $category->parentAccount?->name ?? 'No COA group set' }}
                                </span>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="erp-btn-secondary" @click="openEditCategory({{ $category->id }})">Edit category</button>
                            <button type="button" class="erp-btn-primary" @click="openAdd()">Add account</button>
                        </div>
                    </div>

                    <div class="pay-methods__list">
                        @forelse($category->instruments as $instrument)
                            <article class="pay-methods__card" :class="editingInstrument === {{ $instrument->id }} && 'is-editing'">
                                <div class="pay-methods__card-main">
                                    <div class="pay-methods__card-icon pay-methods__tab-icon--{{ $meta['tone'] }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $meta['icon'] !!}</svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $instrument->name }}</h3>
                                            @if($instrument->is_default)
                                                <span class="erp-badge-brand">Default</span>
                                            @endif
                                            @unless($instrument->is_active)
                                                <span class="erp-badge-warning">Inactive</span>
                                            @endunless
                                        </div>
                                        <p class="erp-caption mt-0.5 font-mono">{{ $instrument->slug }}</p>
                                        <div class="pay-methods__details">
                                            @forelse($instrument->detailLines() as $line)
                                                <span>{{ $line }}</span>
                                            @empty
                                                <span class="text-gray-400">No bank details yet — click Edit to add holder, A/C, branch, address</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div class="pay-methods__card-side">
                                        @if($instrument->account)
                                            <a href="{{ route('admin.accounts.show', $instrument->account) }}" class="pay-methods__ledger">
                                                <span class="pay-methods__ledger-code">{{ $instrument->account->code }}</span>
                                                <span class="pay-methods__ledger-name">{{ $instrument->account->name }}</span>
                                            </a>
                                        @else
                                            <span class="erp-caption">No ledger linked</span>
                                        @endif
                                        <button type="button" class="erp-btn-secondary" @click="openEditInstrument({{ $instrument->id }})">Edit</button>
                                    </div>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.payment-methods.instruments.update', $instrument) }}"
                                    class="pay-methods__editor"
                                    x-show="editingInstrument === {{ $instrument->id }}"
                                    x-cloak
                                >
                                    @csrf
                                    @method('PATCH')
                                    @include('admin.finance.payment_methods.partials.instrument-fields', [
                                        'instrument' => $instrument,
                                        'kind' => $category->kind,
                                    ])
                                    <div class="mt-3 flex flex-wrap items-center gap-4">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <input type="checkbox" name="is_default" value="1" @checked($instrument->is_default)>
                                            Default for {{ $category->name }}
                                        </label>
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <input type="checkbox" name="is_active" value="1" @checked($instrument->is_active)>
                                            Active
                                        </label>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="submit" class="erp-btn-primary">Save account</button>
                                        <button type="button" class="erp-btn-secondary" @click="editingInstrument = null">Cancel</button>
                                    </div>
                                </form>
                            </article>
                        @empty
                            <div class="pay-methods__empty">
                                <p class="erp-body-strong">No accounts in {{ $category->name }} yet</p>
                                <p class="erp-caption mt-1">Add BRAC, SCB, bKash, or cash drawers — each one creates a ledger automatically.</p>
                                <button type="button" class="erp-btn-primary mt-4" @click="openAdd()">Add first account</button>
                            </div>
                        @endforelse
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.payment-methods.instruments.store', $category) }}"
                        class="pay-methods__composer"
                        x-show="addingAccount"
                        x-cloak
                    >
                        @csrf
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="erp-body-strong">Add account under {{ $category->name }}</p>
                                <p class="erp-caption mt-1">Creates a postable ledger under {{ $category->parentAccount?->name ?? 'the category group' }}.</p>
                            </div>
                            <button type="button" class="erp-btn-secondary" @click="addingAccount = false">Close</button>
                        </div>
                        <div class="mt-4">
                            @include('admin.finance.payment_methods.partials.instrument-fields', [
                                'instrument' => null,
                                'kind' => $category->kind,
                            ])
                        </div>
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_default" value="1">
                            Make default for {{ $category->name }}
                        </label>
                        <div class="mt-4">
                            <button type="submit" class="erp-btn-primary">Add account + create ledger</button>
                        </div>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('admin.payment-methods.categories.update', $category) }}"
                        class="pay-methods__composer"
                        x-show="editingCategory === {{ $category->id }}"
                        x-cloak
                    >
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="erp-body-strong">Edit {{ $category->name }}</p>
                                <p class="erp-caption mt-1">Change display name or which COA group new accounts join.</p>
                            </div>
                            <button type="button" class="erp-btn-secondary" @click="editingCategory = null">Close</button>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="erp-field">
                                <label class="erp-label">Name</label>
                                <input name="name" value="{{ $category->name }}" class="erp-input" required>
                            </div>
                            <div class="erp-field">
                                <label class="erp-label">COA parent group</label>
                                <select name="parent_account_id" class="erp-input" required>
                                    @foreach($assetGroups as $group)
                                        <option value="{{ $group->id }}" @selected($category->parent_account_id === $group->id)>
                                            {{ $group->code }} · {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                            Category active
                        </label>
                        <div class="mt-4">
                            <button type="submit" class="erp-btn-primary">Save category</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <div
        class="pay-methods__drawer-backdrop"
        x-show="showAddCategory"
        x-cloak
        @click.self="showAddCategory = false"
    >
        <div class="pay-methods__drawer" @click.stop>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="erp-h3">Add category</p>
                    <p class="erp-caption mt-1">Group for Bank, Mobile, Cash, or your own payment type.</p>
                </div>
                <button type="button" class="erp-btn-secondary" @click="showAddCategory = false">Close</button>
            </div>
            <form method="POST" action="{{ route('admin.payment-methods.categories.store') }}" class="mt-5 space-y-4">
                @csrf
                <div class="erp-field">
                    <label class="erp-label">Name</label>
                    <input name="name" class="erp-input" required placeholder="Mobile banking">
                </div>
                <div class="erp-field">
                    <label class="erp-label">Code</label>
                    <input name="code" class="erp-input" required placeholder="mobile">
                    <p class="erp-caption mt-1">Short key used internally, e.g. bank, mobile, cash</p>
                </div>
                <div class="erp-field">
                    <label class="erp-label">Kind</label>
                    <select name="kind" class="erp-select" required>
                        <option value="bank">Bank</option>
                        <option value="mobile">Mobile banking</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div class="erp-field">
                    <label class="erp-label">COA parent group</label>
                    <select name="parent_account_id" class="erp-select" required>
                        @foreach($assetGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->code }} · {{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-primary w-full">Create category</button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .pay-methods__shell {
        overflow: hidden;
        border: 1px solid rgb(229 231 235);
        border-radius: 1.25rem;
        background: rgb(255 255 255);
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
    }
    .dark .pay-methods__shell {
        border-color: rgb(31 41 55);
        background: rgb(17 24 39);
    }
    .pay-methods__tabs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
        gap: 0.5rem;
        padding: 0.75rem;
        border-bottom: 1px solid rgb(243 244 246);
        background: rgb(249 250 251 / 0.85);
    }
    .dark .pay-methods__tabs {
        border-bottom-color: rgb(31 41 55);
        background: rgb(3 7 18 / 0.45);
    }
    .pay-methods__tab {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid transparent;
        border-radius: 1rem;
        padding: 0.85rem 1rem;
        text-align: left;
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
    }
    .pay-methods__tab:hover {
        background: rgb(255 255 255);
        border-color: rgb(229 231 235);
    }
    .dark .pay-methods__tab:hover {
        background: rgb(17 24 39);
        border-color: rgb(55 65 81);
    }
    .pay-methods__tab.is-active {
        background: rgb(255 255 255);
        border-color: rgb(199 210 254);
        box-shadow: 0 8px 24px rgb(79 70 229 / 0.08);
    }
    .dark .pay-methods__tab.is-active {
        background: rgb(17 24 39);
        border-color: rgb(99 102 241 / 0.45);
        box-shadow: none;
    }
    .pay-methods__tab-icon,
    .pay-methods__card-icon {
        display: inline-flex;
        height: 2.25rem;
        width: 2.25rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 0.85rem;
    }
    .pay-methods__tab-icon--brand,
    .pay-methods__card-icon.pay-methods__tab-icon--brand {
        background: rgb(238 242 255);
        color: rgb(79 70 229);
    }
    .dark .pay-methods__tab-icon--brand,
    .dark .pay-methods__card-icon.pay-methods__tab-icon--brand {
        background: rgb(49 46 129 / 0.35);
        color: rgb(165 180 252);
    }
    .pay-methods__tab-icon--success,
    .pay-methods__card-icon.pay-methods__tab-icon--success {
        background: rgb(236 253 245);
        color: rgb(5 150 105);
    }
    .dark .pay-methods__tab-icon--success,
    .dark .pay-methods__card-icon.pay-methods__tab-icon--success {
        background: rgb(6 78 59 / 0.35);
        color: rgb(110 231 183);
    }
    .pay-methods__tab-icon--warning,
    .pay-methods__card-icon.pay-methods__tab-icon--warning {
        background: rgb(255 247 237);
        color: rgb(234 88 12);
    }
    .dark .pay-methods__tab-icon--warning,
    .dark .pay-methods__card-icon.pay-methods__tab-icon--warning {
        background: rgb(124 45 18 / 0.35);
        color: rgb(253 186 116);
    }
    .pay-methods__tab-copy {
        display: flex;
        min-width: 0;
        flex-direction: column;
    }
    .pay-methods__tab-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: rgb(17 24 39);
    }
    .dark .pay-methods__tab-name { color: rgb(243 244 246); }
    .pay-methods__tab-meta {
        font-size: 0.75rem;
        color: rgb(107 114 128);
    }
    .pay-methods__panel { padding: 1.25rem; }
    .pay-methods__panel-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .pay-methods__list {
        display: grid;
        gap: 0.85rem;
    }
    .pay-methods__card {
        border: 1px solid rgb(229 231 235);
        border-radius: 1.1rem;
        background: rgb(249 250 251 / 0.55);
        padding: 1rem 1.1rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dark .pay-methods__card {
        border-color: rgb(55 65 81);
        background: rgb(3 7 18 / 0.35);
    }
    .pay-methods__card.is-editing,
    .pay-methods__card:hover {
        border-color: rgb(199 210 254);
        background: rgb(255 255 255);
        box-shadow: 0 10px 28px rgb(15 23 42 / 0.05);
    }
    .dark .pay-methods__card.is-editing,
    .dark .pay-methods__card:hover {
        border-color: rgb(99 102 241 / 0.45);
        background: rgb(17 24 39);
        box-shadow: none;
    }
    .pay-methods__card-main {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 1rem;
    }
    .pay-methods__details {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.85rem;
        margin-top: 0.55rem;
        font-size: 0.8rem;
        color: rgb(75 85 99);
    }
    .dark .pay-methods__details { color: rgb(156 163 175); }
    .pay-methods__card-side {
        display: flex;
        min-width: 12rem;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.65rem;
        margin-left: auto;
    }
    .pay-methods__ledger {
        display: flex;
        max-width: 16rem;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
        text-decoration: none;
    }
    .pay-methods__ledger:hover .pay-methods__ledger-name {
        color: rgb(79 70 229);
    }
    .pay-methods__ledger-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.75rem;
        color: rgb(107 114 128);
    }
    .pay-methods__ledger-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: rgb(31 41 55);
    }
    .dark .pay-methods__ledger-name { color: rgb(229 231 235); }
    .pay-methods__editor,
    .pay-methods__composer {
        margin-top: 1rem;
        border-top: 1px solid rgb(229 231 235);
        padding-top: 1rem;
    }
    .dark .pay-methods__editor,
    .dark .pay-methods__composer {
        border-top-color: rgb(55 65 81);
    }
    .pay-methods__composer {
        margin-top: 1.25rem;
        border: 1px solid rgb(229 231 235);
        border-radius: 1.1rem;
        background: rgb(249 250 251 / 0.7);
        padding: 1.1rem 1.2rem;
    }
    .dark .pay-methods__composer {
        border-color: rgb(55 65 81);
        background: rgb(3 7 18 / 0.45);
    }
    .pay-methods__empty {
        border: 1px dashed rgb(209 213 219);
        border-radius: 1.1rem;
        padding: 2.5rem 1.5rem;
        text-align: center;
    }
    .dark .pay-methods__empty { border-color: rgb(75 85 99); }
    .pay-methods__drawer-backdrop {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: rgb(15 23 42 / 0.45);
        padding: 1rem;
        backdrop-filter: blur(4px);
    }
    @media (min-width: 768px) {
        .pay-methods__drawer-backdrop {
            align-items: center;
        }
    }
    .pay-methods__drawer {
        width: min(28rem, 100%);
        border-radius: 1.25rem;
        border: 1px solid rgb(229 231 235);
        background: rgb(255 255 255);
        padding: 1.25rem;
        box-shadow: 0 24px 60px rgb(15 23 42 / 0.2);
    }
    .dark .pay-methods__drawer {
        border-color: rgb(55 65 81);
        background: rgb(17 24 39);
    }
    [x-cloak] { display: none !important; }
</style>
@endpush
@endsection
