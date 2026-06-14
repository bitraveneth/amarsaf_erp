<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /** @var array<string, array<string, string>> */
    protected array $definitions = [
        'general' => [
            'name' => 'General',
            'account' => 'selling_distribution',
            'payable' => null,
        ],
        'marketing' => [
            'name' => 'Marketing',
            'account' => 'marketing_expense',
            'payable' => null,
        ],
        'utilities' => [
            'name' => 'Utilities',
            'account' => 'utilities_expense',
            'payable' => 'utility_payable',
        ],
        'salary' => [
            'name' => 'Salary',
            'account' => 'salaries_wages',
            'payable' => 'salary_payable',
        ],
        'rent' => [
            'name' => 'Rent',
            'account' => 'office_rent',
            'payable' => 'rent_payable',
        ],
        'travel' => [
            'name' => 'Travel & delivery',
            'account' => 'delivery_expense',
            'payable' => null,
        ],
        'other' => [
            'name' => 'Other',
            'account' => 'selling_distribution',
            'payable' => null,
        ],
    ];

    public function run(): void
    {
        if (! Account::query()->where('slug', 'selling_distribution')->exists()) {
            $this->command?->warn('Chart of accounts not seeded — skipping expense categories.');

            return;
        }

        $sort = 10;

        foreach ($this->definitions as $code => $definition) {
            ExpenseCategory::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $definition['name'],
                    'account_id' => $this->accountId($definition['account']),
                    'payable_account_id' => $definition['payable']
                        ? $this->accountId($definition['payable'])
                        : null,
                    'default_payment_type' => ExpenseCategory::PAYMENT_BANK,
                    'default_payment_account_key' => 'bank_default',
                    'sort_order' => $sort,
                    'is_active' => true,
                ]
            );

            $sort += 10;
        }

        $this->backfillExpenses();
    }

    protected function backfillExpenses(): void
    {
        $categories = ExpenseCategory::query()->get()->keyBy('code');

        Expense::query()->whereNull('expense_category_id')->each(function (Expense $expense) use ($categories) {
            $code = strtolower(trim((string) $expense->category));
            $category = $categories->get($code) ?? $categories->get('general');

            if (! $category) {
                return;
            }

            $expense->update([
                'expense_category_id' => $category->id,
                'payment_type' => $expense->payment_type ?: ExpenseCategory::PAYMENT_BANK,
                'payment_account_key' => $expense->payment_account_key ?: 'bank_default',
            ]);
        });
    }

    protected function accountId(string $slug): int
    {
        return (int) Account::query()->where('slug', $slug)->value('id');
    }
}
