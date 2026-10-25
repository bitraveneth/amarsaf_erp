<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class BusinessDataReset
{
    /**
     * Login, permissions, menus, and company settings are never cleared.
     *
     * @var array<int, string>
     */
    protected const PROTECTED = [
        'users',
        'password_reset_tokens',
        'personal_access_tokens',
        'permissions',
        'role_permissions',
        'roles',
        'user_roles',
        'user_permissions',
        'user_warehouse_scopes',
        'system_settings',
        'migrations',
        'failed_jobs',
        'jobs',
        'job_batches',
        'cache',
        'cache_locks',
        'sessions',
        'menu_groups',
        'menu_items',
        'webhook_endpoints',
    ];

    /**
     * @return array<string, array{label: string, description: string, tables: array<int, string>}>
     */
    public static function groups(): array
    {
        return [
            'sales' => [
                'label' => 'Sales orders and invoices',
                'description' => 'Orders, deliveries, invoices, receipts, credit notes, and campaigns.',
                'tables' => [
                    'delivery_pods',
                    'delivery_items',
                    'deliveries',
                    'order_status_history',
                    'order_items',
                    'orders',
                    'agent_advance_applications',
                    'credit_notes',
                    'receipts',
                    'invoice_items',
                    'invoices',
                    'customer_gifts',
                    'campaigns',
                    'sales_targets',
                    'visit_plans',
                    'notification_dispatch_logs',
                    'notifications',
                ],
            ],
            'manufacturing' => [
                'label' => 'Manufacturing',
                'description' => 'Production runs and material issues.',
                'tables' => [
                    'production_material_issue_items',
                    'production_material_issues',
                    'production_runs',
                ],
            ],
            'purchasing' => [
                'label' => 'Purchasing and suppliers',
                'description' => 'Purchase orders, goods receipts, bills, and suppliers.',
                'tables' => [
                    'goods_receipt_items',
                    'goods_receipts',
                    'purchase_order_items',
                    'purchase_orders',
                    'bill_payments',
                    'purchase_bill_items',
                    'purchase_bills',
                    'suppliers',
                    'supplier_supplier_product_category',
                ],
            ],
            'inventory' => [
                'label' => 'Inventory and warehouses',
                'description' => 'Stock, batches, warehouse locations, and stock audits.',
                'tables' => [
                    'stock_movements',
                    'stock_entries',
                    'stock_audits',
                    'inventory_gl_posts',
                    'inventory_valuations',
                    'batches',
                    'warehouse_locations',
                    'warehouses',
                ],
            ],
            'logistics' => [
                'label' => 'Fleet and logistics',
                'description' => 'Vehicles, routes, carriers, and logistics bills.',
                'tables' => [
                    'vehicle_schedules',
                    'vehicles',
                    'delivery_routes',
                    'fleet_expenses',
                    'fleet_recurring_charges',
                    'logistics_bill_payments',
                    'logistics_bill_lines',
                    'logistics_bills',
                    'carrier_rate_cards',
                    'transport_carriers',
                ],
            ],
            'hr' => [
                'label' => 'HR and payroll',
                'description' => 'Employees, leave, badges, and salary records. Login users stay.',
                'tables' => [
                    'employee_location_logs',
                    'employee_overtime',
                    'employee_leave_balances',
                    'employee_leaves',
                    'employee_badges',
                    'employee_allowances',
                    'employee_equipment',
                    'employee_contracts',
                    'employees',
                    'badges',
                    'salary_distributions',
                ],
            ],
            'accounting' => [
                'label' => 'Accounting',
                'description' => 'Journals, ledger lines, expenses, and the chart of accounts.',
                'tables' => [
                    'journal_entry_lines',
                    'journal_entries',
                    'ledger_entries',
                    'expenses',
                    'expense_categories',
                    'accounting_periods',
                    'fiscal_years',
                    'accounts',
                ],
            ],
            'customers' => [
                'label' => 'Customers and agents',
                'description' => 'Agents, price lists, commissions, and advances.',
                'tables' => [
                    'agent_price_lists',
                    'agent_commission_settlements',
                    'agent_commission_rules',
                    'agent_advances',
                    'company_commission_rules',
                    'agents',
                    'kyc_document_types',
                ],
            ],
            'products' => [
                'label' => 'Products and formulas',
                'description' => 'Products, bills of material, packaging, tax classes, and units.',
                'tables' => [
                    'bom_items',
                    'bill_of_materials',
                    'packaging_conversions',
                    'products',
                    'packaging_types',
                    'tax_classes',
                    'units_of_measure',
                    'material_categories',
                    'supplier_product_categories',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, description: string, rows: int, tables: int}>
     */
    public function summary(): array
    {
        $summary = [];

        foreach (self::groups() as $key => $group) {
            $rows = 0;
            $tables = 0;

            foreach ($group['tables'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $tables++;
                $rows += (int) DB::table($table)->count();
            }

            $summary[] = [
                'key' => $key,
                'label' => $group['label'],
                'description' => $group['description'],
                'rows' => $rows,
                'tables' => $tables,
            ];
        }

        return $summary;
    }

    /**
     * @param  array<int, string>  $groupKeys
     * @return array{tables: int, rows: int, labels: array<int, string>}
     */
    public function clear(array $groupKeys): array
    {
        $selected = array_values(array_unique(array_filter($groupKeys, fn ($key) => is_string($key) && $key !== '')));
        $groups = self::groups();
        $unknown = array_diff($selected, array_keys($groups));

        if ($selected === [] || $unknown !== []) {
            throw new RuntimeException('Choose at least one data group to clear.');
        }

        $tables = [];
        $labels = [];

        foreach (array_keys($groups) as $key) {
            if (! in_array($key, $selected, true)) {
                continue;
            }

            $labels[] = $groups[$key]['label'];

            foreach ($groups[$key]['tables'] as $table) {
                if (in_array($table, self::PROTECTED, true) || isset($tables[$table])) {
                    continue;
                }

                $tables[$table] = $table;
            }
        }

        $clearedTables = 0;
        $clearedRows = 0;

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $clearedRows += (int) DB::table($table)->delete();
                $clearedTables++;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return [
            'tables' => $clearedTables,
            'rows' => $clearedRows,
            'labels' => $labels,
        ];
    }
}
