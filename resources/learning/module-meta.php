<?php

/**
 * Per-module metadata: ERP user roles (excluding super_admin), related screens.
 */
return [
    'overview' => [
        'roles' => ['all', 'admin', 'purchase_executive', 'warehouse_officer', 'production_officer', 'sales_officer', 'delivery_coordinator', 'accounts_officer', 'qc_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Dashboard', 'bn' => 'ড্যাশবোর্ড'], 'path' => '/admin'],
            ['title' => ['en' => 'Products', 'bn' => 'পণ্য'], 'path' => '/admin/products'],
            ['title' => ['en' => 'Purchase orders', 'bn' => 'ক্রয় অর্ডার'], 'path' => '/admin/purchase-orders'],
            ['title' => ['en' => 'Sales orders', 'bn' => 'বিক্রয় অর্ডার'], 'path' => '/admin/orders'],
            ['title' => ['en' => 'Customer invoices', 'bn' => 'গ্রাহক ইনভয়েস'], 'path' => '/admin/finance'],
        ],
    ],
    'products' => [
        'roles' => ['all', 'admin', 'purchase_executive', 'warehouse_officer', 'production_officer', 'sales_officer', 'qc_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Products', 'bn' => 'পণ্য'], 'path' => '/admin/products'],
            ['title' => ['en' => 'Materials', 'bn' => 'কাঁচামাল'], 'path' => '/admin/materials'],
            ['title' => ['en' => 'Tax classes', 'bn' => 'ট্যাক্স ক্লাস'], 'path' => '/admin/tax-classes'],
            ['title' => ['en' => 'Price lists', 'bn' => 'প্রাইস লিস্ট'], 'path' => '/admin/products-price-list'],
        ],
    ],
    'agents' => [
        'roles' => ['admin', 'sales_officer', 'accounts_officer', 'delivery_coordinator'],
        'related_screens' => [
            ['title' => ['en' => 'Agents', 'bn' => 'এজেন্ট'], 'path' => '/admin/agents'],
            ['title' => ['en' => 'Commission rules', 'bn' => 'কমিশন নিয়ম'], 'path' => '/admin/commission-rules'],
            ['title' => ['en' => 'Price lists', 'bn' => 'প্রাইস লিস্ট'], 'path' => '/admin/products-price-list'],
        ],
    ],
    'procurement' => [
        'roles' => ['admin', 'purchase_executive', 'warehouse_officer', 'accounts_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Suppliers', 'bn' => 'সাপ্লায়ার'], 'path' => '/admin/suppliers'],
            ['title' => ['en' => 'Purchase orders', 'bn' => 'ক্রয় অর্ডার'], 'path' => '/admin/purchase-orders'],
            ['title' => ['en' => 'GRN inbox', 'bn' => 'GRN ইনবক্স'], 'path' => '/admin/goods-receipts'],
            ['title' => ['en' => 'Purchase bills', 'bn' => 'ক্রয় বিল'], 'path' => '/admin/bills'],
        ],
    ],
    'warehouses' => [
        'roles' => ['admin', 'warehouse_officer', 'delivery_coordinator', 'sales_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Warehouses', 'bn' => 'গুদাম'], 'path' => '/admin/warehouses'],
            ['title' => ['en' => 'Locations', 'bn' => 'লোকেশন'], 'path' => '/admin/warehouse-locations'],
            ['title' => ['en' => 'Vehicles', 'bn' => 'যান'], 'path' => '/admin/vehicles'],
            ['title' => ['en' => 'Delivery routes', 'bn' => 'ডেলিভারি রুট'], 'path' => '/admin/delivery-routes'],
        ],
    ],
    'manufacturing' => [
        'roles' => ['admin', 'production_officer', 'qc_officer', 'warehouse_officer'],
        'related_screens' => [
            ['title' => ['en' => 'BOMs', 'bn' => 'BOM'], 'path' => '/admin/boms'],
            ['title' => ['en' => 'Production runs', 'bn' => 'উৎপাদন রান'], 'path' => '/admin/production'],
            ['title' => ['en' => 'Pending receipts', 'bn' => 'Pending receipts'], 'path' => '/admin/production/pending-receipts'],
            ['title' => ['en' => 'Batches', 'bn' => 'ব্যাচ'], 'path' => '/admin/batches'],
        ],
    ],
    'inventory' => [
        'roles' => ['admin', 'warehouse_officer', 'purchase_executive', 'accounts_officer', 'production_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Inventory dashboard', 'bn' => 'ইনভেন্টরি'], 'path' => '/admin/inventory'],
            ['title' => ['en' => 'Low stock', 'bn' => 'লো স্টক'], 'path' => '/admin/inventory/low-stock'],
            ['title' => ['en' => 'Transfers', 'bn' => 'স্থানান্তর'], 'path' => '/admin/stock/transfers'],
            ['title' => ['en' => 'MRP', 'bn' => 'MRP'], 'path' => '/admin/mrp'],
        ],
    ],
    'sales' => [
        'roles' => ['admin', 'sales_officer', 'accounts_officer', 'delivery_coordinator'],
        'related_screens' => [
            ['title' => ['en' => 'Sales orders', 'bn' => 'বিক্রয় অর্ডার'], 'path' => '/admin/orders'],
            ['title' => ['en' => 'Picking lists', 'bn' => 'পিকিং লিস্ট'], 'path' => '/admin/orders-picking'],
            ['title' => ['en' => 'Sales dashboard', 'bn' => 'বিক্রয় ড্যাশবোর্ড'], 'path' => '/admin/sales-dashboard'],
            ['title' => ['en' => 'Returns', 'bn' => 'ফেরত'], 'path' => '/admin/returns/customer'],
        ],
    ],
    'delivery' => [
        'roles' => ['admin', 'delivery_coordinator', 'warehouse_officer', 'sales_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Deliveries', 'bn' => 'ডেলিভারি'], 'path' => '/admin/deliveries/pod'],
            ['title' => ['en' => 'POD', 'bn' => 'POD'], 'path' => '/admin/deliveries/pod'],
            ['title' => ['en' => 'Vehicle loads', 'bn' => 'যান লোড'], 'path' => '/admin/vehicle-load'],
            ['title' => ['en' => 'Packing slips', 'bn' => 'প্যাকিং স্লিপ'], 'path' => '/admin/deliveries/packing-slips'],
        ],
    ],
    'profit-loss' => [
        'roles' => ['all', 'admin', 'accounts_officer', 'sales_officer', 'production_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Profit & loss', 'bn' => 'লাভ ও ক্ষতি'], 'path' => '/admin/reports/pl'],
            ['title' => ['en' => 'Production runs', 'bn' => 'উৎপাদন'], 'path' => '/admin/production'],
            ['title' => ['en' => 'Customer invoices', 'bn' => 'ইনভয়েস'], 'path' => '/admin/finance'],
            ['title' => ['en' => 'Journal entries', 'bn' => 'জার্নাল'], 'path' => '/admin/journals'],
        ],
    ],
    'accounting' => [
        'roles' => ['admin', 'accounts_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Customer invoices', 'bn' => 'গ্রাহক ইনভয়েস'], 'path' => '/admin/finance'],
            ['title' => ['en' => 'Journals', 'bn' => 'জার্নাল'], 'path' => '/admin/journals'],
            ['title' => ['en' => 'Chart of accounts', 'bn' => 'চার্ট অফ অ্যাকাউন্টস'], 'path' => '/admin/accounts'],
            ['title' => ['en' => 'Accounting periods', 'bn' => 'অ্যাকাউন্টিং পিরিয়ড'], 'path' => '/admin/accounting-periods'],
        ],
    ],
    'reports' => [
        'roles' => ['all', 'admin', 'accounts_officer'],
        'related_screens' => [
            ['title' => ['en' => 'Reports dashboard', 'bn' => 'রিপোর্ট'], 'path' => '/admin/reports-dashboard'],
            ['title' => ['en' => 'P and L', 'bn' => 'লাভ-ক্ষতি'], 'path' => '/admin/reports/pl'],
            ['title' => ['en' => 'VAT report', 'bn' => 'VAT'], 'path' => '/admin/reports/vat'],
            ['title' => ['en' => 'Export center', 'bn' => 'এক্সপোর্ট'], 'path' => '/admin/export-center'],
        ],
    ],
];
