<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Services\Accounting\AccountPathService;
use App\Services\Accounting\AccountResolver;
use Illuminate\Database\Seeder;

class ChartOfAccountsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $referencedIds = JournalEntryLine::query()->pluck('account_id')->unique()->filter();

        Account::query()
            ->whereNull('slug')
            ->each(function (Account $account) {
                $account->update(['code' => 'legacy-' . $account->id]);
            });

        $tree = require __DIR__ . '/data/client_coa_tree.php';

        $builder = new ChartOfAccountsTreeBuilder(
            app(AccountPathService::class),
            app(AccountResolver::class)
        );

        $builder->seed($tree);
        $builder->clearCache();

        $this->call(ExpenseCategorySeeder::class);
    }
}
