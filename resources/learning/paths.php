<?php

/**
 * Role-based recommended course order (module slugs).
 */
return [
    'all' => ['overview', 'troubleshooting', 'products', 'procurement', 'manufacturing', 'sales', 'delivery', 'logistics', 'accounting', 'ledger-mapping', 'reports'],
    'admin' => ['overview', 'troubleshooting', 'products', 'agents', 'procurement', 'warehouses', 'logistics', 'manufacturing', 'inventory', 'sales', 'delivery', 'accounting', 'ledger-mapping', 'profit-loss', 'hr-payroll', 'reports'],
    'purchase_executive' => ['overview', 'troubleshooting', 'products', 'procurement', 'inventory'],
    'warehouse_officer' => ['overview', 'troubleshooting', 'warehouses', 'inventory', 'procurement', 'logistics', 'delivery'],
    'production_officer' => ['overview', 'troubleshooting', 'products', 'manufacturing', 'inventory'],
    'sales_officer' => ['overview', 'troubleshooting', 'agents', 'sales', 'delivery'],
    'delivery_coordinator' => ['overview', 'troubleshooting', 'warehouses', 'logistics', 'sales', 'delivery'],
    'accounts_officer' => ['overview', 'troubleshooting', 'accounting', 'logistics', 'ledger-mapping', 'profit-loss', 'hr-payroll', 'reports'],
    'qc_officer' => ['overview', 'troubleshooting', 'manufacturing', 'products'],
];
