<?php

return [
    'welcome' => [
        [
            'type' => 'prose',
            'body' => [
                'en' => 'Logistics sits **after** delivery planning and **beside** accounting. Use it to record **how much movement cost** — own trucks (fleet expenses) or hired carriers (logistics bills) — then compare that spend to sales by route.',
                'bn' => 'Logistics ডেলিভারি পরিকল্পনার **পরে** এবং হিসাবের **পাশে**। এখানে **সরানোর খরচ** রেকর্ড হয় — নিজস্ব ট্রাক (fleet expense) বা ভাড়া ক্যারিয়ার (logistics bill) — তারপর রুট অনুযায়ী বিক্রয়ের সাথে তুলনা।',
            ],
        ],
        [
            'type' => 'compare',
            'title' => ['en' => 'Do not mix these screens', 'bn' => 'এই স্ক্রিনগুলো মিশাবেন না'],
            'columns' => [
                ['header' => ['en' => 'Screen', 'bn' => 'স্ক্রিন'], 'rows' => [
                    ['en' => 'POD / Deliveries', 'bn' => 'POD / ডেলিভারি'],
                    ['en' => 'Vehicle load', 'bn' => 'যান লোড'],
                    ['en' => 'Fleet expenses', 'bn' => 'ফ্লিট খরচ'],
                    ['en' => 'Logistics bills', 'bn' => 'লজিস্টিক্স বিল'],
                    ['en' => 'Purchase bills', 'bn' => 'ক্রয় বিল'],
                ]],
                ['header' => ['en' => 'What it records', 'bn' => 'কী রেকর্ড করে'], 'rows' => [
                    ['en' => 'Goods arrived (qty, short, damage)', 'bn' => 'পণ্য পৌঁছেছে (পরিমাণ, কমতি, ক্ষতি)'],
                    ['en' => 'Which deliveries share one truck trip', 'bn' => 'কোন ডেলিভারি এক ট্রাকে'],
                    ['en' => 'Own-truck fuel, maintenance, rent', 'bn' => 'নিজস্ব ট্রাকের জ্বালানি, রক্ষণাবেক্ষণ'],
                    ['en' => 'Hired carrier invoice + VAT + pay', 'bn' => 'ভাড়া ক্যারিয়ার ইনভয়েস + VAT'],
                    ['en' => 'Raw materials from a supplier', 'bn' => 'সাপ্লায়ারের কাঁচামাল'],
                ]],
            ],
        ],
    ],
    'flow_pipeline' => [
        'steps' => [
            ['label' => ['en' => 'Vehicles & routes', 'bn' => 'যান ও রুট'], 'desc' => ['en' => 'Registry + zones', 'bn' => 'রেজিস্ট্রি + জোন'], 'phase' => 'operations', 'path' => '/admin/vehicles'],
            ['label' => ['en' => 'Vehicle load', 'bn' => 'যান লোড'], 'desc' => ['en' => 'Assign deliveries to a trip', 'bn' => 'ট্রিপে ডেলিভারি'], 'phase' => 'operations', 'path' => '/admin/vehicle-load'],
            ['label' => ['en' => 'Own fleet cost', 'bn' => 'নিজস্ব ফ্লিট'], 'desc' => ['en' => 'Fuel / maintenance', 'bn' => 'জ্বালানি / রক্ষণাবেক্ষণ'], 'phase' => 'operations', 'path' => '/admin/fleet-expenses'],
            ['label' => ['en' => 'Carrier + rate card', 'bn' => 'ক্যারিয়ার + রেট'], 'desc' => ['en' => 'Hired transport vendor', 'bn' => 'ভাড়া পরিবহন'], 'phase' => 'operations', 'path' => '/admin/logistics/carriers'],
            ['label' => ['en' => 'Logistics bill', 'bn' => 'লজিস্টিক্স বিল'], 'desc' => ['en' => 'Carrier invoice & pay', 'bn' => 'ক্যারিয়ার ইনভয়েস'], 'phase' => 'finance', 'path' => '/admin/logistics-bills'],
            ['label' => ['en' => 'Route vs sales', 'bn' => 'রুট বনাম বিক্রয়'], 'desc' => ['en' => 'Is the route profitable?', 'bn' => 'রুট লাভজনক?'], 'phase' => 'finance', 'path' => '/admin/reports/route-costs'],
        ],
    ],
    'flow' => [
        [
            'type' => 'callout',
            'variant' => 'tip',
            'title' => ['en' => 'Cost posts on the bill or expense — not on the load sheet', 'bn' => 'খরচ বিল বা expense-এ — লোড শিটে নয়'],
            'body' => [
                'en' => 'Planning a vehicle load does not hit the P&L. Saving a **fleet expense** or **logistics bill** does.',
                'bn' => 'যান লোড পরিকল্পনা P&L-এ যায় না। **fleet expense** বা **logistics bill** সেভ করলে যায়।',
            ],
        ],
    ],
    'practice' => [
        [
            'type' => 'example_card',
            'title' => ['en' => 'Own van vs hired carrier', 'bn' => 'নিজস্ব ভ্যান বনাম ভাড়া ক্যারিয়ার'],
            'body' => [
                'en' => "Dhaka city loop: own van — record diesel as a **fleet expense** on that vehicle.\nChittagong trip: hired XYZ Logistics — create a **logistics bill** (freight + loading + VAT), then pay outstanding from the bill.\nDo not also raise a purchase bill on a bottle supplier for the same freight.",
                'bn' => "ঢাকা লুপ: নিজস্ব ভ্যান — ডিজেল **fleet expense**।\nচট্টগ্রাম: XYZ Logistics ভাড়া — **logistics bill** (ফ্রেইট + লোডিং + VAT), তারপর বিল থেকে পরিশোধ।\nএকই ফ্রেইট বোতল সাপ্লায়ারের ক্রয় বিলে তুলবেন না।",
            ],
        ],
        [
            'type' => 'tips',
            'title' => ['en' => 'Keep POD and cost in sync', 'bn' => 'POD ও খরচ সিঙ্কে রাখুন'],
            'items' => [
                ['en' => 'Finish **POD** so delivered qty is true before you judge route profit.', 'bn' => 'রুট লাভ দেখার আগে **POD** শেষ করুন।'],
                ['en' => 'Open **Route cost vs sales** after a week of trips — not after a single load sheet.', 'bn' => 'এক সপ্তাহের ট্রিপের পর **Route cost vs sales** খুলুন — এক লোড শিটের পর নয়।'],
                ['en' => 'Rate cards speed data entry; they do not replace the logistics bill.', 'bn' => 'রেট কার্ড এন্ট্রি দ্রুত করে; বিলের বিকল্প নয়।'],
            ],
        ],
    ],
];
