<?php

return [
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => "Saf ERP connects **buying materials → making products → storing stock → selling to agents → delivering → invoicing** in one system. Every document links to the next so you can trace any carton from invoice back to batch, production, GRN, and PO.",
                'bn' => 'Saf ERP **কাঁচামাল কেনা → তৈরি → স্টক → এজেন্টকে বিক্রি → ডেলিভারি → ইনভয়েস** এক সিস্টেমে জোড়ে। প্রতিটি ডকুমেন্ট পরেরটির সাথে যুক্ত — যেকোনো কার্টন ইনভয়েস থেকে ব্যাচ, উৎপাদন, GRN, PO পর্যন্ত ট্রেস করতে পারবেন।',
            ],
        ],
        [
            'type' => 'module_grid',
            'title' => ['en' => 'ERP module map', 'bn' => 'ERP মডিউল মানচিত্র'],
            'rows' => [
                ['num' => '01', 'name' => ['en' => 'Products & materials', 'bn' => 'পণ্য ও কাঁচামাল'], 'output' => ['en' => 'SKUs for BOM and sales', 'bn' => 'BOM ও বিক্রয়ের SKU']],
                ['num' => '02', 'name' => ['en' => 'Agents & pricing', 'bn' => 'এজেন্ট ও মূল্য'], 'output' => ['en' => 'Who you sell to', 'bn' => 'কাকে বিক্রি করেন']],
                ['num' => '03', 'name' => ['en' => 'Procurement', 'bn' => 'ক্রয়'], 'output' => ['en' => 'Materials in stock', 'bn' => 'কাঁচামাল স্টকে']],
                ['num' => '04', 'name' => ['en' => 'Warehouses & routes', 'bn' => 'গুদাম ও রুট'], 'output' => ['en' => 'Where stock lives', 'bn' => 'স্টক কোথায়']],
                ['num' => '05', 'name' => ['en' => 'Manufacturing', 'bn' => 'উৎপাদন'], 'output' => ['en' => 'Finished goods + batch', 'bn' => 'তৈরি পণ্য + ব্যাচ']],
                ['num' => '06', 'name' => ['en' => 'Inventory', 'bn' => 'ইনভেন্টরি'], 'output' => ['en' => 'Accurate quantities', 'bn' => 'সঠিক পরিমাণ']],
                ['num' => '07', 'name' => ['en' => 'Sales', 'bn' => 'বিক্রয়'], 'output' => ['en' => 'Confirmed orders', 'bn' => 'নিশ্চিত অর্ডার']],
                ['num' => '08', 'name' => ['en' => 'Delivery & POD', 'bn' => 'ডেলিভারি ও POD'], 'output' => ['en' => 'Goods delivered', 'bn' => 'পণ্য পৌঁছেছে']],
                ['num' => '09', 'name' => ['en' => 'Accounting', 'bn' => 'হিসাব'], 'output' => ['en' => 'Ledger updated', 'bn' => 'লেজার আপডেট']],
                ['num' => '10', 'name' => ['en' => 'Transaction → ledger map', 'bn' => 'লেনদেন → লেজার'], 'output' => ['en' => 'Client cheat sheet', 'bn' => 'ক্লায়েন্ট cheat sheet']],
                ['num' => '11', 'name' => ['en' => 'HR & payroll', 'bn' => 'HR ও পে-রোল'], 'output' => ['en' => 'Payroll to GL', 'bn' => 'GL-এ পে-রোল']],
                ['num' => '12', 'name' => ['en' => 'Reports', 'bn' => 'রিপোর্ট'], 'output' => ['en' => 'P&L, stock, aging', 'bn' => 'P&L, স্টক, aging']],
            ],
        ],
        [
            'type' => 'checklist',
            'title' => ['en' => 'Go-live checklist', 'bn' => 'গো-লাইভ চেকলিস্ট'],
            'items' => [
                ['en' => 'At least one material, product, agent, supplier, and warehouse exist.', 'bn' => 'অন্তত এক কাঁচামাল, পণ্য, এজেন্ট, সাপ্লায়ার ও গুদাম আছে।'],
                ['en' => 'Tax classes set on products (VAT rate correct).', 'bn' => 'পণ্যে ট্যাক্স ক্লাস (VAT হার) ঠিক আছে।'],
                ['en' => 'Active BOM for each finished good you manufacture.', 'bn' => 'প্রতিটি তৈরি পণ্যের সক্রিয় BOM।'],
                ['en' => 'Test full cycle with one SKU: PO → GRN → production → sales → delivery → invoice.', 'bn' => 'এক SKU দিয়ে পূর্ণ চক্র টেস্ট: PO → GRN → উৎপাদন → বিক্রয় → ডেলিভারি → ইনভয়েস।'],
            ],
        ],
    ],
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'Master data', 'bn' => 'মাস্টার ডেটা'], 'desc' => ['en' => 'Materials, products, agents, suppliers, warehouses', 'bn' => 'কাঁচামাল, পণ্য, এজেন্ট, সাপ্লায়ার, গুদাম'], 'phase' => 'foundation'],
            ['label' => ['en' => 'Buy (PO + GRN)', 'bn' => 'কিনুন (PO + GRN)'], 'desc' => ['en' => 'Raw materials into stock', 'bn' => 'কাঁচামাল স্টকে'], 'phase' => 'operations', 'path' => '/admin/purchase-orders'],
            ['label' => ['en' => 'Make (BOM + run)', 'bn' => 'তৈরি (BOM + রান)'], 'desc' => ['en' => 'Production, QC, FG stock', 'bn' => 'উৎপাদন, QC, FG স্টক'], 'phase' => 'operations', 'path' => '/admin/production'],
            ['label' => ['en' => 'Sell (order)', 'bn' => 'বিক্রি (অর্ডার)'], 'desc' => ['en' => 'Agent order, stock reserved', 'bn' => 'এজেন্ট অর্ডার, স্টক রিজার্ভ'], 'phase' => 'commercial', 'path' => '/admin/orders'],
            ['label' => ['en' => 'Deliver (POD)', 'bn' => 'ডেলিভারি (POD)'], 'desc' => ['en' => 'Goods reach agent', 'bn' => 'পণ্য এজেন্টের কাছে'], 'phase' => 'operations', 'path' => '/admin/deliveries/pod'],
            ['label' => ['en' => 'Bill & collect', 'bn' => 'বিল ও আদায়'], 'desc' => ['en' => 'Invoice, receipt, P&L', 'bn' => 'ইনভয়েস, রসিদ, P&L'], 'phase' => 'finance', 'path' => '/admin/finance'],
        ],
    ],
    'flow' => [
        [
            'type' => 'callout',
            'variant' => 'info',
            'title' => ['en' => 'Document trail', 'bn' => 'ডকুমেন্ট ট্রেইল'],
            'body' => [
                'en' => '**PO → GRN → Production → Sales order → Delivery → Invoice → Receipt**. If a customer asks where a carton came from, trace backwards through this chain.',
                'bn' => '**PO → GRN → উৎপাদন → বিক্রয় অর্ডার → ডেলিভারি → ইনভয়েস → রসিদ**। কার্টন কোথা থেকে এসেছে জানতে এই চেইনে পিছনে যান।',
            ],
        ],
        [
            'type' => 'compare',
            'title' => ['en' => 'How stock moves', 'bn' => 'স্টক কীভাবে চলে'],
            'columns' => [
                ['header' => ['en' => 'Event', 'bn' => 'ঘটনা'], 'rows' => [
                    ['en' => 'GRN posted (QC approved)', 'bn' => 'GRN পোস্ট (QC অনুমোদিত)'],
                    ['en' => 'Production confirmed', 'bn' => 'উৎপাদন নিশ্চিত'],
                    ['en' => 'Delivery completed', 'bn' => 'ডেলিভারি সম্পন্ন'],
                ]],
                ['header' => ['en' => 'Material stock', 'bn' => 'কাঁচামাল'], 'rows' => [
                    ['en' => '↑ Increases', 'bn' => '↑ বাড়ে'],
                    ['en' => '↓ Decreases (BOM)', 'bn' => '↓ কমে (BOM)'],
                    ['en' => '—', 'bn' => '—'],
                ]],
                ['header' => ['en' => 'FG stock', 'bn' => 'তৈরি পণ্য'], 'rows' => [
                    ['en' => '—', 'bn' => '—'],
                    ['en' => '↑ Increases', 'bn' => '↑ বাড়ে'],
                    ['en' => '↓ Decreases', 'bn' => '↓ কমে'],
                ]],
            ],
        ],
    ],
    'step-0' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Master data screens', 'bn' => 'মাস্টার ডেটা স্ক্রিন'],
            'menu' => ['en' => 'Control → Products / Suppliers / Agents', 'bn' => 'Control → Products / Suppliers / Agents'],
            'path' => '/admin/products',
            'highlights' => [
                ['label' => ['en' => 'Materials', 'bn' => 'Materials'], 'desc' => ['en' => 'Raw items you buy (bottles, caps).', 'bn' => 'যা কিনেন (বোতল, ক্যাপ)।']],
                ['label' => ['en' => 'Products', 'bn' => 'Products'], 'desc' => ['en' => 'Finished goods you sell.', 'bn' => 'যা বিক্রি করেন।']],
                ['label' => ['en' => 'Suppliers & Agents', 'bn' => 'Suppliers & Agents'], 'desc' => ['en' => 'Who you buy from and sell to.', 'bn' => 'কার কাছ থেকে কিনেন, কাকে বিক্রি করেন।']],
            ],
        ],
        [
            'type' => 'clicks',
            'title' => ['en' => 'Setup walkthrough', 'bn' => 'সেটআপ ওয়াকথ্রু'],
            'items' => [
                ['en' => 'Open **Control → Products → Materials** → Add at least one raw material (e.g. RM-PET-500).', 'bn' => '**Control → Products → Materials** → এক কাঁচামাল যোগ করুন।'],
                ['en' => 'Open **Control → Products → Products** → Add finished product (e.g. SAF-500ML-CTN).', 'bn' => '**Products** → তৈরি পণ্য যোগ করুন।'],
                ['en' => 'Open **Control → Suppliers** → Add your main packaging supplier.', 'bn' => '**Suppliers** → সাপ্লায়ার যোগ করুন।'],
                ['en' => 'Open **Sales → Agents** → Add one agent with zone and credit limit.', 'bn' => '**Agents** → এক এজেন্ট যোগ করুন।'],
                ['en' => 'Open **Control → Warehouses** → Add Factory warehouse for production.', 'bn' => '**Warehouses** → Factory গুদাম যোগ করুন।'],
            ],
        ],
    ],
    'step-1' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Purchase order screen', 'bn' => 'ক্রয় অর্ডার স্ক্রিন'],
            'menu' => ['en' => 'Control → Suppliers → Purchase orders', 'bn' => 'Control → Suppliers → Purchase orders'],
            'path' => '/admin/purchase-orders/create',
            'form' => [
                ['field' => 'Supplier', 'required' => true, 'example' => 'ABC Packaging', 'hint' => ['en' => 'Registered supplier', 'bn' => 'নিবন্ধিত সাপ্লায়ার']],
                ['field' => 'Material lines', 'required' => true, 'example' => '10,000 bottles', 'hint' => ['en' => 'Qty + price per line', 'bn' => 'পরিমাণ + মূল্য']],
            ],
        ],
        [
            'type' => 'callout',
            'variant' => 'warning',
            'title' => ['en' => 'Approve before GRN', 'bn' => 'GRN-এর আগে অনুমোদন'],
            'body' => [
                'en' => 'Draft PO does **not** add stock. Click **Approve** when lines are correct, then warehouse can receive via GRN.',
                'bn' => 'ড্রাফট PO স্টক বাড়ায় না। লাইন ঠিক হলে **Approve** করুন, তারপর GRN।',
            ],
        ],
    ],
    'step-2' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Production run screen', 'bn' => 'উৎপাদন রান স্ক্রিন'],
            'menu' => ['en' => 'Manufacturing → Production → Create', 'bn' => 'Manufacturing → Production → Create'],
            'path' => '/admin/production/create',
            'highlights' => [
                ['label' => ['en' => 'BOM required', 'bn' => 'BOM প্রয়োজন'], 'desc' => ['en' => 'System consumes materials per recipe.', 'bn' => 'রেসিপি অনুযায়ী কাঁচামাল কাটে।']],
                ['label' => ['en' => 'QC gate', 'bn' => 'QC'], 'desc' => ['en' => 'QC must approve before FG stock.', 'bn' => 'FG স্টকের আগে QC।']],
            ],
        ],
    ],
    'step-3' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Sales to cash screens', 'bn' => 'বিক্রয় থেকে নগদ স্ক্রিন'],
            'menu' => ['en' => 'Sales → Orders → Delivery → Accounting', 'bn' => 'Sales → Orders → Delivery → Accounting'],
            'path' => '/admin/orders',
            'highlights' => [
                ['label' => ['en' => 'Confirm order', 'bn' => 'অর্ডার নিশ্চিত'], 'desc' => ['en' => 'Reserves FG stock.', 'bn' => 'FG রিজার্ভ।']],
                ['label' => ['en' => 'POD', 'bn' => 'POD'], 'desc' => ['en' => 'Record delivered qty.', 'bn' => 'ডেলিভার্ড পরিমাণ।']],
                ['label' => ['en' => 'Invoice + receipt', 'bn' => 'ইনভয়েস + রসিদ'], 'desc' => ['en' => 'Revenue and cash collection.', 'bn' => 'আয় ও টাকা আদায়।']],
            ],
        ],
        [
            'type' => 'compare',
            'title' => ['en' => 'When money hits P&L', 'bn' => 'P&L-এ টাকা কখন'],
            'columns' => [
                ['header' => ['en' => 'Action', 'bn' => 'কাজ'], 'rows' => [
                    ['en' => 'GRN posted', 'bn' => 'GRN'],
                    ['en' => 'Invoice issued', 'bn' => 'ইনভয়েস'],
                    ['en' => 'Receipt posted', 'bn' => 'রসিদ'],
                ]],
                ['header' => ['en' => 'P&L effect', 'bn' => 'P&L প্রভাব'], 'rows' => [
                    ['en' => 'None (balance sheet stock)', 'bn' => 'নয় (ব্যালেন্স শিট স্টক)'],
                    ['en' => 'Revenue + COGS', 'bn' => 'আয় + COGS'],
                    ['en' => 'None (clears AR only)', 'bn' => 'নয় (শুধু AR ক্লিয়ার)'],
                ]],
            ],
        ],
    ],
    'practice' => [
        [
            'type' => 'example_card',
            'title' => ['en' => 'Example: one carton of water', 'bn' => 'উদাহরণ: এক কার্টন পানি'],
            'body' => [
                'en' => "1. Buy bottles + caps (PO + GRN)\n2. BOM: 12 bottles + 1 carton per output\n3. Production makes SAF-500ML-CTN\n4. Agent orders 100 cartons\n5. Deliver with POD → Invoice → Receipt\n\nTrace: **INV → DEL → SO → Batch → Production → GRN → PO**",
                'bn' => "১. বোতল + ক্যাপ কিনুন (PO + GRN)\n২. BOM: ১২ বোতল + ১ কার্টন\n৩. SAF-500ML-CTN উৎপাদন\n৪. এজেন্ট ১০০ কার্টন অর্ডার\n৫. POD → ইনভয়েস → রসিদ",
            ],
        ],
        [
            'type' => 'tips',
            'title' => ['en' => 'Daily rhythm', 'bn' => 'দৈনিক রুটিন'],
            'items' => [
                ['en' => '**Morning:** Clear GRN backlog, production QC, pending stock confirms.', 'bn' => '**সকাল:** GRN, QC, স্টক নিশ্চিতকরণ।'],
                ['en' => '**Midday:** Process sales orders — check FG stock.', 'bn' => '**দুপুর:** বিক্রয় অর্ডার — FG স্টক দেখুন।'],
                ['en' => '**Afternoon:** Dispatch and update POD.', 'bn' => '**বিকেল:** ডিসপ্যাচ ও POD।'],
                ['en' => '**End of day:** Post receipts, review open invoices.', 'bn' => '**দিন শেষ:** রসিদ, খোলা ইনভয়েস।'],
            ],
        ],
    ],
];
