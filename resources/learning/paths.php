<?php

/**
 * Role-based recommended course order (module slugs).
 */
return [
    'all' => ['overview', 'products', 'procurement', 'manufacturing', 'sales', 'delivery', 'accounting', 'reports'],
    'admin' => ['overview', 'products', 'agents', 'procurement', 'warehouses', 'manufacturing', 'inventory', 'sales', 'delivery', 'accounting', 'profit-loss', 'reports'],
    'purchase_executive' => ['overview', 'products', 'procurement', 'inventory'],
    'warehouse_officer' => ['overview', 'warehouses', 'inventory', 'procurement', 'delivery'],
    'production_officer' => ['overview', 'products', 'manufacturing', 'inventory'],
    'sales_officer' => ['overview', 'agents', 'sales', 'delivery'],
    'delivery_coordinator' => ['overview', 'warehouses', 'sales', 'delivery'],
    'accounts_officer' => ['overview', 'accounting', 'profit-loss', 'reports'],
    'qc_officer' => ['overview', 'manufacturing', 'products'],
];
