<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['1000', 'Bank', 'asset'],
            ['1100', 'Accounts Receivable', 'asset'],
            ['1150', 'Input VAT', 'asset'],
            ['1160', 'Withholding Tax Receivable', 'asset'],
            ['1200', 'Agent Advances', 'asset'],
            ['1300', 'Raw Materials Inventory', 'asset'],
            ['1310', 'Finished Goods Inventory', 'asset'],
            ['1350', 'Work in Progress', 'asset'],
            ['2000', 'VAT Payable', 'liability'],
            ['2100', 'Accounts Payable', 'liability'],
            ['2190', 'GRNI Accrual', 'liability'],
            ['2200', 'Commission Payable', 'liability'],
            ['3000', 'Owner\'s Equity', 'equity'],
            ['4000', 'Sales Revenue', 'income'],
            ['4050', 'Sales Returns', 'income'],
            ['4100', 'Other Income', 'income'],
            ['5000', 'Cost of Goods Sold', 'expense'],
            ['5050', 'Purchases', 'expense'],
            ['5100', 'Selling & Distribution Expense', 'expense'],
            ['5150', 'Inventory Write-Off Expense', 'expense'],
            ['5200', 'Marketing Expense', 'expense'],
            ['5300', 'Payroll Expense', 'expense'],
            ['5350', 'Commission Expense', 'expense'],
            ['5400', 'Utilities Expense', 'expense'],
        ];

        foreach ($accounts as [$code, $name, $type]) {
            Account::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'is_active' => true]
            );
        }
    }
}
