<?php

/**
 * One-off normalisation of common UI strings (sentence case, standard terms).
 * Run: php scripts/normalise-ui-labels.php
 */

$root = dirname(__DIR__);
$dir = $root . '/resources/views/admin';

$replacements = [
    'Purchase Orders' => 'Purchase orders',
    'Create Purchase Order' => 'Create purchase order',
    'Purchase Order' => 'Purchase order',
    'Agent Master' => 'Agents',
    'Commission Summary' => 'Commission report',
    'VAT Summary' => 'VAT report',
    'Chart of Accounts' => 'Chart of accounts',
    'General Ledger' => 'General ledger',
    'Trial Balance' => 'Trial balance',
    'Balance Sheet' => 'Balance sheet',
    'Bank Reconciliation' => 'Bank reconciliation',
    'Webhook Endpoints' => 'Webhook endpoints',
    'Inventory Dashboard' => 'Inventory dashboard',
    'Stock Movements' => 'Stock movements',
    'Recent Stock Movements' => 'Recent stock movements',
    'Data export center' => 'Export center',
    'Net revenue' => 'Net sales',
    'Net Revenue' => 'Net sales',
    'Net Sales' => 'Net sales',
    'title="Dealers"' => 'title="Agents"',
    'Tax report' => 'VAT report',
    'No Orders Yet' => 'No orders yet',
    'No Orders yet' => 'No orders yet',
    'No Employees Yet' => 'No employees yet',
    'No Badges Defined' => 'No badges defined',
    'No Contracts' => 'No contracts',
    'No Allowances' => 'No allowances',
    'No Location Logs' => 'No location logs',
    'No Leave Requests' => 'No leave requests',
    'No Supplier Returns' => 'No supplier returns',
    'No customer returns yet' => 'No customer returns yet',
    'Cashflow' => 'Cash flow',
    'Net Cashflow' => 'Net cash flow',
    'About Cashflow' => 'About cash flow',
    'Cashflow Ratio' => 'Cash flow ratio',
    'Formal report' => 'Profit & loss',
    'Packing Slip' => 'Packing slip',
    'Picking List' => 'Picking list',
    'Proof of Delivery' => 'Proof of delivery',
    'Delivery Challan' => 'Delivery challan',
    'Goods Receipt' => 'Goods receipt note',
    'Production Order' => 'Production order',
    'Sales Order' => 'Sales order',
    'Tax Invoice' => 'Tax invoice',
    'Approve PO' => 'Approve PO',
    'Create GRN' => 'Create GRN',
    'Back to Finance' => 'Back to finance',
    'Back to Runs' => 'Back to runs',
    'Back to Deliveries' => 'Back to deliveries',
    'Quick Write-off' => 'Quick write-off',
    'Add Credit' => 'Add credit',
    'Record Payment' => 'Record payment',
    'No invoices yet' => 'No invoices yet',
    'No Invoices Yet' => 'No invoices yet',
    'No Deliveries Yet' => 'No deliveries yet',
    'No deliveries yet' => 'No deliveries yet',
    'No Products Yet' => 'No products yet',
    'No products yet' => 'No products yet',
    'No Suppliers Yet' => 'No suppliers yet',
    'No suppliers yet' => 'No suppliers yet',
    'Employee Master' => 'Employees',
    'Supplier Master' => 'Suppliers',
    'Product Master' => 'Products',
    'Warehouse Master' => 'Warehouses',
    'Vehicle Master' => 'Vehicles',
    'Route Master' => 'Delivery routes',
    'Batch Master' => 'Batches',
    'Profit and Loss' => 'Profit & loss',
    'Profit And Loss' => 'Profit & loss',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $contents = file_get_contents($path);
    $updated = str_replace(array_keys($replacements), array_values($replacements), $contents, $count);

    if ($count > 0) {
        file_put_contents($path, $updated);
        echo "Updated {$count} in " . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . PHP_EOL;
    }
}

// Dashboard components outside admin/
$extraFiles = [
    $root . '/resources/views/admin/sales/dashboard.blade.php',
    $root . '/resources/views/admin/reports/dashboard.blade.php',
    $root . '/resources/views/admin/orders/index.blade.php',
];

foreach ($extraFiles as $path) {
    if (! file_exists($path)) {
        continue;
    }
    $contents = file_get_contents($path);
    $updated = $contents;

    if (str_contains($path, 'orders/index.blade.php')) {
        $updated = str_replace('title="Orders"', 'title="Sales orders"', $updated);
        $updated = str_replace('Track agent orders', 'Track agent sales orders', $updated);
    }

    if ($updated !== $contents) {
        file_put_contents($path, $updated);
        echo 'Updated ' . basename($path) . PHP_EOL;
    }
}

echo "Done." . PHP_EOL;
