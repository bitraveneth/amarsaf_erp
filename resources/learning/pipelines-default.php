<?php

/**
 * Default CSS pipeline steps when no custom flow_pipeline in enrichments.
 */
return [
    'agents' => [
        ['label' => ['en' => 'Create agent', 'bn' => 'এজেন্ট তৈরি'], 'desc' => ['en' => 'Name, zone, credit', 'bn' => 'নাম, জোন, ক্রেডিট'], 'phase' => 'commercial'],
        ['label' => ['en' => 'Set price list', 'bn' => 'প্রাইস লিস্ট'], 'desc' => ['en' => 'Special prices if needed', 'bn' => 'আলাদা মূল্য'], 'phase' => 'commercial'],
        ['label' => ['en' => 'Sales order', 'bn' => 'বিক্রয় অর্ডার'], 'desc' => ['en' => 'Agent places order', 'bn' => 'এজেন্ট অর্ডার'], 'phase' => 'commercial'],
    ],
    'warehouses' => [
        ['label' => ['en' => 'Warehouse', 'bn' => 'গুদাম'], 'desc' => ['en' => 'Factory or depot', 'bn' => 'Factory বা depot'], 'phase' => 'operations'],
        ['label' => ['en' => 'Locations', 'bn' => 'লোকেশন'], 'desc' => ['en' => 'Bins and zones', 'bn' => 'বিন ও জোন'], 'phase' => 'operations'],
        ['label' => ['en' => 'Routes & vehicles', 'bn' => 'রুট ও যান'], 'desc' => ['en' => 'Dispatch planning', 'bn' => 'ডিসপ্যাচ'], 'phase' => 'operations'],
    ],
    'inventory' => [
        ['label' => ['en' => 'Stock in', 'bn' => 'স্টক ইন'], 'desc' => ['en' => 'GRN or production', 'bn' => 'GRN বা উৎপাদন'], 'phase' => 'operations'],
        ['label' => ['en' => 'Monitor levels', 'bn' => 'লেভেল দেখুন'], 'desc' => ['en' => 'Dashboard & low stock', 'bn' => 'ড্যাশবোর্ড'], 'phase' => 'operations'],
        ['label' => ['en' => 'Transfer / adjust', 'bn' => 'স্থানান্তর'], 'desc' => ['en' => 'Between warehouses', 'bn' => 'গুদাম间'], 'phase' => 'operations'],
    ],
    'sales' => [
        ['label' => ['en' => 'Draft order', 'bn' => 'ড্রাফট'], 'desc' => ['en' => 'Agent + products', 'bn' => 'এজেন্ট + পণ্য'], 'phase' => 'commercial'],
        ['label' => ['en' => 'Confirm', 'bn' => 'নিশ্চিত'], 'desc' => ['en' => 'Reserves FG stock', 'bn' => 'FG রিজার্ভ'], 'phase' => 'commercial'],
        ['label' => ['en' => 'Pick & deliver', 'bn' => 'পিক ও ডেলিভার'], 'desc' => ['en' => 'Warehouse dispatch', 'bn' => 'গুদাম পাঠায়'], 'phase' => 'operations'],
    ],
    'delivery' => [
        ['label' => ['en' => 'Pick list', 'bn' => 'পিক লিস্ট'], 'desc' => ['en' => 'From confirmed order', 'bn' => 'নিশ্চিত অর্ডার'], 'phase' => 'operations'],
        ['label' => ['en' => 'Dispatch', 'bn' => 'ডিসপ্যাচ'], 'desc' => ['en' => 'Route + vehicle', 'bn' => 'রুট + যান'], 'phase' => 'operations'],
        ['label' => ['en' => 'POD', 'bn' => 'POD'], 'desc' => ['en' => 'Proof of delivery', 'bn' => 'ডেলিভারি প্রমাণ'], 'phase' => 'operations'],
        ['label' => ['en' => 'Delivered', 'bn' => 'Delivered'], 'desc' => ['en' => 'Ready to invoice', 'bn' => 'ইনভয়েস প্রস্তুত'], 'phase' => 'commercial'],
    ],
    'accounting' => [
        ['label' => ['en' => 'Delivered order', 'bn' => 'ডেলিভার্ড'], 'desc' => ['en' => 'POD complete', 'bn' => 'POD সম্পন্ন'], 'phase' => 'finance'],
        ['label' => ['en' => 'Invoice', 'bn' => 'ইনভয়েস'], 'desc' => ['en' => 'Revenue + VAT + COGS', 'bn' => 'আয় + VAT + COGS'], 'phase' => 'finance'],
        ['label' => ['en' => 'Receipt', 'bn' => 'রসিদ'], 'desc' => ['en' => 'Bank clears AR', 'bn' => 'AR ক্লিয়ার'], 'phase' => 'finance'],
        ['label' => ['en' => 'Reports', 'bn' => 'রিপোর্ট'], 'desc' => ['en' => 'VAT, P&L, aging', 'bn' => 'VAT, P&L'], 'phase' => 'finance'],
    ],
    'reports' => [
        ['label' => ['en' => 'Daily posting', 'bn' => 'দৈনিক পোস্ট'], 'desc' => ['en' => 'Invoices, GRN, payroll', 'bn' => 'ইনভয়েস, GRN'], 'phase' => 'finance'],
        ['label' => ['en' => 'Reports dashboard', 'bn' => 'রিপোর্ট'], 'desc' => ['en' => 'Pick standard reports', 'bn' => 'স্ট্যান্ডার্ড রিপোর্ট'], 'phase' => 'finance'],
        ['label' => ['en' => 'Month-end pack', 'bn' => 'মাস শেষ'], 'desc' => ['en' => 'Stock + AR + P&L', 'bn' => 'স্টক + AR + P&L'], 'phase' => 'finance'],
    ],
];
