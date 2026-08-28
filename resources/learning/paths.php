<?php

/**
 * Role-based recommended course order (module slugs).
 */
return [
    'all' => ['overview', 'products', 'procurement', 'manufacturing', 'sales', 'delivery', 'logistics', 'accounting', 'ledger-mapping', 'reports'],
    'admin' => ['overview', 'products', 'agents', 'procurement', 'warehouses', 'logistics', 'manufacturing', 'inventory', 'sales', 'delivery', 'accounting', 'ledger-mapping', 'profit-loss', 'hr-payroll', 'reports'],
    'purchase_executive' => ['overview', 'products', 'procurement', 'inventory'],
    'warehouse_officer' => ['overview', 'warehouses', 'inventory', 'procurement', 'logistics', 'delivery'],
    'production_officer' => ['overview', 'products', 'manufacturing', 'inventory'],
    'sales_officer' => ['overview', 'agents', 'sales', 'delivery'],
    'delivery_coordinator' => ['overview', 'warehouses', 'logistics', 'sales', 'delivery'],
    'accounts_officer' => ['overview', 'accounting', 'logistics', 'ledger-mapping', 'profit-loss', 'hr-payroll', 'reports'],
    'qc_officer' => ['overview', 'manufacturing', 'products'],
];
