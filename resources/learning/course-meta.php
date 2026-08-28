<?php

/**
 * Course catalog metadata per module slug.
 */
return [
    'overview' => [
        'track' => ['en' => 'Foundation', 'bn' => 'ভিত্তি'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 15,
        'outcomes' => [
            ['en' => 'Map the full buy → make → sell → bill cycle', 'bn' => 'কিনুন → তৈরি → বিক্রি → বিল পুরো চক্র বুঝুন'],
            ['en' => 'Know which ERP screen comes next in each chain', 'bn' => 'প্রতিটি ধারায় পরের ERP স্ক্রিন চিনুন'],
            ['en' => 'Spot where stock, revenue, and cash actually change', 'bn' => 'স্টক, আয় ও নগদ কোথায় বদলায় তা চিনুন'],
        ],
    ],
    'products' => [
        'track' => ['en' => 'Foundation', 'bn' => 'ভিত্তি'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 10,
        'outcomes' => [
            ['en' => 'Separate materials from sellable products', 'bn' => 'কাঁচামাল ও বিক্রয়যোগ্য পণ্য আলাদা করুন'],
            ['en' => 'Set SKUs, tax class, packaging, and material categories', 'bn' => 'SKU, ট্যাক্স ক্লাস, প্যাকেজিং ও ম্যাটেরিয়াল ক্যাটাগরি সেট করুন'],
        ],
    ],
    'agents' => [
        'track' => ['en' => 'Commercial', 'bn' => 'বাণিজ্য'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 10,
        'outcomes' => [
            ['en' => 'Register agents with zones and credit limits', 'bn' => 'জোন ও ক্রেডিট লিমিটসহ এজেন্ট নিবন্ধন'],
            ['en' => 'Apply price lists and commission rules', 'bn' => 'প্রাইস লিস্ট ও কমিশন নিয়ম প্রয়োগ'],
        ],
    ],
    'procurement' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 18,
        'outcomes' => [
            ['en' => 'Create and approve purchase orders', 'bn' => 'ক্রয় অর্ডার তৈরি ও অনুমোদন'],
            ['en' => 'Post GRN and understand partial receipts', 'bn' => 'GRN পোস্ট ও আংশিক গ্রহণ বুঝুন'],
        ],
    ],
    'warehouses' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 10,
        'outcomes' => [
            ['en' => 'Configure factory vs depot warehouses', 'bn' => 'Factory বনাম depot গুদাম কনফিগার'],
            ['en' => 'Set bins and locations for accurate picking', 'bn' => 'সঠিক পিকের জন্য বিন ও লোকেশন সেট করুন'],
        ],
    ],
    'logistics' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 18,
        'outcomes' => [
            ['en' => 'Separate own-fleet cost from hired-carrier bills', 'bn' => 'নিজস্ব ফ্লিট খরচ ও ভাড়া ক্যারিয়ার বিল আলাদা করুন'],
            ['en' => 'Plan vehicle loads and read route profitability', 'bn' => 'যান লোড পরিকল্পনা ও রুট লাভজনকতা পড়ুন'],
        ],
    ],
    'manufacturing' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 20,
        'outcomes' => [
            ['en' => 'Build BOMs and run production batches', 'bn' => 'BOM তৈরি ও উৎপাদন ব্যাচ চালান'],
            ['en' => 'Complete QC and stock confirmation', 'bn' => 'QC ও স্টক নিশ্চিতকরণ সম্পন্ন করুন'],
        ],
    ],
    'inventory' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 12,
        'outcomes' => [
            ['en' => 'Read stock levels and low-stock signals', 'bn' => 'স্টক লেভেল ও লো-স্টক সিগন্যাল পড়ুন'],
            ['en' => 'Transfer stock between warehouses', 'bn' => 'গুদামের মধ্যে স্টক স্থানান্তর'],
        ],
    ],
    'sales' => [
        'track' => ['en' => 'Commercial', 'bn' => 'বাণিজ্য'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 15,
        'outcomes' => [
            ['en' => 'Create and confirm agent sales orders', 'bn' => 'এজেন্ট বিক্রয় অর্ডার তৈরি ও নিশ্চিত'],
            ['en' => 'Use targets, returns, gifts, and commission settlements', 'bn' => 'টার্গেট, ফেরত, গিফট ও কমিশন সেটেলমেন্ট ব্যবহার'],
        ],
    ],
    'delivery' => [
        'track' => ['en' => 'Operations', 'bn' => 'অপারেশন'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 14,
        'outcomes' => [
            ['en' => 'Dispatch deliveries with routes and vehicles', 'bn' => 'রুট ও যান দিয়ে ডেলিভারি পাঠান'],
            ['en' => 'Record POD before invoicing', 'bn' => 'ইনভয়েসের আগে POD রেকর্ড করুন'],
        ],
    ],
    'accounting' => [
        'track' => ['en' => 'Finance', 'bn' => 'অর্থ'],
        'level' => ['en' => 'Advanced', 'bn' => 'উন্নত'],
        'duration_min' => 25,
        'outcomes' => [
            ['en' => 'Issue invoices and post receipts', 'bn' => 'ইনভয়েস ইস্যু ও রসিদ পোস্ট'],
            ['en' => 'Handle VAT, withholding, bank reconciliation, and supplier bills', 'bn' => 'VAT, উৎসে কর, ব্যাংক মিলকরণ ও সাপ্লায়ার বিল পরিচালনা'],
        ],
    ],
    'profit-loss' => [
        'track' => ['en' => 'Finance', 'bn' => 'অর্থ'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 18,
        'outcomes' => [
            ['en' => 'Calculate gross profit per sold unit', 'bn' => 'বিক্রিত ইউনিটে মোট লাভ হিসাব'],
            ['en' => 'Know when COGS and revenue hit the P&L', 'bn' => 'COGS ও আয় কখন P&L-এ আসে জানুন'],
        ],
    ],
    'reports' => [
        'track' => ['en' => 'Finance', 'bn' => 'অর্থ'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 12,
        'outcomes' => [
            ['en' => 'Run month-end pack including logistics and fleet', 'bn' => 'লজিস্টিক্স ও ফ্লিটসহ মাস শেষ প্যাক চালান'],
            ['en' => 'Export CSV/PDF from Export center', 'bn' => 'Export center থেকে CSV/PDF নিন'],
        ],
    ],
    'ledger-mapping' => [
        'track' => ['en' => 'Finance', 'bn' => 'অর্থ'],
        'level' => ['en' => 'Intermediate', 'bn' => 'মধ্যম'],
        'duration_min' => 20,
        'outcomes' => [
            ['en' => 'Answer client questions about which ledger each transaction hits', 'bn' => 'ক্লায়েন্টের “কোন ledger?” প্রশ্নের উত্তর দিন'],
            ['en' => 'Know which documents post GL vs operational only', 'bn' => 'কোন ডকুমেন্ট GL পোস্ট করে জানুন'],
        ],
    ],
    'hr-payroll' => [
        'track' => ['en' => 'Finance', 'bn' => 'অর্থ'],
        'level' => ['en' => 'Beginner', 'bn' => 'প্রাথমিক'],
        'duration_min' => 12,
        'outcomes' => [
            ['en' => 'Separate HR records from payroll GL posting', 'bn' => 'HR রেকর্ড ও payroll GL আলাদা করুন'],
            ['en' => 'Post salary distributions without double-counting P&L', 'bn' => 'P&L দ্বিগুণ ছাড়া salary distribution পোস্ট'],
        ],
    ],
];
