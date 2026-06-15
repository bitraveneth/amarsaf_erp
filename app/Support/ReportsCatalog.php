<?php

namespace App\Support;

class ReportsCatalog
{
    /**
     * @return array<int, array{key: string, label: string, description: string, reports: array<int, array<string, mixed>>}>
     */
    public static function groups(array $periodQuery = []): array
    {
        return collect(self::definitions())
            ->groupBy('category')
            ->map(function ($reports, $category) use ($periodQuery) {
                return [
                    'key' => (string) str($category)->slug(),
                    'label' => (string) $category,
                    'description' => self::categoryDescription((string) $category),
                    'reports' => $reports
                        ->map(fn (array $report) => self::hydrateReport($report, $periodQuery))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function flat(array $periodQuery = []): array
    {
        return collect(self::definitions())
            ->map(fn (array $report) => self::hydrateReport($report, $periodQuery))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function featured(array $periodQuery = []): array
    {
        return collect(self::definitions())
            ->filter(fn (array $report) => ! empty($report['featured']))
            ->map(fn (array $report) => self::hydrateReport($report, $periodQuery))
            ->values()
            ->all();
    }

    /**
     * Sidebar menu items for Reports & analytics (excluding Data export — added by MenuHelper).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sidebarMenuItems(): array
    {
        $items = [
            [
                'name' => 'Reports dashboard',
                'icon' => 'reports',
                'path' => '/admin/reports-dashboard',
                'permission' => 'reports.view',
            ],
        ];

        foreach (self::menuCategories() as $menuCategory) {
            $reports = collect(self::definitions())
                ->filter(fn (array $report) => ($report['menuCategory'] ?? '') === $menuCategory['key'])
                ->values();

            if ($reports->isEmpty()) {
                continue;
            }

            $subItems = $reports->map(function (array $report) use ($menuCategory) {
                if (! empty($report['hubOnly'])) {
                    return null;
                }

                return [
                    'name' => $report['menuTitle'] ?? $report['title'],
                    'path' => self::menuPath($report['route']),
                    'permission' => $report['permission'] ?? 'reports.view',
                ];
            })->filter()->values()->all();

            $items[] = [
                'name' => $menuCategory['label'],
                'icon' => $menuCategory['icon'],
                'path' => $menuCategory['hubRoute'] ? self::menuPath($menuCategory['hubRoute']) : '#',
                'expandOnly' => (bool) ($menuCategory['expandOnly'] ?? false),
                'permission' => 'reports.view',
                'subItems' => $subItems,
            ];
        }

        return $items;
    }

    /**
     * Hub page payload for a sidebar report group.
     *
     * @return array<string, mixed>
     */
    public static function categoryHub(string $menuCategoryKey, array $periodQuery = []): array
    {
        $category = collect(self::menuCategories())->firstWhere('key', $menuCategoryKey);

        if (! $category) {
            abort(404);
        }

        $reports = collect(self::definitions())
            ->filter(fn (array $report) => ($report['menuCategory'] ?? '') === $menuCategoryKey && empty($report['hubOnly']))
            ->map(fn (array $report) => self::hydrateReport($report, $periodQuery))
            ->values();

        return [
            'title' => $category['label'],
            'subtitle' => self::menuCategorySubtitle($menuCategoryKey),
            'hubAction' => ($category['hubRoute'] ?? null) ? route($category['hubRoute']) : route('admin.reports.dashboard'),
            'reports' => $reports->all(),
        ];
    }

    public static function menuCategoryLabel(string $menuCategoryKey): ?string
    {
        return collect(self::menuCategories())->firstWhere('key', $menuCategoryKey)['label'] ?? null;
    }

    protected static function menuCategorySubtitle(string $key): string
    {
        return match ($key) {
            'financial' => 'Profit, balance sheet, cash, and tax filing.',
            'receivables' => 'Who owes you and who you still need to pay.',
            'costs' => 'Expense categories, utilities, and operating overhead.',
            'logistics' => 'Carrier bills, fleet spend, and route profitability.',
            'operations' => 'Stock worth, factory output, and lot tracking.',
            'sales' => 'Agent rankings, commissions, and sales registers.',
            'accountant' => 'Trial balance, ledger detail, payroll, and journals.',
            default => '',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function menuCategories(): array
    {
        return [
            ['key' => 'financial', 'label' => 'Finance reports', 'icon' => 'reports', 'hubRoute' => null, 'expandOnly' => true],
            ['key' => 'receivables', 'label' => 'AR & AP reports', 'icon' => 'accounting', 'hubRoute' => null, 'expandOnly' => true],
            ['key' => 'costs', 'label' => 'Cost reports', 'icon' => 'chart', 'hubRoute' => 'admin.reports.costs', 'expandOnly' => false],
            ['key' => 'logistics', 'label' => 'Logistics reports', 'icon' => 'suppliers', 'hubRoute' => 'admin.reports.logistics', 'expandOnly' => false],
            ['key' => 'operations', 'label' => 'Inventory reports', 'icon' => 'manufacturing', 'hubRoute' => 'admin.reports.operations', 'expandOnly' => false],
            ['key' => 'sales', 'label' => 'Sales reports', 'icon' => 'agents', 'hubRoute' => 'admin.reports.sales', 'expandOnly' => false],
            ['key' => 'accountant', 'label' => 'Accounting reports', 'icon' => 'ledger', 'hubRoute' => 'admin.reports.accountant', 'expandOnly' => false],
        ];
    }

    protected static function menuPath(string $routeName): string
    {
        try {
            return route($routeName, [], false);
        } catch (\Throwable) {
            return '/admin/' . ltrim(str_replace('.', '/', preg_replace('#^admin\.#', '', $routeName)), '/');
        }
    }

    protected static function hydrateReport(array $report, array $periodQuery): array
    {
        $routeName = $report['route'];
        $params = match ($report['periodType'] ?? 'none') {
            'range' => $periodQuery,
            'as_of' => [],
            default => [],
        };

        return array_merge($report, [
            'href' => route($routeName, $params),
            'baseUrl' => route($routeName),
            'asOfKey' => self::asOfQueryKey($report['id'] ?? ''),
            'periodNote' => self::periodNote($report['periodType'] ?? 'none'),
            'exportModule' => $report['exportModule'] ?? null,
        ]);
    }

    protected static function asOfQueryKey(string $id): string
    {
        return match ($id) {
            'balance-sheet' => 'date',
            'ar-aging', 'ap-aging', 'outstanding-invoices', 'outstanding-bills' => 'as_of',
            default => 'as_of',
        };
    }

    protected static function periodNote(string $periodType): string
    {
        return match ($periodType) {
            'range' => 'Uses the period selected on this dashboard.',
            'as_of' => 'Point-in-time report — pick an as-of date on the report page.',
            default => 'Not filtered by the dashboard period.',
        };
    }

    protected static function categoryDescription(string $category): string
    {
        return match ($category) {
            'Financial statements' => 'Profit, balance sheet, cash, and tax filing.',
            'Receivables & payables' => 'Who owes you and who you still need to pay.',
            'Operating costs' => 'Expenses by category, utilities, and overhead.',
            'Logistics & fleet' => 'Carrier bills, fleet spend, and route profitability.',
            'Inventory & production' => 'Stock worth, factory output, and lot tracking.',
            'Accountant tools' => 'Trial balance, ledger detail, payroll, and journals.',
            'Sales & network' => 'Agent rankings, commissions, and sales registers.',
            'Executive' => 'Management overview and month-end close.',
            default => '',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            // Financial statements
            self::def('income-statement', 'Financial statements', 'Income statement', 'admin.reports.pl', 'range', 'profit-loss', 'financial', true, 'Finance', 'Revenue, costs, and net profit'),
            self::def('balance-sheet', 'Financial statements', 'Balance sheet', 'admin.reports.bs', 'as_of', 'balance-sheet', 'financial', false, 'Finance', 'Assets, liabilities, and equity'),
            self::def('cash-flow', 'Financial statements', 'Cash flow', 'admin.reports.cashflow', 'range', 'cash-flow', 'financial', true, 'Finance', 'Cash in and out of bank accounts'),
            self::def('vat-report', 'Financial statements', 'VAT report', 'admin.reports.vat', 'range', 'vat-report', 'financial', false, 'Tax', 'Input and output VAT summary'),
            self::def('executive-summary', 'Executive', 'Executive summary', 'admin.reports.executive', 'range', 'executive-summary', 'financial', true, 'Executive', 'KPI rollup for management review', permission: 'reports.view'),

            // Receivables & payables
            self::def('ar-aging', 'Receivables & payables', 'AR aging', 'admin.reports.ar-aging', 'as_of', 'ar-aging', 'receivables', true, 'AR', 'Customers who have not paid yet'),
            self::def('ap-aging', 'Receivables & payables', 'AP aging', 'admin.reports.ap-aging', 'as_of', 'ap-aging', 'receivables', false, 'AP', 'Supplier bills you still owe'),
            self::def('outstanding-bills', 'Receivables & payables', 'Outstanding bills', 'admin.reports.outstanding-bills', 'as_of', 'outstanding-bills', 'receivables', false, 'AP', 'Open supplier balances'),

            // Operating costs
            self::def('expense-summary', 'Operating costs', 'Expense summary', 'admin.reports.expense-summary', 'range', 'expense-summary', 'costs', true, 'Costs', 'Operating spend by category'),
            self::def('utilities-report', 'Operating costs', 'Utilities & electricity', 'admin.reports.utilities', 'range', 'utilities-report', 'costs', true, 'Utilities', 'Electricity and utility bills'),

            // Logistics & fleet
            self::def('route-costs', 'Logistics & fleet', 'Route cost vs sales', 'admin.reports.route-costs', 'range', 'route-costs', 'logistics', true, 'Logistics', 'Fleet and carrier cost by route'),
            self::def('logistics-bills', 'Logistics & fleet', 'Logistics bills summary', 'admin.reports.logistics-bills', 'range', 'logistics-bills', 'logistics', false, 'Logistics', 'Carrier invoices and payment status'),
            self::def('fleet-expenses', 'Logistics & fleet', 'Fleet expense summary', 'admin.reports.fleet-expenses', 'range', 'fleet-expenses', 'logistics', false, 'Fleet', 'Fuel, maintenance, and vehicle costs'),

            // Inventory & production
            self::def('inventory-valuation', 'Inventory & production', 'Inventory valuation', 'admin.reports.inventory-valuation', 'none', 'inventory-valuation', 'operations', true, 'Stock', 'Raw materials vs finished goods worth'),
            self::def('production-summary', 'Inventory & production', 'Production reports', 'admin.reports.production', 'range', 'production-summary', 'operations', false, 'Factory', 'Output and material cost'),
            self::def('production-variance', 'Inventory & production', 'Production variance', 'admin.reports.production-variance', 'range', 'production-variance', 'operations', false, 'Factory', 'Standard vs actual cost'),
            self::def('batch-trace', 'Inventory & production', 'Batch traceability', 'admin.reports.batch-trace', 'none', 'batch-trace', 'operations', false, 'QA', 'Follow a lot through the system'),
            self::def('manufacturing-schedule', 'Inventory & production', 'Manufacturing schedule', 'admin.reports.manufacturing-schedule', 'range', 'manufacturing-schedule', 'operations', false, 'Plan', 'Planned and in-progress runs'),
            self::def('low-stock', 'Inventory & production', 'Low stock alert', 'admin.reports.low-stock', 'none', 'low-stock-report', 'operations', false, 'Stock', 'Products below reorder level'),
            self::def('delivery-performance', 'Inventory & production', 'Delivery performance', 'admin.reports.delivery-performance', 'range', 'delivery-performance', 'operations', false, 'Dispatch', 'Deliveries and POD status'),

            // Sales & network
            self::def('agent-performance', 'Sales & network', 'Agent performance', 'admin.reports.agents', 'range', 'agent-performance', 'sales', false, 'Sales', 'Sales and collections by agent'),
            self::def('commission-summary', 'Sales & network', 'Commission summary', 'admin.reports.commissions', 'range', 'commission-summary', 'sales', true, 'Sales', 'Agent commission for the period', permission: 'control.agents'),
            self::def('sales-register', 'Sales & network', 'Sales register', 'admin.reports.sales-register', 'range', 'sales-register', 'sales', true, 'Revenue', 'Invoice audit trail'),
            self::def('outstanding-invoices', 'Sales & network', 'Outstanding invoices', 'admin.reports.outstanding-invoices', 'as_of', 'outstanding-invoices', 'sales', true, 'Collections', 'Open customer balances'),
            self::def('sales-targets', 'Sales & network', 'Sales target vs actual', 'admin.reports.sales-targets', 'range', 'sales-targets', 'sales', false, 'Targets', 'Target achievement by agent'),

            // Accountant tools
            self::def('trial-balance', 'Accountant tools', 'Trial balance', 'admin.reports.trial-balance', 'range', 'trial-balance', 'accountant', true, 'Ledger', 'All account balances'),
            self::def('general-ledger', 'Accountant tools', 'General ledger', 'admin.reports.general-ledger', 'range', 'general-ledger', 'accountant', false, 'Ledger', 'Posted lines for one account'),
            self::def('payroll-summary', 'Accountant tools', 'Payroll summary', 'admin.reports.payroll', 'range', 'payroll-summary', 'accountant', false, 'HR', 'Salary distributions in period'),
            self::def('journal-register', 'Accountant tools', 'Journal register', 'admin.reports.journal-register', 'range', 'journal-register', 'accountant', false, 'Ledger', 'Posted journal vouchers'),
            self::def('bank-reconciliation', 'Accountant tools', 'Bank reconciliation', 'admin.reports.bank-reconciliation', 'range', 'bank-reconciliation', 'accountant', false, 'Bank', 'Receipts and payments in period'),
            self::def('customer-statement', 'Accountant tools', 'Customer statement', 'admin.reports.customer-statement', 'range', 'customer-statement', 'accountant', false, 'AR', 'Invoice and receipt activity by agent'),
            self::def('supplier-statement', 'Accountant tools', 'Supplier statement', 'admin.reports.supplier-statement', 'range', 'supplier-statement', 'accountant', false, 'AP', 'Bills and payments by supplier'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function def(
        string $id,
        string $category,
        string $title,
        string $route,
        string $periodType,
        string $exportModule,
        string $menuCategory,
        bool $featured,
        string $badge,
        string $hint,
        string $permission = 'reports.view',
        bool $hubOnly = false,
    ): array {
        return [
            'id' => $id,
            'category' => $category,
            'badge' => $badge,
            'title' => $title,
            'hint' => $hint,
            'description' => $hint,
            'bullets' => [],
            'periodType' => $periodType,
            'route' => $route,
            'exportModule' => $exportModule,
            'menuCategory' => $menuCategory,
            'permission' => $permission,
            'featured' => $featured,
            'hubOnly' => $hubOnly,
        ];
    }
}
