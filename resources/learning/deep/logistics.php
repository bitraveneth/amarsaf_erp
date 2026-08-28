<?php

return [
    'audience' => [
        'en' => 'Delivery coordinators, warehouse officers, and accounts officers who post fleet or carrier costs',
        'bn' => 'ডেলিভারি সমন্বয়ক, গুদাম কর্মকর্তা ও হিসাব কর্মকর্তা যারা ফ্লিট বা ক্যারিয়ার খরচ পোস্ট করেন',
    ],
    'technical_tab' => ['en' => 'Cost vs delivery', 'bn' => 'খরচ বনাম ডেলিভারি'],
    'faqs' => [
        [
            'q' => ['en' => 'Is a logistics bill the same as a purchase bill?', 'bn' => 'লজিস্টিক্স বিল কি ক্রয় বিল?'],
            'a' => ['en' => 'No. Purchase bills are for materials/suppliers. Logistics bills are hired-transport invoices against a transport carrier.', 'bn' => 'না। ক্রয় বিল কাঁচামাল/সাপ্লায়ারের। লজিস্টিক্স বিল ভাড়া পরিবহনের ক্যারিয়ার ইনভয়েস।'],
        ],
        [
            'q' => ['en' => 'When do I use fleet expense vs logistics bill?', 'bn' => 'কখন fleet expense, কখন logistics bill?'],
            'a' => ['en' => 'Own truck (fuel, maintenance, rent) → Fleet expenses. Hired truck/courier → Logistics bill on that carrier.', 'bn' => 'নিজস্ব ট্রাক (জ্বালানি, রক্ষণাবেক্ষণ, ভাড়া) → Fleet expenses। ভাড়া ট্রাক/কুরিয়ার → সেই ক্যারিয়ারের Logistics bill।'],
        ],
        [
            'q' => ['en' => 'Does vehicle load post to the ledger?', 'bn' => 'Vehicle load কি ledger-এ পোস্ট করে?'],
            'a' => ['en' => 'No. Vehicle load is operational planning. Cost posts when you save a fleet expense or a logistics bill.', 'bn' => 'না। Vehicle load অপারেশনাল পরিকল্পনা। খরচ পোস্ট হয় fleet expense বা logistics bill সেভ করলে।'],
        ],
        [
            'q' => ['en' => 'Where do delivery routes live?', 'bn' => 'ডেলিভারি রুট কোথায়?'],
            'a' => ['en' => 'Master data → Logistics → Delivery zones & routes. Use them on deliveries, vehicle loads, and route-cost reports.', 'bn' => 'Master data → Logistics → Delivery zones & routes। ডেলিভারি, যান লোড ও রুট-খরচ রিপোর্টে ব্যবহার হয়।'],
        ],
    ],
    'deep_sections' => [
        [
            'id' => 'split',
            'title' => ['en' => 'Two cost paths', 'bn' => 'দুই খরচের পথ'],
            'type' => 'prose',
            'body' => [
                'en' => '**Delivery / POD** proves goods arrived. **Logistics** records what the trip cost. Own fleet and hired carriers must not be posted twice for the same trip.',
                'bn' => '**Delivery / POD** পণ্য পৌঁছানোর প্রমাণ। **Logistics** ট্রিপের খরচ। একই ট্রিপে নিজস্ব ফ্লিট ও ভাড়া ক্যারিয়ার দুবার পোস্ট করবেন না।',
            ],
        ],
        [
            'id' => 'checklist',
            'title' => ['en' => 'Before you post cost', 'bn' => 'খরচ পোস্টের আগে'],
            'type' => 'checklist',
            'items' => [
                ['en' => 'Vehicle exists and is active if this is own fleet.', 'bn' => 'নিজস্ব ফ্লিট হলে যান আছে ও Active।'],
                ['en' => 'Transport carrier exists if this is hired freight — not a raw-material supplier.', 'bn' => 'ভাড়া ফ্রেইট হলে ক্যারিয়ার আছে — কাঁচামাল সাপ্লায়ার নয়।'],
                ['en' => 'Route is set so Route cost vs sales can group the spend.', 'bn' => 'রুট সেট আছে যাতে Route cost vs sales খরচ গ্রুপ করতে পারে।'],
                ['en' => 'POD is complete if you are comparing cost to delivered sales.', 'bn' => 'বিক্রয়ের সাথে খরচ তুলনা করতে POD সম্পন্ন।'],
            ],
        ],
    ],
];
