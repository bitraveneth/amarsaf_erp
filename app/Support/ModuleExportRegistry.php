<?php

namespace App\Support;

use App\Helpers\Permission;
use App\Models\User;
use App\Support\ExportDateRange;
use App\Support\ModuleExports\ModuleExportBuilders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ModuleExportRegistry
{
    /** @var array<string, string> */
    public const DATE_COLUMNS = [
        'orders' => 'created_at',
        'deliveries' => 'created_at',
        'invoices' => 'issued_at',
        'bills' => 'bill_date',
        'production' => 'created_at',
        'stock-movements' => 'created_at',
        'expenses' => 'date',
        'journals' => 'entry_date',
        'goods-receipts' => 'received_at',
        'purchase-orders' => 'order_date',
        'campaigns' => 'start_date',
        'gifts' => 'date',
        'customer-returns' => 'created_at',
        'supplier-returns' => 'created_at',
        'agent-advances' => 'advanced_at',
        'salary-distributions' => 'period_start',
        'settlements' => 'period_start',
        'sales-targets' => 'period_start',
    ];

    public static function dateColumnFor(string $slug): ?string
    {
        return self::DATE_COLUMNS[$slug] ?? null;
    }
    public static function slugForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (self::definitions() as $slug => $definition) {
            if (($definition['route'] ?? null) === $routeName) {
                return $slug;
            }
        }

        return null;
    }

    public static function resolve(string $slug, Request $request): array
    {
        $definition = self::definitions()[$slug] ?? null;

        abort_unless($definition, 404);

        $builder = $definition['builder'];
        $rows = is_array($builder)
            ? call_user_func($builder, $request)
            : app($builder)->__invoke($request);

        return [
            'title' => $definition['title'] . self::titleRangeSuffix($request),
            'filename' => $definition['filename'] . ExportDateRange::filenameSuffix($request),
            'columns' => $definition['columns'],
            'rows' => $rows,
            'permission' => $definition['permission'],
        ];
    }

    protected static function titleRangeSuffix(Request $request): string
    {
        $resolved = ExportDateRange::resolve($request);

        if (! $resolved) {
            return '';
        }

        return ' (' . $resolved['label'] . ')';
    }

    public static function definitions(): array
    {
        return [
            'products' => [
                'route' => 'admin.products.index',
                'title' => 'Product Catalog',
                'filename' => 'products',
                'permission' => 'control.products',
                'columns' => ['SKU', 'Name', 'Type', 'Packaging', 'Tax rate', 'Price', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'products'],
            ],
            'materials' => [
                'route' => 'admin.materials.index',
                'title' => 'Materials',
                'filename' => 'materials',
                'permission' => 'control.products',
                'columns' => ['SKU', 'Name', 'Category', 'Type', 'UOM', 'Standard Cost', 'Supplier', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'materials'],
            ],
            'agents' => [
                'route' => 'admin.agents.index',
                'title' => 'Agent Master',
                'filename' => 'agents',
                'permission' => 'control.agents',
                'columns' => ['Agent', 'Location code', 'Zone', 'Parent', 'Credit limit', 'Status', 'Commission'],
                'builder' => [ModuleExportBuilders::class, 'agents'],
            ],
            'orders' => [
                'route' => 'admin.orders.index',
                'title' => 'Orders',
                'filename' => 'orders',
                'permission' => 'sales.manage',
                'columns' => ['Order', 'Agent', 'Type', 'Delivery', 'Status', 'Total', 'Commission'],
                'builder' => [ModuleExportBuilders::class, 'orders'],
            ],
            'suppliers' => [
                'route' => 'admin.suppliers.index',
                'title' => 'Suppliers',
                'filename' => 'suppliers',
                'permission' => 'control.suppliers',
                'columns' => ['Supplier', 'Contact person', 'Phone', 'Tax ID / BIN'],
                'builder' => [ModuleExportBuilders::class, 'suppliers'],
            ],
            'warehouses' => [
                'route' => 'admin.warehouses.index',
                'title' => 'Warehouses',
                'filename' => 'warehouses',
                'permission' => 'control.warehouses',
                'columns' => ['Name', 'Type', 'Code', 'Stock items', 'Capacity', 'Address', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'warehouses'],
            ],
            'employees' => [
                'route' => 'admin.employees.index',
                'title' => 'Employees',
                'filename' => 'employees',
                'permission' => 'control.employees',
                'columns' => ['Employee', 'Employee ID', 'Department', 'Position', 'Contact', 'Work zone'],
                'builder' => [ModuleExportBuilders::class, 'employees'],
            ],
            'deliveries' => [
                'route' => 'admin.deliveries.index',
                'title' => 'Deliveries',
                'filename' => 'deliveries',
                'permission' => 'control.warehouses',
                'columns' => ['Seq', 'Order', 'Agent', 'Route', 'Vehicle', 'Order status', 'Delivery status', 'POD'],
                'builder' => [ModuleExportBuilders::class, 'deliveries'],
            ],
            'invoices' => [
                'route' => 'admin.finance.index',
                'title' => 'Customer Invoices',
                'filename' => 'invoices',
                'permission' => 'accounting.manage',
                'columns' => ['Invoice', 'Issued', 'Order', 'Agent', 'Net', 'VAT', 'Withholding', 'Receipts', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'invoices'],
            ],
            'bills' => [
                'route' => 'admin.bills.index',
                'title' => 'Purchase Bills',
                'filename' => 'purchase-bills',
                'permission' => 'accounting.manage',
                'columns' => ['Bill #', 'Supplier', 'Date', 'Net total', 'Status', 'Paid'],
                'builder' => [ModuleExportBuilders::class, 'bills'],
            ],
            'production' => [
                'route' => 'admin.production.index',
                'title' => 'Production Runs',
                'filename' => 'production-runs',
                'permission' => 'manufacturing.manage',
                'columns' => ['Product', 'Batch', 'Line', 'Shift', 'Quantity', 'Order no.', 'Status', 'QC', 'Approved by', 'Stock'],
                'builder' => [ModuleExportBuilders::class, 'production'],
            ],
            'inventory' => [
                'route' => 'admin.inventory.index',
                'title' => 'Expiring Stock',
                'filename' => 'inventory-expiring',
                'permission' => 'inventory.manage',
                'columns' => ['Product', 'Batch', 'Expiry', 'Warehouse', 'Quantity'],
                'builder' => [ModuleExportBuilders::class, 'inventory'],
            ],
            'low-stock' => [
                'route' => 'admin.inventory.low-stock',
                'title' => 'Low Stock',
                'filename' => 'inventory-low-stock',
                'permission' => 'inventory.manage',
                'columns' => ['Product', 'SKU', 'Available', 'Reorder level', 'Shortage'],
                'builder' => [ModuleExportBuilders::class, 'lowStock'],
            ],
            'boms' => [
                'route' => 'admin.boms.index',
                'title' => 'Bills of Materials',
                'filename' => 'boms',
                'permission' => 'manufacturing.manage',
                'columns' => ['Product', 'SKU', 'BOM name', 'Components', 'Unit cost', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'boms'],
            ],
            'batches' => [
                'route' => 'admin.batches.index',
                'title' => 'Batches',
                'filename' => 'batches',
                'permission' => 'manufacturing.manage',
                'columns' => ['Product', 'Batch code', 'Production', 'Expiry', 'QC status'],
                'builder' => [ModuleExportBuilders::class, 'batches'],
            ],
            'purchase-orders' => [
                'route' => 'admin.purchase-orders.index',
                'title' => 'Purchase Orders',
                'filename' => 'purchase-orders',
                'permission' => 'control.suppliers',
                'columns' => ['PO', 'Supplier', 'Order date', 'Status', 'Lines'],
                'builder' => [ModuleExportBuilders::class, 'purchaseOrders'],
            ],
            'goods-receipts' => [
                'route' => 'admin.goods-receipts.index',
                'title' => 'Goods Receipts',
                'filename' => 'goods-receipts',
                'permission' => 'inventory.manage',
                'columns' => ['GRN', 'Supplier', 'Warehouse', 'Received at', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'goodsReceipts'],
            ],
            'stock-movements' => [
                'route' => 'admin.stock.movements',
                'title' => 'Stock Movements',
                'filename' => 'stock-movements',
                'permission' => 'inventory.manage',
                'columns' => ['Product', 'Warehouse', 'Type', 'Quantity', 'Reference', 'Time'],
                'builder' => [ModuleExportBuilders::class, 'stockMovements'],
            ],
            'expenses' => [
                'route' => 'admin.expenses.index',
                'title' => 'Expenses',
                'filename' => 'expenses',
                'permission' => 'accounting.manage',
                'columns' => ['Date', 'Category', 'Description', 'Amount', 'Reference', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'expenses'],
            ],
            'accounts' => [
                'route' => 'admin.accounts.index',
                'title' => 'Chart of Accounts',
                'filename' => 'accounts',
                'permission' => 'accounting.manage',
                'columns' => ['Code', 'Account', 'Type', 'Status', 'Updated'],
                'builder' => [ModuleExportBuilders::class, 'accounts'],
            ],
            'journals' => [
                'route' => 'admin.journals.index',
                'title' => 'Journal Entries',
                'filename' => 'journal-entries',
                'permission' => 'accounting.manage',
                'columns' => ['Number', 'Date', 'Type', 'Description', 'Debit', 'Credit', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'journals'],
            ],
            'sales-targets' => [
                'route' => 'admin.sales-targets.index',
                'title' => 'Sales Targets',
                'filename' => 'sales-targets',
                'permission' => 'sales.manage',
                'columns' => ['Owner', 'Owner type', 'Period start', 'Period end', 'Target'],
                'builder' => [ModuleExportBuilders::class, 'salesTargets'],
            ],
            'commissions' => [
                'route' => 'admin.commissions.index',
                'title' => 'Commission Summary',
                'filename' => 'commission-summary',
                'permission' => 'control.agents',
                'columns' => ['Agent ID', 'Agent name', 'Sales', 'Commission', 'Effective rate %'],
                'builder' => [ModuleExportBuilders::class, 'commissions'],
            ],
            'settlements' => [
                'route' => 'admin.settlements.index',
                'title' => 'Commission Settlements',
                'filename' => 'commission-settlements',
                'permission' => 'control.agents',
                'columns' => ['Agent', 'Sales', 'Commission', 'Rate %', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'settlements'],
            ],
            'agent-advances' => [
                'route' => 'admin.agent-advances.index',
                'title' => 'Agent Advances',
                'filename' => 'agent-advances',
                'permission' => 'accounting.manage',
                'columns' => ['Agent', 'Date', 'Advance', 'Applied', 'Available', 'Method', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'agentAdvances'],
            ],
            'vehicles' => [
                'route' => 'admin.vehicles.index',
                'title' => 'Vehicles',
                'filename' => 'vehicles',
                'permission' => 'control.warehouses',
                'columns' => ['Vehicle', 'Type', 'License plate', 'Driver', 'Capacity (crates)', 'Active'],
                'builder' => [ModuleExportBuilders::class, 'vehicles'],
            ],
            'packaging' => [
                'route' => 'admin.packaging.index',
                'title' => 'Packaging Types',
                'filename' => 'packaging-types',
                'permission' => 'control.products',
                'columns' => ['Name', 'Unit', 'Description', 'Updated'],
                'builder' => [ModuleExportBuilders::class, 'packaging'],
            ],
            'tax-classes' => [
                'route' => 'admin.tax-classes.index',
                'title' => 'Tax Classes',
                'filename' => 'tax-classes',
                'permission' => 'control.products',
                'columns' => ['Tax class', 'Rate', 'HSN/SAC', 'Last updated'],
                'builder' => [ModuleExportBuilders::class, 'taxClasses'],
            ],
            'users' => [
                'route' => 'admin.users.index',
                'title' => 'Users',
                'filename' => 'users',
                'permission' => 'system.settings',
                'columns' => ['Name', 'Email', 'Role', 'Access summary', 'Linked employee'],
                'builder' => [ModuleExportBuilders::class, 'users'],
            ],
            'webhooks' => [
                'route' => 'admin.webhooks.index',
                'title' => 'Webhooks',
                'filename' => 'webhooks',
                'permission' => 'system.settings',
                'columns' => ['Name', 'URL', 'Events', 'Active'],
                'builder' => [ModuleExportBuilders::class, 'webhooks'],
            ],
            'campaigns' => [
                'route' => 'admin.campaigns.index',
                'title' => 'Campaigns',
                'filename' => 'campaigns',
                'permission' => 'sales.manage',
                'columns' => ['Campaign', 'Platform', 'Period start', 'Period end', 'Reach', 'Impressions', 'Cost', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'campaigns'],
            ],
            'gifts' => [
                'route' => 'admin.gifts.index',
                'title' => 'Customer Gifts',
                'filename' => 'customer-gifts',
                'permission' => 'sales.manage',
                'columns' => ['Date', 'Agent', 'Handled by', 'Occasion', 'Gift', 'Amount', 'Campaign', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'gifts'],
            ],
            'customer-returns' => [
                'route' => 'admin.returns.customer.index',
                'title' => 'Customer Returns',
                'filename' => 'customer-returns',
                'permission' => 'sales.manage',
                'columns' => ['Date', 'Order', 'Customer', 'Product', 'Warehouse', 'Quantity', 'Notes'],
                'builder' => [ModuleExportBuilders::class, 'customerReturns'],
            ],
            'supplier-returns' => [
                'route' => 'admin.returns.supplier.index',
                'title' => 'Supplier Returns',
                'filename' => 'supplier-returns',
                'permission' => 'control.suppliers',
                'columns' => ['Date', 'Product', 'Warehouse', 'Quantity', 'Notes'],
                'builder' => [ModuleExportBuilders::class, 'supplierReturns'],
            ],
            'accounting-periods' => [
                'route' => 'admin.accounting-periods.index',
                'title' => 'Accounting Periods',
                'filename' => 'accounting-periods',
                'permission' => 'accounting.manage',
                'columns' => ['Fiscal year', 'Period', 'Start date', 'End date', 'Status'],
                'builder' => [ModuleExportBuilders::class, 'accountingPeriods'],
            ],
            'salary-distributions' => [
                'route' => 'admin.salary-distributions.index',
                'title' => 'Salary Distributions',
                'filename' => 'salary-distributions',
                'permission' => 'accounting.manage',
                'columns' => ['Employee', 'Period start', 'Period end', 'Base salary', 'Bonus', 'TA', 'DA', 'Commission', 'Payment method', 'Document', 'Total'],
                'builder' => [ModuleExportBuilders::class, 'salaryDistributions'],
            ],
            'warehouse-locations' => [
                'route' => 'admin.warehouse-locations.index',
                'title' => 'Warehouse Locations',
                'filename' => 'warehouse-locations',
                'permission' => 'control.warehouses',
                'columns' => ['Warehouse', 'Location code', 'Description'],
                'builder' => [ModuleExportBuilders::class, 'warehouseLocations'],
            ],
            'delivery-routes' => [
                'route' => 'admin.delivery-routes.index',
                'title' => 'Delivery Routes',
                'filename' => 'delivery-routes',
                'permission' => 'control.warehouses',
                'columns' => ['Route', 'Zone', 'Schedule', 'Vehicle', 'Driver'],
                'builder' => [ModuleExportBuilders::class, 'deliveryRoutes'],
            ],
            'badges' => [
                'route' => 'admin.badges.index',
                'title' => 'Badges',
                'filename' => 'badges',
                'permission' => 'control.employees',
                'columns' => ['Name', 'Code', 'Color', 'Status', 'Description'],
                'builder' => [ModuleExportBuilders::class, 'badges'],
            ],
            'mrp' => [
                'route' => 'admin.mrp.index',
                'title' => 'MRP Reorder Suggestions',
                'filename' => 'mrp-reorder-suggestions',
                'permission' => 'inventory.manage',
                'columns' => ['Product', 'SKU', 'Available', 'Reorder', 'Suggest PO'],
                'builder' => [ModuleExportBuilders::class, 'mrp'],
            ],
        ];
    }

    public static function modulesGroupedForUser(?User $user, array $query = []): array
    {
        $grouped = [];

        foreach (self::definitions() as $slug => $definition) {
            if (! Permission::can($user, $definition['permission'])) {
                continue;
            }

            $category = self::categoryFor($definition['permission']);
            $dateColumn = self::dateColumnFor($slug);

            $grouped[$category][] = [
                'slug' => $slug,
                'title' => $definition['title'],
                'column_count' => count($definition['columns']),
                'supports_date_range' => $dateColumn !== null,
                'module_url' => route($definition['route']),
                'csv_url' => self::exportUrl($slug, 'csv', $query),
                'pdf_url' => self::exportUrl($slug, 'pdf', $query),
            ];
        }

        ksort($grouped);

        foreach ($grouped as &$modules) {
            usort($modules, fn (array $a, array $b) => strcasecmp($a['title'], $b['title']));
        }

        return $grouped;
    }

    public static function featuredModulesForUser(?User $user, array $query = []): array
    {
        $featured = [
            ['slug' => 'orders', 'badge' => 'Most used', 'hint' => 'Sales & return orders'],
            ['slug' => 'agents', 'badge' => 'Important', 'hint' => 'Selling partner master'],
            ['slug' => 'invoices', 'badge' => 'Finance', 'hint' => 'Issued invoices'],
            ['slug' => 'products', 'badge' => 'Master data', 'hint' => 'Product catalog'],
            ['slug' => 'production', 'badge' => 'Operations', 'hint' => 'Daily production'],
            ['slug' => 'expenses', 'badge' => 'Finance', 'hint' => 'Expense entries'],
        ];

        $available = collect(self::modulesGroupedForUser($user, $query))
            ->flatten(1)
            ->keyBy('slug');

        return collect($featured)
            ->filter(fn (array $item) => $available->has($item['slug']))
            ->map(fn (array $item) => array_merge($item, $available->get($item['slug'])))
            ->values()
            ->all();
    }

    public static function categoryFor(string $permission): string
    {
        return match (true) {
            str_starts_with($permission, 'control.products') => 'Products & packaging',
            str_starts_with($permission, 'control.agents') => 'Sales & agents',
            str_starts_with($permission, 'sales.') => 'Sales & agents',
            str_starts_with($permission, 'inventory.') => 'Inventory & warehouse',
            str_starts_with($permission, 'control.warehouses') => 'Inventory & warehouse',
            str_starts_with($permission, 'manufacturing.') => 'Manufacturing',
            str_starts_with($permission, 'accounting.') => 'Finance & accounting',
            str_starts_with($permission, 'control.suppliers') => 'Procurement',
            str_starts_with($permission, 'control.employees') => 'HR & people',
            str_starts_with($permission, 'system.') => 'System administration',
            default => 'Other',
        };
    }

    public static function exportUrl(string $slug, string $format, array $query = []): string
    {
        return route('admin.modules.export', array_merge([
            'module' => $slug,
            'format' => $format,
        ], $query));
    }

    public static function currentExportUrl(string $format): ?string
    {
        $slug = self::slugForRoute(Route::currentRouteName());

        if (! $slug) {
            return null;
        }

        return self::exportUrl($slug, $format, request()->query());
    }
}
