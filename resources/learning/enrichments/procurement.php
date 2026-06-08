<?php

return [
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => 'Procurement is how raw materials enter your factory. The chain is **Need stock → PO → Approve → GRN → QC → Stock increases → Supplier bill → Payment**.',
                'bn' => 'ক্রয় = কাঁচামাল কারখানায় আসার পথ। **প্রয়োজন → PO → অনুমোদন → GRN → QC → স্টক বাড়ে → বিল → পরিশোধ**।',
            ],
        ],
        [
            'type' => 'checklist',
            'title' => ['en' => 'Before you start', 'bn' => 'শুরুর আগে'],
            'items' => [
                ['en' => 'Suppliers created in Control → Suppliers', 'bn' => 'Suppliers মডিউলে সাপ্লায়ার আছে'],
                ['en' => 'Materials exist in catalog (course 01)', 'bn' => 'কাঁচামাল ক্যাটালগে আছে'],
                ['en' => 'Warehouse ready for receiving (usually Factory)', 'bn' => 'গ্রহণের গুদাম প্রস্তুত'],
            ],
        ],
    ],
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'Need materials', 'bn' => 'প্রয়োজন'], 'desc' => ['en' => 'Low stock or plan', 'bn' => 'লো স্টক বা পরিকল্পনা'], 'phase' => 'operations'],
            ['label' => ['en' => 'Create PO', 'bn' => 'PO তৈরি'], 'desc' => ['en' => 'Draft → approve', 'bn' => 'ড্রাফট → অনুমোদন'], 'phase' => 'operations', 'path' => '/admin/purchase-orders/create'],
            ['label' => ['en' => 'Supplier delivers', 'bn' => 'সাপ্লায়ার ডেলিভারি'], 'desc' => ['en' => 'Physical goods arrive', 'bn' => 'পণ্য আসে'], 'phase' => 'operations'],
            ['label' => ['en' => 'Post GRN', 'bn' => 'GRN পোস্ট'], 'desc' => ['en' => 'Receive qty + QC', 'bn' => 'পরিমাণ + QC'], 'phase' => 'operations', 'path' => '/admin/goods-receipts'],
            ['label' => ['en' => 'QC approved', 'bn' => 'QC অনুমোদিত'], 'desc' => ['en' => 'Stock increases', 'bn' => 'স্টক বাড়ে'], 'phase' => 'operations'],
            ['label' => ['en' => 'Supplier bill', 'bn' => 'সাপ্লায়ার বিল'], 'desc' => ['en' => 'AP + input VAT', 'bn' => 'AP + ইনপুট VAT'], 'phase' => 'finance', 'path' => '/admin/bills'],
        ],
    ],
    'step-0' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Create PO', 'bn' => 'PO তৈরি'],
            'menu' => ['en' => 'Control → Suppliers → Purchase orders → Create', 'bn' => 'Control → Suppliers → Purchase orders → Create'],
            'path' => '/admin/purchase-orders/create',
            'form' => [
                ['field' => 'Supplier', 'required' => true, 'example' => 'ABC Packaging Ltd', 'hint' => ['en' => 'Pick from registered list', 'bn' => 'তালিকা থেকে বেছে নিন']],
                ['field' => 'Expected delivery', 'required' => false, 'example' => '+7 days', 'hint' => ['en' => 'For follow-up', 'bn' => 'ফলো-আপের জন্য']],
                ['field' => 'Lines: material + qty + price', 'required' => true, 'example' => 'RM-PET-500 × 10,000 @ 3.20', 'hint' => ['en' => 'One row per material', 'bn' => 'প্রতি কাঁচামাল এক লাইন']],
            ],
        ],
        [
            'type' => 'status_table',
            'title' => ['en' => 'PO statuses', 'bn' => 'PO স্ট্যাটাস'],
            'rows' => [
                ['status' => 'Draft', 'meaning' => ['en' => 'Editing — cannot receive', 'bn' => 'সম্পাদনা — গ্রহণ নয়']],
                ['status' => 'Approved', 'meaning' => ['en' => 'Ready for GRN', 'bn' => 'GRN-এর জন্য প্রস্তুত']],
                ['status' => 'Partial received', 'meaning' => ['en' => 'Some qty on GRN already', 'bn' => 'আংশিক GRN হয়েছে']],
                ['status' => 'Received', 'meaning' => ['en' => 'Fully received', 'bn' => 'সম্পূর্ণ গ্রহণ']],
            ],
        ],
        [
            'type' => 'clicks',
            'title' => ['en' => 'Create & approve PO', 'bn' => 'PO তৈরি ও অনুমোদন'],
            'items' => [
                ['en' => 'Open **Purchase orders** → **Create**.', 'bn' => '**Purchase orders** → **Create**।'],
                ['en' => 'Select supplier, add material lines with qty and unit price.', 'bn' => 'সাপ্লায়ার, লাইনে পরিমাণ ও মূল্য দিন।'],
                ['en' => '**Save** as Draft — review totals.', 'bn' => '**Save** ড্রাফট — মোট যাচাই।'],
                ['en' => 'Click **Approve** when correct. Status becomes **Approved**.', 'bn' => 'ঠিক হলে **Approve** — স্ট্যাটাস **Approved**।'],
            ],
        ],
    ],
    'step-1' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'GRN receive screen', 'bn' => 'GRN গ্রহণ স্ক্রিন'],
            'menu' => ['en' => 'Approved PO → Receive goods', 'bn' => 'অনুমোদিত PO → Receive goods'],
            'path' => '/admin/goods-receipts',
            'form' => [
                ['field' => 'Warehouse', 'required' => true, 'example' => 'Factory Main', 'hint' => ['en' => 'Destination for stock', 'bn' => 'স্টক গন্তব্য']],
                ['field' => 'Received qty per line', 'required' => true, 'example' => '6,000 now, 4,000 later', 'hint' => ['en' => 'Partial OK', 'bn' => 'আংশিক চলে']],
                ['field' => 'QC per line', 'required' => true, 'example' => 'Approved', 'hint' => ['en' => 'Only Approved adds stock', 'bn' => 'Approved-এই স্টক']],
            ],
        ],
        [
            'type' => 'callout',
            'variant' => 'warning',
            'title' => ['en' => 'QC pending = no stock', 'bn' => 'QC পেন্ডিং = স্টক নয়'],
            'body' => [
                'en' => 'If GRN lines stay **Pending QC**, material stock will **not** increase. Warehouse and procurement must approve GRN on dual-approval setups.',
                'bn' => 'GRN লাইন **Pending QC** থাকলে স্টক **বাড়ে না**। দ্বৈত অনুমোদনে গুদাম ও ক্রয় দুজনে সাইন অফ করুন।',
            ],
        ],
    ],
];
