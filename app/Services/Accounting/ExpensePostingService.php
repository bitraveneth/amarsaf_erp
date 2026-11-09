<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Carbon;

class ExpensePostingService
{
    public function __construct(
        protected AccountingService $accounting,
        protected AccountResolver $accounts
    ) {
    }

    public function sync(Expense $expense): void
    {
        $this->accounting->deleteByJournalTypeAndSource('expense', Expense::class, $expense->id);

        if ((float) $expense->amount <= 0 || ! in_array($expense->status, [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED], true)) {
            return;
        }

        $expense->loadMissing([
            'expenseCategory.account',
            'expenseCategory.payableAccount',
            'accountOverride',
            'paymentInstrument.account',
            'paymentInstrument.category',
        ]);

        $expenseAccountKey = $this->expenseAccountKey($expense);
        $creditKey = $this->creditAccountKey($expense);
        $analytic = $expense->analytic_label;

        $this->accounting->post(
            'expense',
            Carbon::parse($expense->date),
            [
                [
                    'account_key' => $expenseAccountKey,
                    'debit' => $expense->amount,
                    'credit' => 0,
                    'description' => $expense->description,
                    'analytic_label' => $analytic,
                ],
                [
                    'account_key' => $creditKey,
                    'debit' => 0,
                    'credit' => $expense->amount,
                    'description' => $expense->description,
                    'analytic_label' => $analytic,
                ],
            ],
            [
                'description' => 'Expense #' . $expense->id,
                'source_type' => Expense::class,
                'source_id' => $expense->id,
            ]
        );
    }

    public function expenseAccountKey(Expense $expense): string
    {
        if ($expense->account_id && $expense->accountOverride?->isPostable()) {
            return $expense->accountOverride->slug;
        }

        $category = $expense->expenseCategory;
        if ($category?->account?->slug) {
            return $category->account->slug;
        }

        return $this->legacyCategoryKey($expense->category);
    }

    public function creditAccountKey(Expense $expense): string
    {
        $paymentType = $expense->payment_type ?: ExpenseCategory::PAYMENT_BANK;

        if ($paymentType === ExpenseCategory::PAYMENT_PAYABLE) {
            $payable = $expense->expenseCategory?->payableAccount;
            if ($payable?->slug) {
                return $payable->slug;
            }

            return 'trade_creditors';
        }

        $instrument = $expense->paymentInstrument;
        if ($instrument?->ledgerSlug()) {
            return $instrument->ledgerSlug();
        }

        $key = $expense->payment_account_key;
        if ($key === 'bank_default') {
            $key = 'bank_brac';
        }

        if ($paymentType === ExpenseCategory::PAYMENT_CASH) {
            return $key ?: 'cash_in_hand';
        }

        return $key ?: 'bank_brac';
    }

    protected function legacyCategoryKey(?string $category): string
    {
        $category = strtolower(trim((string) $category));

        return match (true) {
            str_contains($category, 'marketing') => 'marketing_expense',
            str_contains($category, 'salary'), str_contains($category, 'payroll') => 'salaries_wages',
            str_contains($category, 'utility') => 'utilities_expense',
            str_contains($category, 'rent') => 'office_rent',
            str_contains($category, 'delivery'), str_contains($category, 'transport'), str_contains($category, 'travel') => 'delivery_expense',
            default => 'selling_distribution',
        };
    }
}
