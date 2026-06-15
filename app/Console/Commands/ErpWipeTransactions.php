<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ErpWipeTransactions extends Command
{
    protected $signature = 'erp:wipe-transactions {--force : Confirm destructive wipe}';

    protected $description = 'Remove all transactional ERP data while preserving roles, permissions, menu, and chart of accounts';

    /** @var array<int, string> */
    protected array $tables = [
        'journal_entry_lines',
        'journal_entries',
        'ledger_entries',
        'inventory_gl_posts',
        'inventory_valuations',
        'receipts',
        'credit_notes',
        'invoice_items',
        'invoices',
        'bill_payments',
        'purchase_bill_items',
        'purchase_bills',
        'purchase_order_items',
        'purchase_orders',
        'goods_receipt_items',
        'goods_receipts',
        'agent_advance_applications',
        'agent_advances',
        'agent_commission_settlements',
        'expenses',
        'salary_distributions',
        'order_status_history',
        'order_items',
        'delivery_pods',
        'delivery_items',
        'deliveries',
        'orders',
        'stock_movements',
        'stock_entries',
        'stock_audits',
        'production_runs',
        'batches',
        'customer_gifts',
        'campaigns',
        'sales_targets',
        'fleet_expenses',
        'logistics_bills',
        'employee_location_logs',
        'employee_overtime',
        'employee_leaves',
        'employee_allowances',
        'employee_equipment',
        'employee_badges',
        'visit_plans',
        'notification_dispatch_logs',
        'notifications',
    ];

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('This will delete all transactional data. Re-run with --force to confirm.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! config('app.allow_production_wipe', false)) {
            $this->error('Refusing to wipe production database. Set ALLOW_PRODUCTION_WIPE=true to override.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->truncate();
            $this->line("Truncated {$table}");
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Transactional data wiped. Roles, permissions, menu, COA, and reference catalogs preserved.');

        return self::SUCCESS;
    }
}
