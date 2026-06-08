<?php

return [
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'Categories', 'bn' => 'ক্যাটাগরি'], 'desc' => ['en' => 'Group materials', 'bn' => 'কাঁচামাল গ্রুপ'], 'phase' => 'foundation'],
            ['label' => ['en' => 'Materials', 'bn' => 'কাঁচামাল'], 'desc' => ['en' => 'Raw & service SKUs', 'bn' => 'Raw ও service SKU'], 'phase' => 'foundation', 'path' => '/admin/materials/create'],
            ['label' => ['en' => 'Products', 'bn' => 'পণ্য'], 'desc' => ['en' => 'Finished goods to sell', 'bn' => 'বিক্রয়যোগ্য পণ্য'], 'phase' => 'foundation', 'path' => '/admin/products/create'],
            ['label' => ['en' => 'Tax classes', 'bn' => 'Tax classes'], 'desc' => ['en' => 'Correct VAT on invoices', 'bn' => 'সঠিক VAT'], 'phase' => 'finance'],
            ['label' => ['en' => 'Ready', 'bn' => 'প্রস্তুত'], 'desc' => ['en' => 'BOM and sales can start', 'bn' => 'BOM ও বিক্রয় শুরু'], 'phase' => 'operations'],
        ],
    ],
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => "**Materials** = what you buy and consume (bottles, caps, water). **Products** = what you sell (cartons, jars). If it goes *into* the factory → Material. If it goes *out* to agents → Product.",
                'bn' => '**কাঁচামাল** = যা কিনে ব্যবহার করেন। **পণ্য** = যা বিক্রি করেন। কারখানায় ঢোকে → কাঁচামাল। এজেন্টের কাছে যায় → পণ্য।',
            ],
        ],
    ],
    'step-0' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Add material form', 'bn' => 'কাঁচামাল ফর্ম'],
            'menu' => ['en' => 'Control → Products → Materials → Add', 'bn' => 'Control → Products → Materials → Add'],
            'path' => '/admin/materials/create',
            'form' => [
                ['field' => 'Material name', 'required' => true, 'example' => 'PET Bottle 500ml', 'hint' => ['en' => 'Warehouse recognises this name', 'bn' => 'গুদামের চেনা নাম']],
                ['field' => 'Type: Raw / Service', 'required' => true, 'example' => 'Raw', 'hint' => ['en' => 'Raw = physical stock qty', 'bn' => 'Raw = স্টক পরিমাণ']],
                ['field' => 'SKU', 'required' => true, 'example' => 'RM-PET-500', 'hint' => ['en' => 'Unique on PO and BOM', 'bn' => 'PO ও BOM-এ অনন্য']],
                ['field' => 'UOM', 'required' => false, 'example' => 'piece', 'hint' => ['en' => 'piece, liter, kg', 'bn' => 'piece, liter, kg']],
                ['field' => 'Standard cost', 'required' => false, 'example' => '3.50', 'hint' => ['en' => 'BOM costing default', 'bn' => 'BOM খরচ']],
            ],
        ],
        [
            'type' => 'clicks',
            'title' => ['en' => 'Click-by-click', 'bn' => 'ধাপে ধাপে ক্লিক'],
            'items' => [
                ['en' => 'Sidebar → **Control** → **Products** → **Materials (raw/service)**.', 'bn' => 'সাইডবার → **Control** → **Products** → **Materials**।'],
                ['en' => 'Click **Add material** (top right).', 'bn' => '**Add material** ক্লিক করুন।'],
                ['en' => 'Fill name, type **Raw**, SKU **RM-PET-500**, UOM **piece**, cost **3.50**.', 'bn' => 'নাম, টাইপ **Raw**, SKU, UOM, খরচ পূরণ করুন।'],
                ['en' => 'Leave **Active** on → **Save**. Material appears in PO and BOM pickers.', 'bn' => '**Active** চালু → **Save**। PO ও BOM-এ দেখা যাবে।'],
            ],
        ],
    ],
    'step-1' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Add product form', 'bn' => 'পণ্য ফর্ম'],
            'menu' => ['en' => 'Control → Products → Add product', 'bn' => 'Control → Products → Add product'],
            'path' => '/admin/products/create',
            'form' => [
                ['field' => 'Product name', 'required' => true, 'example' => 'SAF 500ml Carton 12x', 'hint' => ['en' => 'Shown on sales orders', 'bn' => 'বিক্রয় অর্ডারে দেখায়']],
                ['field' => 'SKU', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Never duplicate', 'bn' => 'দুবার নয়']],
                ['field' => 'Base price', 'required' => true, 'example' => '450', 'hint' => ['en' => 'Net before VAT', 'bn' => 'VAT-আগে নেট']],
                ['field' => 'Tax class', 'required' => true, 'example' => 'VAT 15%', 'hint' => ['en' => 'Wrong class = wrong NBR figures', 'bn' => 'ভুল ক্লাস = ভুল VAT']],
            ],
        ],
        [
            'type' => 'callout',
            'variant' => 'warning',
            'title' => ['en' => 'Tax class is critical', 'bn' => 'ট্যাক্স ক্লাস গুরুত্বপূর্ণ'],
            'body' => [
                'en' => 'Every product needs correct **tax class** before you invoice. Fix tax class first if VAT report does not match expectations.',
                'bn' => 'ইনভয়েসের আগে সঠিক **ট্যাক্স ক্লাস** দিন। VAT রিপোর্ট মিল না হলে আগে এটা ঠিক করুন।',
            ],
        ],
    ],
];
