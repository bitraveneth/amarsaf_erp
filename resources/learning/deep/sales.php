<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Picking list', 'bn' => 'পিকিং লিস্ট'], 'body' => ['en' => 'Warehouse picks FG against confirmed orders before dispatch.', 'bn' => 'ডিসপ্যাচের আগে নিশ্চিত অর্ডারের বিপরীতে FG পিক।'], 'path' => '/admin/orders-picking'],
        ['title' => ['en' => 'Credit check', 'bn' => 'ক্রেডিট চেক'], 'body' => ['en' => 'System checks agent credit limit and outstanding before confirm.', 'bn' => 'নিশ্চিতের আগে এজেন্ট ক্রেডিট লিমিট ও বকেয়া চেক।'], 'path' => '/admin/agents'],
        ['title' => ['en' => 'Customer returns & credit notes', 'bn' => 'ফেরত ও ক্রেডিট নোট'], 'body' => ['en' => 'Returns → credit note posts Dr Sales returns, Cr AR. See Transaction → ledger map course.', 'bn' => 'Returns → credit note Dr Sales returns, Cr AR। ledger map কোর্স দেখুন।'], 'path' => '/admin/returns/customer'],
        ['title' => ['en' => 'Commissions & settlements', 'bn' => 'কমিশন ও সেটেলমেন্ট'], 'body' => ['en' => 'Settlement accrual Dr Commission expense, Cr Commission payable; payment Cr Bank.', 'bn' => 'Settlement accrual → payment।'], 'path' => '/admin/settlements'],
    ],
    'examples_extra' => [
        ['title' => ['en' => 'Order lifecycle', 'bn' => 'অর্ডার লাইফসাইকেল'], 'body' => ['en' => 'Draft → Confirmed (reserves stock) → Picking → Delivered (via POD) → Invoiced → Paid.', 'bn' => 'Draft → Confirmed (স্টক রিজার্ভ) → Picking → Delivered → Invoiced → Paid।']],
    ],
    'faqs' => [
        ['q' => ['en' => 'Why cannot confirm order?', 'bn' => 'অর্ডার নিশ্চিত হচ্ছে না কেন?'], 'a' => ['en' => 'Usually insufficient FG stock, inactive agent, or over credit limit.', 'bn' => 'সাধারণত FG স্টক কম, নিষ্ক্রিয় এজেন্ট, বা ক্রেডিট লিমিট অতিক্রম।']],
    ],
];
