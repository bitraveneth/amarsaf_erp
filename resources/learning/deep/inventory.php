<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Low stock alert', 'bn' => 'লো স্টক'], 'body' => ['en' => 'Review low stock and MRP suggestions before creating POs.', 'bn' => 'PO তৈরির আগে লো স্টক ও MRP দেখুন।'], 'path' => '/admin/inventory/low-stock'],
        ['title' => ['en' => 'Stock movements', 'bn' => 'স্টক মুভমেন্ট'], 'body' => ['en' => 'Audit trail of adjustments, write-offs, and corrections.', 'bn' => 'সমন্বয়, রাইট-অফ ও সংশোধনের অডিট ট্রেইল।'], 'path' => '/admin/stock/movements'],
    ],
    'examples_extra' => [
        ['title' => ['en' => 'Transfer example', 'bn' => 'স্থানান্তর উদাহরণ'], 'body' => ['en' => 'Move 500 cartons from Factory FG to Dhaka Depot so sales orders for that zone can confirm.', 'bn' => '৫০০ কার্টন Factory FG থেকে Dhaka Depot — সেই জোনের বিক্রয় অর্ডার নিশ্চিত করতে।']],
    ],
    'report_links' => [
        ['title' => ['en' => 'Inventory valuation', 'bn' => 'মূল্যায়ন'], 'path' => '/admin/reports/inventory-valuation', 'desc' => ['en' => 'Stock value by warehouse', 'bn' => 'গুদাম অনুযায়ী মূল্য']],
    ],
];
