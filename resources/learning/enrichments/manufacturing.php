<?php

return [
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => 'Manufacturing turns raw materials into **finished goods** using a **BOM recipe**. Every run has a **batch/lot** for traceability. **QC** must approve before stock is confirmed.',
                'bn' => 'উৎপাদন **BOM** দিয়ে কাঁচামাল **তৈরি পণ্যে** রূপান্তর করে। প্রতিটি রানে **ব্যাচ/লট**। **QC** অনুমোদনের পর স্টক নিশ্চিত।',
            ],
        ],
        [
            'type' => 'checklist',
            'title' => ['en' => 'Before first production run', 'bn' => 'প্রথম রানের আগে'],
            'items' => [
                ['en' => 'Finished product exists in catalog', 'bn' => 'তৈরি পণ্য ক্যাটালগে আছে'],
                ['en' => 'Active BOM with material lines and qty per 1 unit', 'bn' => 'সক্রিয় BOM — প্রতি ১ ইউনিটে কাঁচামাল'],
                ['en' => 'Enough raw material stock from GRN', 'bn' => 'GRN থেকে যথেষ্ট কাঁচামাল স্টক'],
                ['en' => 'Factory warehouse configured', 'bn' => 'Factory গুদাম সেট'],
            ],
        ],
    ],
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'Create BOM', 'bn' => 'BOM তৈরি'], 'desc' => ['en' => 'Recipe per FG unit', 'bn' => 'প্রতি ইউনিট রেসিপি'], 'phase' => 'foundation', 'path' => '/admin/boms/create'],
            ['label' => ['en' => 'Plan batch', 'bn' => 'ব্যাচ পরিকল্পনা'], 'desc' => ['en' => 'Lot code for traceability', 'bn' => 'লট কোড'], 'phase' => 'operations'],
            ['label' => ['en' => 'Production run', 'bn' => 'উৎপাদন রান'], 'desc' => ['en' => 'Execute on line/shift', 'bn' => 'লাইন/শিফটে চালান'], 'phase' => 'operations', 'path' => '/admin/production/create'],
            ['label' => ['en' => 'QC approve', 'bn' => 'QC অনুমোদন'], 'desc' => ['en' => 'Pass or reject batch', 'bn' => 'পাস বা প্রত্যাখ্যাত'], 'phase' => 'operations'],
            ['label' => ['en' => 'Confirm stock', 'bn' => 'স্টক নিশ্চিত'], 'desc' => ['en' => 'FG in, RM consumed', 'bn' => 'FG যোগ, RM কাটা'], 'phase' => 'operations', 'path' => '/admin/production/pending-receipts'],
        ],
    ],
    'step-0' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'BOM editor', 'bn' => 'BOM এডিটর'],
            'menu' => ['en' => 'Manufacturing → BOMs → Create', 'bn' => 'Manufacturing → BOMs → Create'],
            'path' => '/admin/boms/create',
            'form' => [
                ['field' => 'Finished product', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Output SKU', 'bn' => 'আউটপুট SKU']],
                ['field' => 'Material lines', 'required' => true, 'example' => '12 bottles + 12 caps + 1 carton', 'hint' => ['en' => 'Qty per 1 FG unit', 'bn' => 'প্রতি ১ তৈরি ইউনিট']],
                ['field' => 'Active', 'required' => true, 'example' => 'On', 'hint' => ['en' => 'Inactive BOM = no consumption', 'bn' => 'নিষ্ক্রিয় = কাঁচামাল কাটে না']],
            ],
        ],
        [
            'type' => 'example_card',
            'title' => ['en' => '1 carton BOM example', 'bn' => '১ কার্টন BOM'],
            'body' => [
                'en' => 'Per **SAF-500ML-CTN**: 12× RM-PET-500 bottles, 12× caps, 1× carton, 6L water. Total material cost ≈ ৳42 per carton when standard costs are set.',
                'bn' => 'প্রতি **SAF-500ML-CTN**: ১২ বোতল, ১২ ক্যাপ, ১ কার্টন, ৬L পানি। মোট ≈ ৳৪২/কার্টন।',
            ],
        ],
    ],
    'step-1' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Production run form', 'bn' => 'উৎপাদন রান ফর্ম'],
            'menu' => ['en' => 'Manufacturing → Production → Create', 'bn' => 'Manufacturing → Production → Create'],
            'path' => '/admin/production/create',
            'form' => [
                ['field' => 'Product + BOM', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Auto-loads BOM lines', 'bn' => 'BOM লাইন লোড']],
                ['field' => 'Batch lot', 'required' => true, 'example' => 'LOT-2026-042', 'hint' => ['en' => 'Trace on invoice queries', 'bn' => 'ইনভয়েস ট্রেস']],
                ['field' => 'Output qty', 'required' => true, 'example' => '500 cartons', 'hint' => ['en' => 'Planned production', 'bn' => 'পরিকল্পিত উৎপাদন']],
            ],
        ],
        [
            'type' => 'callout',
            'variant' => 'warning',
            'title' => ['en' => 'Low RM stock?', 'bn' => 'কাঁচামাল কম?'],
            'body' => [
                'en' => 'If raw material stock is insufficient, production cannot consume BOM. Check **Inventory → Low stock** and create PO first.',
                'bn' => 'কাঁচামাল কম হলে BOM কাটা যায় না। **Low stock** দেখে আগে PO করুন।',
            ],
        ],
    ],
    'step-2' => [
        [
            'type' => 'screen',
            'title' => ['en' => 'Pending receipts — confirm stock', 'bn' => 'Pending receipts'],
            'menu' => ['en' => 'Manufacturing → Pending receipts', 'bn' => 'Manufacturing → Pending receipts'],
            'path' => '/admin/production/pending-receipts',
            'highlights' => [
                ['label' => ['en' => 'QC passed runs only', 'bn' => 'QC পাস'], 'desc' => ['en' => 'Rejected QC cannot confirm FG', 'bn' => 'প্রত্যাখ্যাত QC = FG নয়']],
                ['label' => ['en' => 'Confirm stock', 'bn' => 'Confirm stock'], 'desc' => ['en' => 'Adds FG, consumes RM per BOM', 'bn' => 'FG যোগ, RM কাটে']],
            ],
        ],
        [
            'type' => 'clicks',
            'title' => ['en' => 'QC to stock confirm', 'bn' => 'QC থেকে স্টক'],
            'items' => [
                ['en' => 'Production officer completes run → status awaits **QC**.', 'bn' => 'রান সম্পন্ন → **QC** অপেক্ষা।'],
                ['en' => 'QC officer opens run → **Approve** or reject with reason.', 'bn' => 'QC **Approve** বা প্রত্যাখ্যাত।'],
                ['en' => 'Warehouse opens **Pending receipts** → select run → **Confirm stock**.', 'bn' => '**Pending receipts** → রান বেছে **Confirm stock**।'],
                ['en' => 'Verify FG qty on **Inventory dashboard**.', 'bn' => '**Inventory**-তে FG যাচাই।'],
            ],
        ],
    ],
];
