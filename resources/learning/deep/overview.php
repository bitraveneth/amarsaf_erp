<?php

return [
    'quick_start' => [
        'title' => ['en' => '5-minute start (new team)', 'bn' => '৫ মিনিটে শুরু (নতুন দল)'],
        'items' => [
            ['en' => '**Day 1 — Setup:** Add materials, products, one supplier, one agent, one warehouse.', 'bn' => '**দিন ১ — সেটআপ:** কাঁচামাল, পণ্য, এক সাপ্লায়ার, এক এজেন্ট, এক গুদাম।'],
            ['en' => '**Day 2 — Buy:** Create PO → approve → post GRN. Check material stock.', 'bn' => '**দিন ২ — কেনা:** PO → অনুমোদন → GRN। কাঁচামাল স্টক দেখুন।'],
            ['en' => '**Day 3 — Make:** BOM → production run → QC → confirm FG stock.', 'bn' => '**দিন ৩ — উৎপাদন:** BOM → রান → QC → FG স্টক।'],
            ['en' => '**Day 4 — Sell:** Sales order → confirm → delivery → POD → invoice → receipt.', 'bn' => '**দিন ৪ — বিক্রি:** অর্ডার → নিশ্চিত → ডেলিভারি → POD → ইনভয়েস → রসিদ।'],
        ],
    ],
    'onboarding_checklist' => [
        'title' => ['en' => 'Go-live checklist', 'bn' => 'গো-লাইভ চেকলিস্ট'],
        'items' => [
            ['en' => 'At least one material, product, agent, supplier, and warehouse exist.', 'bn' => 'অন্তত এক কাঁচামাল, পণ্য, এজেন্ট, সাপ্লায়ার ও গুদাম আছে।'],
            ['en' => 'Tax classes set on products (VAT rate correct).', 'bn' => 'পণ্যে ট্যাক্স ক্লাস (VAT হার) ঠিক আছে।'],
            ['en' => 'Active BOM for each finished good you manufacture.', 'bn' => 'প্রতিটি তৈরি পণ্যের সক্রিয় BOM।'],
            ['en' => 'Test PO → GRN → production → sales → invoice flow with one SKU.', 'bn' => 'এক SKU দিয়ে PO → GRN → উৎপাদন → বিক্রয় → ইনভয়েস টেস্ট।'],
            ['en' => 'Finance user can open trial balance and VAT report.', 'bn' => 'হিসাব ব্যবহারকারী ট্রায়াল ব্যালেন্স ও VAT রিপোর্ট খুলতে পারে।'],
        ],
    ],
    'faqs' => [
        [
            'q' => ['en' => 'What order should we train staff?', 'bn' => 'স্টাফকে কোন ক্রমে প্রশিক্ষণ দেব?'],
            'a' => ['en' => 'Master data → procurement → manufacturing → inventory → sales → delivery → accounting → reports. Use the role filter above if someone only needs one area.', 'bn' => 'মাস্টার ডেটা → ক্রয় → উৎপাদন → ইনভেন্টরি → বিক্রয় → ডেলিভারি → হিসাব → রিপোর্ট। এক এলাকার জন্য রোল ফিল্টার ব্যবহার করুন।'],
        ],
        [
            'q' => ['en' => 'Where is the full written manual?', 'bn' => 'সম্পূর্ণ লিখিত ম্যানুয়াল কোথায়?'],
            'a' => ['en' => 'Open **Full written manual** from the hero — same content as docs/ for printing and sharing with clients.', 'bn' => 'হিরো থেকে **সম্পূর্ণ লিখিত নির্দেশিকা** খুলুন — `docs/` ফোল্ডারের মতো প্রিন্ট ও শেয়ারের জন্য।'],
        ],
    ],
    'glossary' => [
        ['term' => ['en' => 'GRN', 'bn' => 'GRN'], 'def' => ['en' => 'Goods Receipt Note — stock-in document from a purchase order.', 'bn' => 'গুডস রিসিপ্ট নোট — ক্রয় অর্ডার থেকে স্টক-ইন ডকুমেন্ট।']],
        ['term' => ['en' => 'BOM', 'bn' => 'BOM'], 'def' => ['en' => 'Bill of Materials — recipe of raw materials per finished unit.', 'bn' => 'বিল অফ ম্যাটেরিয়ালস — প্রতি তৈরি ইউনিটের কাঁচামালের রেসিপি।']],
        ['term' => ['en' => 'POD', 'bn' => 'POD'], 'def' => ['en' => 'Proof of Delivery — record of what the agent actually received.', 'bn' => 'প্রুফ অফ ডেলিভারি — এজেন্ট আসলে কী পেয়েছে তার রেকর্ড।']],
    ],
];
