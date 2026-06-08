<?php

return [
    'technical_tab' => ['en' => 'Production reference', 'bn' => 'উৎপাদন রেফারেন্স'],
    'steps_extra' => [
        ['title' => ['en' => 'Create batch', 'bn' => 'ব্যাচ তৈরি'], 'body' => ['en' => 'Batch/lot number for traceability and expiry (FEFO).', 'bn' => 'ট্রেসেবিলিটি ও মেয়াদ (FEFO) জন্য ব্যাচ নম্বর।'], 'path' => '/admin/batches'],
        ['title' => ['en' => 'Material issue', 'bn' => 'কাঁচামাল ইস্যু'], 'body' => ['en' => 'On confirm, BOM materials consume from factory warehouse.', 'bn' => 'নিশ্চিতকরণে BOM কাঁচামাল কারখানা গুদাম থেকে কাটে।'], 'path' => '/admin/production'],
    ],
    'deep_sections' => [
        [
            'id' => 'qc',
            'title' => ['en' => 'QC and stock confirm', 'bn' => 'QC ও স্টক নিশ্চিত'],
            'type' => 'prose',
            'body' => [
                'en' => "Production run completes → **QC** approves or rejects output → approved qty moves to **Pending receipts** → warehouse user **confirms stock**.\n\nOnly after confirm does finished goods appear in inventory for sales. If inventory GL is on, COGS-related WIP/FG journals post at confirm.",
                'bn' => "উৎপাদন রান শেষ → **QC** অনুমোদন/প্রত্যাখ্যান → অনুমোদিত পরিমাণ **Pending receipts** → গুদাম **স্টক নিশ্চিত**।\n\nনিশ্চিতের পরই তৈরি পণ্য বিক্রয়ের স্টকে আসে।",
            ],
        ],
    ],
    'faqs' => [
        ['q' => ['en' => 'Materials not consumed?', 'bn' => 'কাঁচামাল কাটছে না?'], 'a' => ['en' => 'Check: active BOM for product, enough RM stock in factory warehouse, production status reached confirm step.', 'bn' => 'চেক: সক্রিয় BOM, কারখানায় RM স্টক, উৎপাদন স্ট্যাটাস confirm পর্যন্ত।']],
    ],
    'report_links' => [
        ['title' => ['en' => 'Production dashboard', 'bn' => 'উৎপাদন ড্যাশবোর্ড'], 'path' => '/admin/manufacturing-dashboard', 'desc' => ['en' => 'Runs, variance, output summary', 'bn' => 'রান, ভ্যারিয়েন্স, আউটপুট']],
    ],
];
