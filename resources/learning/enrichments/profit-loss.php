<?php

return [
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => 'Profit on the P&L is **not** cash in minus cash out. It is built from **posted ledger accounts**: Net sales − COGS = gross profit, then minus commission, expenses, and payroll = net profit.',
                'bn' => 'P&L-এ লাভ **নগদ ইন−আউট** নয়। **পোস্ট করা লেজার** থেকে: নেট বিক্রয় − COGS = মোট লাভ, তারপর কমিশন, খরচ, বেতন বিয়োগ = নিট লাভ।',
            ],
        ],
        [
            'type' => 'compare',
            'title' => ['en' => '1 carton SAF-1L-BTL — money breakdown', 'bn' => '১ কার্টন — টাকার বিভাজন'],
            'columns' => [
                ['header' => ['en' => 'Line', 'bn' => 'লাইন'], 'rows' => [
                    ['en' => 'Cost to make (BOM)', 'bn' => 'তৈরি খরচ'],
                    ['en' => 'Net selling price', 'bn' => 'নেট বিক্রয় মূল্য'],
                    ['en' => 'VAT 15%', 'bn' => 'VAT ১৫%'],
                    ['en' => 'COGS on invoice', 'bn' => 'ইনভয়েসে COGS'],
                    ['en' => 'Gross profit', 'bn' => 'মোট লাভ'],
                ]],
                ['header' => ['en' => 'BDT', 'bn' => 'টাকা'], 'rows' => [
                    ['en' => '৳42', 'bn' => '৳৪২'],
                    ['en' => '৳80', 'bn' => '৳৮০'],
                    ['en' => '৳12 (not revenue)', 'bn' => '৳১২ (আয় নয়)'],
                    ['en' => '৳42', 'bn' => '৳৪২'],
                    ['en' => '৳38', 'bn' => '৳৩৮'],
                ]],
            ],
        ],
    ],
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'GRN materials', 'bn' => 'GRN কাঁচামাল'], 'desc' => ['en' => 'Cost in stock (BS)', 'bn' => 'স্টকে খরচ (BS)'], 'phase' => 'operations'],
            ['label' => ['en' => 'Production', 'bn' => 'উৎপাদন'], 'desc' => ['en' => 'FG at unit cost', 'bn' => 'FG ইউনিট খরচে'], 'phase' => 'operations', 'path' => '/admin/production'],
            ['label' => ['en' => 'Deliver', 'bn' => 'ডেলিভারি'], 'desc' => ['en' => 'Stock out, no revenue yet', 'bn' => 'স্টক বের, আয় এখনো নয়'], 'phase' => 'operations', 'path' => '/admin/deliveries/pod'],
            ['label' => ['en' => 'Invoice', 'bn' => 'ইনভয়েস'], 'desc' => ['en' => 'Revenue + COGS hit P&L', 'bn' => 'আয় + COGS → P&L'], 'phase' => 'finance', 'path' => '/admin/finance'],
            ['label' => ['en' => 'Gross profit', 'bn' => 'মোট লাভ'], 'desc' => ['en' => 'Net sales − COGS (e.g. ৳38/unit)', 'bn' => 'নেট বিক্রয় − COGS (যেমন ৳৩৮)'], 'phase' => 'finance'],
            ['label' => ['en' => 'Net profit', 'bn' => 'নিট লাভ'], 'desc' => ['en' => 'After commission & overhead', 'bn' => 'কমিশন ও ওভারহেড পর'], 'phase' => 'finance', 'path' => '/admin/reports/pl'],
        ],
    ],
    'step-0' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Production — unit cost', 'bn' => 'উৎপাদন — ইউনিট খরচ'],
            'menu' => ['en' => 'Manufacturing → Production runs', 'bn' => 'Manufacturing → Production runs'],
            'path' => '/admin/production',
            'highlights' => [
                ['label' => ['en' => 'material_unit_cost', 'bn' => 'material_unit_cost'], 'desc' => ['en' => 'Cost per FG unit after BOM consumption — used for COGS if no GL journal.', 'bn' => 'BOM পর FG প্রতি খরচ — GL না থাকলে COGS অনুমান।']],
            ],
        ],
        [
            'type' => 'callout',
            'variant' => 'info',
            'title' => ['en' => 'No P&L yet', 'bn' => 'এখনো P&L নয়'],
            'body' => [
                'en' => 'Production and GRN only move **balance sheet** stock accounts. Profit appears when you **invoice** delivered goods.',
                'bn' => 'উৎপাদন ও GRN শুধু **ব্যালেন্স শিট** স্টক। **ইনভয়েসে** লাভ দেখা যায়।',
            ],
        ],
    ],
    'step-2' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Sales invoice journal', 'bn' => 'বিক্রয় ইনভয়েস জার্নাল'],
            'menu' => ['en' => 'Accounting → Customer invoices', 'bn' => 'Accounting → Customer invoices'],
            'path' => '/admin/finance',
            'table' => [
                ['col' => 'Account', 'sample' => 'Dr AR ৳92'],
                ['col' => 'Credit', 'sample' => 'Cr Revenue ৳80'],
                ['col' => 'Credit', 'sample' => 'Cr VAT ৳12'],
                ['col' => 'COGS', 'sample' => 'Dr COGS ৳42 / Cr FG ৳42'],
            ],
        ],
        [
            'type' => 'clicks',
            'title' => ['en' => 'Invoice a delivered order', 'bn' => 'ডেলিভার্ড অর্ডার ইনভয়েস'],
            'items' => [
                ['en' => 'Open **Accounting → Customer invoices**.', 'bn' => '**Accounting → Customer invoices** খুলুন।'],
                ['en' => '**Create from order** — pick a **Delivered** sales order.', 'bn' => '**Create from order** — **Delivered** অর্ডার বেছে নিন।'],
                ['en' => 'Review net amount, VAT, and total AR. **Post invoice**.', 'bn' => 'নেট, VAT, AR মোট দেখে **Post invoice**।'],
                ['en' => 'Check **Journal entries** — Sales Revenue + VAT Payable + COGS should appear.', 'bn' => '**Journal entries** — Revenue + VAT + COGS দেখুন।'],
            ],
        ],
    ],
    'step-3' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Profit & loss report', 'bn' => 'লাভ-ক্ষতি রিপোর্ট'],
            'menu' => ['en' => 'Reports → Profit & loss', 'bn' => 'Reports → Profit & loss'],
            'path' => '/admin/reports/pl',
            'highlights' => [
                ['label' => ['en' => 'Net sales', 'bn' => 'নেট বিক্রয়'], 'desc' => ['en' => 'Revenue account credits in period', 'bn' => 'সময়কালের Revenue ক্রেডিট']],
                ['label' => ['en' => 'COGS source', 'bn' => 'COGS উৎস'], 'desc' => ['en' => 'Shows GL vs estimated', 'bn' => 'GL বনাম অনুমান']],
                ['label' => ['en' => 'Net profit', 'bn' => 'নিট লাভ'], 'desc' => ['en' => 'After all operating costs', 'bn' => 'সব খরচের পর']],
            ],
        ],
    ],
];
