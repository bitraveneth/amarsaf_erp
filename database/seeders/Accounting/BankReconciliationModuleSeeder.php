<?php

namespace Database\Seeders\Accounting;

use App\Models\Receipt;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Bank reconciliation.
 */
class BankReconciliationModuleSeeder extends Seeder
{
    public function run(): void
    {
        // For demo we simply mark all receipts up to today as reconciled.
        Receipt::whereDate('received_at', '<=', now()->toDateString())
            ->update(['reconciled' => true]);
    }
}
