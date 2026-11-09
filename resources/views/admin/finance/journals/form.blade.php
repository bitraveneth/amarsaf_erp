@extends('layouts.app')

@section('content')
@php
    $lineRows = old('lines', $journal->exists ? $journal->lines->map(fn ($line) => [
        'account_id' => $line->account_id,
        'debit' => $line->debit,
        'credit' => $line->credit,
        'description' => $line->description,
    ])->all() : [
        ['account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
        ['account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
    ]);
    $isEdit = $journal->exists;
@endphp
<div class="erp-page">
    <x-admin.page-header
        icon="journal"
        :title="$isEdit ? 'Edit journal' : 'New manual journal'"
        subtitle="Debits must equal credits before posting."
    >
        <x-slot:actions>
            <a href="{{ route('admin.journals.index') }}" class="erp-btn-secondary">Back to journals</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if($errors->any())
        <div class="rounded-2xl border border-error-200 bg-error-50 px-5 py-4 dark:border-error-500/30 dark:bg-error-500/10">
            <p class="erp-body-strong text-error-700 dark:text-error-300">{{ $errors->first() }}</p>
        </div>
    @endif

    <form
        method="POST"
        id="journal-form"
        action="{{ $isEdit ? route('admin.journals.update', $journal) : route('admin.journals.store') }}"
        class="erp-form-card"
    >
        @csrf
        @if($isEdit) @method('PATCH') @endif

        <x-admin.form.section title="Entry" description="Date and the reason for this voucher.">
            <div class="erp-form-grid">
                <x-admin.form.field label="Entry date" name="entry_date" :required="true">
                    <input id="entry_date" type="date" name="entry_date" value="{{ old('entry_date', optional($journal->entry_date)->toDateString() ?? now()->toDateString()) }}" class="erp-input" required>
                </x-admin.form.field>
                <x-admin.form.field label="Description" name="description" hint="Shown on the journal list and the posted voucher.">
                    <input id="description" type="text" name="description" value="{{ old('description', $journal->description) }}" class="erp-input" maxlength="255" placeholder="What this entry records">
                </x-admin.form.field>
            </div>
        </x-admin.form.section>

        <x-admin.form.section title="Lines" description="Each line is a debit or a credit, not both.">
            <div class="erp-table-wrap -mx-5 sm:-mx-6">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th class="is-right">#</th>
                            <th>Account</th>
                            <th class="is-right">Debit</th>
                            <th class="is-right">Credit</th>
                            <th>Line note</th>
                            <th class="is-right"><span class="sr-only">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody id="journal-lines">
                        @foreach($lineRows as $index => $line)
                            @include('admin.finance.journals.partials.line', ['index' => $index, 'line' => $line, 'accounts' => $accounts])
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <button type="button" class="erp-btn-secondary" id="journal-add-line">Add line</button>
            </div>

            <div class="erp-stat-grid">
                <div class="erp-metric-card">
                    <p class="erp-metric-label">Debit</p>
                    <p class="erp-metric-value tabular-nums" id="journal-total-debit">0.00</p>
                    <p class="erp-metric-hint">Sum of debit lines</p>
                </div>
                <div class="erp-metric-card">
                    <p class="erp-metric-label">Credit</p>
                    <p class="erp-metric-value tabular-nums" id="journal-total-credit">0.00</p>
                    <p class="erp-metric-hint">Sum of credit lines</p>
                </div>
                <div class="erp-metric-card" id="journal-balance-card">
                    <p class="erp-metric-label">Difference</p>
                    <p class="erp-metric-value tabular-nums" id="journal-total-diff">0.00</p>
                    <p class="erp-metric-hint" id="journal-balance-hint">Enter at least two lines.</p>
                </div>
            </div>
            <p id="journal-balance-badge" class="erp-badge-neutral">Waiting for amounts</p>
        </x-admin.form.section>

        <div class="erp-form-actions">
            <a href="{{ route('admin.journals.index') }}" class="erp-btn-secondary">Cancel</a>
            <button type="submit" class="erp-btn-secondary">Save draft</button>
            <button type="submit" name="post_now" value="1" class="erp-btn-primary">Save &amp; post</button>
        </div>
    </form>

    <x-admin.quick-guide
        title="How to enter a balanced journal"
        lead="A journal is a voucher that moves money between accounts. Debits must equal credits before you can post."
        :steps="[
            ['title' => 'One side per line', 'body' => 'Put an amount in Debit or Credit — not both.'],
            ['title' => 'At least two lines', 'body' => 'Money leaves one account and arrives in another.'],
            ['title' => 'Difference = 0.00', 'body' => 'The totals above the buttons show when the entry is balanced.'],
            ['title' => 'Draft or post', 'body' => 'Save draft to keep working. Save & post puts it on ledgers and reports.'],
        ]"
        :examples="[
            [
                'eyebrow' => 'Example 1',
                'title' => 'Pay office rent from bank',
                'caption' => 'Description: Office rent — Nov 2026',
                'headers' => ['Account', 'Debit', 'Credit'],
                'rows' => [
                    [['label' => 'Rent expense', 'hint' => 'Cost goes up'], '10,000.00', '—'],
                    [['label' => 'Bank – BRAC', 'hint' => 'Cash leaves the bank'], '—', '10,000.00'],
                ],
            ],
            [
                'eyebrow' => 'Example 2',
                'title' => 'Three lines still balance',
                'caption' => 'Utilities 8,000 + delivery 2,000 from bank 10,000',
                'headers' => ['Account', 'Debit', 'Credit'],
                'rows' => [
                    ['Utilities expense', '8,000.00', '—'],
                    ['Delivery expense', '2,000.00', '—'],
                    ['Bank – BRAC', '—', '10,000.00'],
                ],
            ],
        ]"
        note="Prefer Expenses, Invoices, Bills, or Payroll for day-to-day work — those create vouchers for you. Use a manual journal for openings, corrections, and adjustments with no other screen."
    />
</div>

<template id="journal-line-template">
    @include('admin.finance.journals.partials.line', [
        'index' => '__INDEX__',
        'line' => ['account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
        'accounts' => $accounts,
    ])
</template>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('journal-form');
        const body = document.getElementById('journal-lines');
        const template = document.getElementById('journal-line-template');
        const debitEl = document.getElementById('journal-total-debit');
        const creditEl = document.getElementById('journal-total-credit');
        const diffEl = document.getElementById('journal-total-diff');
        const hintEl = document.getElementById('journal-balance-hint');
        const badgeEl = document.getElementById('journal-balance-badge');

        const money = (amount) => amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        const rows = () => [...body.querySelectorAll('[data-journal-line]')];

        function reindex() {
            rows().forEach((row, index) => {
                row.querySelector('[data-journal-line-no]').textContent = String(index + 1);
                row.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(/lines\[[^\]]+\]/, 'lines[' + index + ']');
                });
            });
            rows().forEach((row) => {
                row.querySelector('[data-journal-remove]').hidden = rows().length <= 2;
            });
        }

        function amount(input) {
            const value = parseFloat(input.value);
            return Number.isFinite(value) && value > 0 ? value : 0;
        }

        function refresh() {
            let debit = 0;
            let credit = 0;
            rows().forEach((row) => {
                debit += amount(row.querySelector('[data-journal-debit-input]'));
                credit += amount(row.querySelector('[data-journal-credit-input]'));
            });
            debit = Math.round(debit * 100) / 100;
            credit = Math.round(credit * 100) / 100;
            const diff = Math.round(Math.abs(debit - credit) * 100) / 100;
            debitEl.textContent = money(debit);
            creditEl.textContent = money(credit);
            diffEl.textContent = money(diff);

            badgeEl.className = 'erp-badge-neutral';
            if (debit === 0 && credit === 0) {
                hintEl.textContent = 'Enter at least two lines.';
                badgeEl.textContent = 'Waiting for amounts';
                return;
            }
            if (diff < 0.01 && debit > 0) {
                hintEl.textContent = 'Ready to post.';
                badgeEl.className = 'erp-badge-success';
                badgeEl.textContent = 'Balanced';
                return;
            }
            hintEl.textContent = debit > credit ? 'Debits are higher.' : 'Credits are higher.';
            badgeEl.className = 'erp-badge-warning';
            badgeEl.textContent = 'Out of balance';
        }

        body.addEventListener('input', (event) => {
            const row = event.target.closest('[data-journal-line]');
            if (!row) {
                return;
            }
            const debitInput = row.querySelector('[data-journal-debit-input]');
            const creditInput = row.querySelector('[data-journal-credit-input]');
            if (event.target === debitInput && amount(debitInput) > 0) {
                creditInput.value = '';
            }
            if (event.target === creditInput && amount(creditInput) > 0) {
                debitInput.value = '';
            }
            refresh();
        });

        body.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-journal-remove]');
            if (!remove || rows().length <= 2) {
                return;
            }
            remove.closest('[data-journal-line]').remove();
            reindex();
            refresh();
        });

        document.getElementById('journal-add-line').addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__INDEX__', String(rows().length));
            body.insertAdjacentHTML('beforeend', html);
            reindex();
            refresh();
            const account = rows().at(-1).querySelector('[data-journal-account]');
            if (account) {
                account.focus();
            }
        });

        form.addEventListener('submit', () => {
            rows().forEach((row) => {
                const account = row.querySelector('[data-journal-account]').value;
                const debit = amount(row.querySelector('[data-journal-debit-input]'));
                const credit = amount(row.querySelector('[data-journal-credit-input]'));
                const note = row.querySelector('[name$="[description]"]').value.trim();
                if (!account && debit === 0 && credit === 0 && note === '') {
                    row.remove();
                }
            });
            reindex();
        });

        reindex();
        refresh();
    })();
</script>
@endpush
