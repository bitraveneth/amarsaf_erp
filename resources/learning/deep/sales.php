<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Picking list', 'bn' => 'পিকিং লিস্ট'], 'body' => ['en' => 'Warehouse picks FG against confirmed orders before dispatch.', 'bn' => 'ডিসপ্যাচের আগে নিশ্চিত অর্ডারের বিপরীতে FG পিক।'], 'path' => '/admin/orders-picking'],
        ['title' => ['en' => 'Credit check', 'bn' => 'ক্রেডিট চেক'], 'body' => ['en' => 'System checks agent credit limit and outstanding before confirm.', 'bn' => 'নিশ্চিতের আগে এজেন্ট ক্রেডিট লিমিট ও বকেয়া চেক।'], 'path' => '/admin/agents'],
        ['title' => ['en' => 'Customer returns & credit notes', 'bn' => 'ফেরত ও ক্রেডিট নোট'], 'body' => ['en' => 'Returns → credit note posts Dr Sales returns, Cr AR. See Transaction → ledger map course.', 'bn' => 'Returns → credit note Dr Sales returns, Cr AR। ledger map কোর্স দেখুন।'], 'path' => '/admin/returns/customer'],
        ['title' => ['en' => 'Commissions & settlements', 'bn' => 'কমিশন ও সেটেলমেন্ট'], 'body' => ['en' => 'Settlement accrual Dr Commission expense, Cr Commission payable; payment Cr Bank.', 'bn' => 'Settlement accrual → payment।'], 'path' => '/admin/settlements'],
        ['title' => ['en' => 'Sales targets', 'bn' => 'বিক্রয় টার্গেট'], 'body' => ['en' => 'Set qty or value targets per agent and period. Compare confirmed/invoiced sales on the sales dashboard.', 'bn' => 'এজেন্ট ও পিরিয়ড অনুযায়ী পরিমাণ/মূল্য টার্গেট। নিশ্চিত/ইনভয়েসড বিক্রয় তুলনা করুন।'], 'path' => '/admin/sales-targets'],
        ['title' => ['en' => 'Customer gifts', 'bn' => 'গ্রাহক গিফট'], 'body' => ['en' => 'Track promotional free goods given with orders — not billed like normal sales.', 'bn' => 'অর্ডারের সাথে প্রমোশনাল ফ্রি পণ্য — সাধারণ বিক্রয় নয়।'], 'path' => '/admin/gifts'],
        ['title' => ['en' => 'Marketing campaigns', 'bn' => 'মার্কেটিং ক্যাম্পেইন'], 'body' => ['en' => 'Name, dates, and linked offers. Use with gifts when a promotion is time-boxed.', 'bn' => 'নাম, তারিখ ও যুক্ত অফার। সময়সীমা অফারে গিফটের সাথে ব্যবহার।'], 'path' => '/admin/campaigns'],
    ],
    'examples_extra' => [
        ['title' => ['en' => 'Order lifecycle', 'bn' => 'অর্ডার লাইফসাইকেল'], 'body' => ['en' => 'Draft → Confirmed (reserves stock) → Picking → Delivered (via POD) → Invoiced → Paid.', 'bn' => 'Draft → Confirmed (স্টক রিজার্ভ) → Picking → Delivered → Invoiced → Paid।']],
    ],
    'faqs' => [
        ['q' => ['en' => 'Why cannot confirm order?', 'bn' => 'অর্ডার নিশ্চিত হচ্ছে না কেন?'], 'a' => ['en' => 'Usually insufficient FG stock, inactive agent, or over credit limit.', 'bn' => 'সাধারণত FG স্টক কম, নিষ্ক্রিয় এজেন্ট, বা ক্রেডিট লিমিট অতিক্রম।']],
    ],
];
