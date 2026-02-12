<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups(): array
    {
        return [
            [
                'title' => 'Overview',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'icon' => 'dashboard',
                        'path' => '/admin',
                    ],
                ],
            ],
            [
                'title' => 'Control (Masters & Settings)',
                'items' => [
                    [
                        'name' => 'Products',
                        'icon' => 'products',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Products', 'path' => '/admin/products'],
                            ['name' => 'Materials (raw / service)', 'path' => '/admin/materials'],
                            ['name' => 'Packaging types', 'path' => '/admin/packaging'],
                            ['name' => 'Tax & VAT classes', 'path' => '/admin/tax-classes'],
                            ['name' => 'Price lists', 'path' => '/admin/products/prices'],
                        ],
                    ],
                    [
                        'name' => 'Agents',
                        'icon' => 'agents',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Agents', 'path' => '/admin/agents'],
                            ['name' => 'Commission rules', 'path' => '/admin/commission-rules'],
                        ],
                    ],
                    [
                        'name' => 'Suppliers',
                        'icon' => 'suppliers',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Suppliers', 'path' => '/admin/suppliers'],
                            ['name' => 'Purchase bills', 'path' => '/admin/bills'],
                        ],
                    ],
                    [
                        'name' => 'Warehouses',
                        'icon' => 'warehouses',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Warehouses', 'path' => '/admin/warehouses'],
                            ['name' => 'Warehouse locations', 'path' => '/admin/warehouse-locations'],
                            ['name' => 'Delivery routes', 'path' => '/admin/delivery-routes'],
                            ['name' => 'Fleet', 'path' => '/admin/vehicles'],
                        ],
                    ],
                    [
                        'name' => 'Employees',
                        'icon' => 'employees',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Employees', 'path' => '/admin/employees'],
                            ['name' => 'Contracts', 'path' => '/admin/contracts'],
                            ['name' => 'Allowances', 'path' => '/admin/allowances'],
                            ['name' => 'Equipment', 'path' => '/admin/equipment'],
                            ['name' => 'Leaves', 'path' => '/admin/leaves'],
                            ['name' => 'Badges', 'path' => '/admin/badges'],
                        ],
                    ],
                    [
                        'name' => 'System settings',
                        'icon' => 'settings',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Help & configuration guide', 'path' => '/admin/help'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Manufacturing',
                'items' => [
                    [
                        'name' => 'Manufacturing',
                        'icon' => 'manufacturing',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'BOMs (Bill of Materials)', 'path' => '/admin/boms'],
                            ['name' => 'Production orders & runs', 'path' => '/admin/production'],
                            ['name' => 'Pending receipts', 'path' => '/admin/production/pending-receipts'],
                            ['name' => 'Batches & lots', 'path' => '/admin/batches'],
                            ['name' => 'Production analysis', 'path' => '/admin/reports/production'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Inventory (Core operations)',
                'items' => [
                    [
                        'name' => 'Inventory',
                        'icon' => 'inventory',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Inventory dashboard', 'path' => '/admin/inventory'],
                            ['name' => 'Material stock', 'path' => '/admin/inventory/materials'],
                            ['name' => 'Transfers', 'path' => '/admin/stock/transfers'],
                            ['name' => 'Deliveries & POD', 'path' => '/admin/deliveries/pod'],
                            ['name' => 'Vehicle loads', 'path' => '/admin/vehicle-load'],
                            ['name' => 'Packing slips', 'path' => '/admin/deliveries/packing-slips'],
                            ['name' => 'Picking lists', 'path' => '/admin/orders-picking'],
                            ['name' => 'Inventory adjustments', 'path' => '/admin/stock/audit'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Sales',
                'items' => [
                    [
                        'name' => 'Sales',
                        'icon' => 'sales',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Sales orders', 'path' => '/admin/orders'],
                            ['name' => 'Returns', 'path' => '/admin/returns/customer'],
                            ['name' => 'Customer gifts', 'path' => '/admin/gifts'],
                            ['name' => 'Marketing campaigns', 'path' => '/admin/campaigns'],
                            ['name' => 'Commission report', 'path' => '/admin/commissions'],
                            ['name' => 'Commission settlements', 'path' => '/admin/settlements'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Accounting',
                'items' => [
                    [
                        'name' => 'Accounting',
                        'icon' => 'accounting',
                        'path' => '#',
                        'subItems' => [
                            ['name' => 'Customer invoices', 'path' => '/admin/finance'],
                            ['name' => 'Expenses', 'path' => '/admin/expenses'],
                            ['name' => 'Payroll', 'path' => '/admin/reports/payroll'],
                            ['name' => 'Salary distributions', 'path' => '/admin/salary-distributions'],
                            ['name' => 'Chart of accounts', 'path' => '/admin/accounts'],
                            ['name' => 'Bank reconciliation', 'path' => '/admin/finance/reconciliation'],
                            ['name' => 'Agent performance', 'path' => '/admin/reports/agents'],
                            ['name' => 'Tax report', 'path' => '/admin/reports/vat'],
                            ['name' => 'Profit & Loss', 'path' => '/admin/reports/pl'],
                            ['name' => 'Balance sheet', 'path' => '/admin/reports/bs'],
                            ['name' => 'Cashflow', 'path' => '/admin/reports/cashflow'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function getIconSvg(string $key): string
    {
        // Simple icon set, reused across the sidebar.
        $icons = [
            'dashboard' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="3" y="3" width="8" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="3" width="8" height="5" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="10" width="8" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <rect x="3" y="13" width="8" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG,
            'products' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 9L12 4L20 9V15L12 20L4 15V9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M4 9L12 14L20 9" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
</svg>
SVG,
            'agents' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="7.5" r="3.5" stroke="currentColor" stroke-width="1.5"/>
  <path d="M5 19C6.4 16.5 8.9 15 12 15C15.1 15 17.6 16.5 19 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'suppliers' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="3" y="7" width="11" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
  <path d="M14 10H18L21 13V17H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG,
            'warehouses' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 10L12 4L20 10V20H4V10Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M9 20V13H15V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'employees' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="8" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
  <circle cx="16" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
  <path d="M3 19C3.8 16.8 5.6 15.5 8 15.5C10.4 15.5 12.2 16.8 13 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M11 19C11.8 16.8 13.6 15.5 16 15.5C18.4 15.5 20.2 16.8 21 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'settings' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
  <path d="M4 12H2M22 12H20M12 4V2M12 22V20M6.2 6.2L4.8 4.8M19.2 19.2L17.8 17.8M17.8 6.2L19.2 4.8M4.8 19.2L6.2 17.8"
        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'manufacturing' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 15L10 9L14 13L20 7V19H4V15Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
</svg>
SVG,
            'inventory' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="4" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
  <rect x="13" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG,
            'sales' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M5 7H19L17.5 18H6.5L5 7Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
  <path d="M9 7L10 4H14L15 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'accounting' => <<<'SVG'
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M8 7H15C16.657 7 18 8.343 18 10C18 11.657 16.657 13 15 13H9C7.343 13 6 14.343 6 16C6 17.657 7.343 19 9 19H16"
        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
  <path d="M12 5V7M12 19V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>
SVG,
            'default' => <<<'SVG'
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG,
        ];

        return $icons[$key] ?? $icons['default'];
    }
}
