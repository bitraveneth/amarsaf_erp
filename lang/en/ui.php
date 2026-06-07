<?php

/**
 * Saf ERP — standard user-facing labels (British English, sentence case).
 * Use __('ui.key') in Blade and PHP for menus, KPIs, and shared terms.
 */
return [
    // Abbreviations (buttons / table columns after first mention on page)
    'abbr' => [
        'po' => 'PO',
        'grn' => 'GRN',
        'bom' => 'BOM',
        'sku' => 'SKU',
        'pod' => 'POD',
        'cogs' => 'COGS',
        'vat' => 'VAT',
        'mrp' => 'MRP',
        'qc' => 'QC',
    ],

    // Domain terms
    'terms' => [
        'agent' => 'Agent',
        'agents' => 'Agents',
        'purchase_order' => 'Purchase order',
        'purchase_orders' => 'Purchase orders',
        'sales_order' => 'Sales order',
        'sales_orders' => 'Sales orders',
        'goods_receipt' => 'Goods receipt note',
        'goods_receipts' => 'Goods receipts (GRN)',
        'net_sales' => 'Net sales',
        'gross_profit' => 'Gross profit',
        'net_profit' => 'Net profit',
        'outstanding' => 'Outstanding',
        'collections' => 'Collections',
        'export_center' => 'Export center',
        'cash_flow' => 'Cash flow',
        'profit_and_loss' => 'Profit & loss',
        'vat_report' => 'VAT report',
        'commission_report' => 'Commission report',
        'chart_of_accounts' => 'Chart of accounts',
        'general_ledger' => 'General ledger',
        'trial_balance' => 'Trial balance',
        'balance_sheet' => 'Balance sheet',
        'bank_reconciliation' => 'Bank reconciliation',
        'stock_movements' => 'Stock movements',
        'inventory_dashboard' => 'Inventory dashboard',
    ],

    // Module dashboards (pattern: "{Domain} dashboard")
    'dashboards' => [
        'home' => 'Dashboard',
        'sales' => 'Sales dashboard',
        'accounting' => 'Accounting dashboard',
        'manufacturing' => 'Manufacturing dashboard',
        'reports' => 'Reports dashboard',
        'inventory' => 'Inventory dashboard',
    ],

    // Buttons
    'actions' => [
        'new_po' => 'New PO',
        'save_po' => 'Save PO',
        'create_po' => 'Create purchase order',
        'new_order' => 'New sales order',
        'select_po' => 'Select purchase order',
    ],
];
