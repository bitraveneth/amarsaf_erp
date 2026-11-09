<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use App\Models\Expense;
use App\Models\FleetExpense;
use App\Models\PaymentInstrument;
use App\Models\PaymentMethodCategory;
use App\Services\Accounting\PaymentInstrumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('payment_method_categories') || ! Schema::hasTable('accounts')) {
            return;
        }

        /** @var PaymentInstrumentService $service */
        $service = app(PaymentInstrumentService::class);

        $cashGroup = $service->findGroupByCodeOrName('1111', 'Cash in Hand');
        $bankGroup = $service->findGroupByCodeOrName('1112', 'Cash at Bank');
        $mobileGroup = $service->ensureMobileWalletsGroup();

        $categories = [
            [
                'code' => 'bank',
                'name' => 'Bank',
                'kind' => PaymentMethodCategory::KIND_BANK,
                'parent_account_id' => $bankGroup?->id,
                'sort_order' => 10,
            ],
            [
                'code' => 'mobile',
                'name' => 'Mobile banking',
                'kind' => PaymentMethodCategory::KIND_MOBILE,
                'parent_account_id' => $mobileGroup->id,
                'sort_order' => 20,
            ],
            [
                'code' => 'cash',
                'name' => 'Cash',
                'kind' => PaymentMethodCategory::KIND_CASH,
                'parent_account_id' => $cashGroup?->id,
                'sort_order' => 30,
            ],
        ];

        foreach ($categories as $row) {
            PaymentMethodCategory::updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_active' => true]
            );
        }

        $bank = PaymentMethodCategory::where('code', 'bank')->first();
        $cash = PaymentMethodCategory::where('code', 'cash')->first();
        $mobile = PaymentMethodCategory::where('code', 'mobile')->first();

        $this->linkExisting($bank, [
            ['slug' => 'bank_brac', 'name' => 'BRAC Bank', 'is_default' => true],
            ['slug' => 'bank_scb', 'name' => 'Standard Chartered Bank', 'is_default' => false],
        ]);

        $this->linkExisting($cash, [
            ['slug' => 'cash_in_hand', 'name' => 'Cash in hand', 'is_default' => true],
            ['slug' => 'petty_cash', 'name' => 'Petty cash', 'is_default' => false],
        ]);

        // Optional starter mobile wallets if COA already has them.
        $this->linkExisting($mobile, [
            ['slug' => 'bkash_wallet', 'name' => 'bKash', 'is_default' => true, 'create_if_missing' => true],
            ['slug' => 'nagad_wallet', 'name' => 'Nagad', 'is_default' => false, 'create_if_missing' => true],
        ], $service);

        $this->backfillExpenses();
    }

    /**
     * @param  array<int, array{slug: string, name: string, is_default?: bool, create_if_missing?: bool}>  $rows
     */
    protected function linkExisting(?PaymentMethodCategory $category, array $rows, ?PaymentInstrumentService $service = null): void
    {
        if (! $category) {
            return;
        }

        foreach ($rows as $index => $row) {
            $account = Account::query()
                ->where('slug', $row['slug'])
                ->where('is_group', false)
                ->first();

            if (! $account && ! empty($row['create_if_missing']) && $service) {
                $service->create($category, [
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'is_default' => (bool) ($row['is_default'] ?? false),
                ]);

                continue;
            }

            if (! $account) {
                continue;
            }

            PaymentInstrument::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'payment_method_category_id' => $category->id,
                    'name' => $row['name'],
                    'account_id' => $account->id,
                    'is_default' => (bool) ($row['is_default'] ?? false),
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]
            );
        }
    }

    protected function backfillExpenses(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'payment_instrument_id')) {
            Expense::query()
                ->whereNull('payment_instrument_id')
                ->whereNotNull('payment_account_key')
                ->orderBy('id')
                ->chunkById(200, function ($chunk) {
                    foreach ($chunk as $expense) {
                        $key = $expense->payment_account_key === 'bank_default'
                            ? 'bank_brac'
                            : $expense->payment_account_key;
                        $instrument = PaymentInstrument::where('slug', $key)->first();
                        if ($instrument) {
                            $expense->forceFill(['payment_instrument_id' => $instrument->id])->saveQuietly();
                        }
                    }
                });
        }

        if (Schema::hasTable('fleet_expenses') && Schema::hasColumn('fleet_expenses', 'payment_instrument_id')) {
            FleetExpense::query()
                ->whereNull('payment_instrument_id')
                ->whereNotNull('payment_account_key')
                ->orderBy('id')
                ->chunkById(200, function ($chunk) {
                    foreach ($chunk as $expense) {
                        $key = $expense->payment_account_key === 'bank_default'
                            ? 'bank_brac'
                            : $expense->payment_account_key;
                        $instrument = PaymentInstrument::where('slug', $key)->first();
                        if ($instrument) {
                            $expense->forceFill(['payment_instrument_id' => $instrument->id])->saveQuietly();
                        }
                    }
                });
        }
    }
}
