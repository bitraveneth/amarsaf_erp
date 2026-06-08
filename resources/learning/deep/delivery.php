<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Vehicle load', 'bn' => 'যান লোড'], 'body' => ['en' => 'Plan capacity per trip; link deliveries to vehicle load sheet.', 'bn' => 'প্রতি ট্রিপে ক্যাপাসিটি; ডেলিভারি যান লোড শিটে।'], 'path' => '/admin/vehicle-load'],
        ['title' => ['en' => 'Packing slip', 'bn' => 'প্যাকিং স্লিপ'], 'body' => ['en' => 'Print packing slip for driver before dispatch.', 'bn' => 'ডিসপ্যাচের আগে ড্রাইভারের প্যাকিং স্লিপ।'], 'path' => '/admin/deliveries/packing-slips'],
    ],
    'faqs' => [
        ['q' => ['en' => 'Short delivery on POD?', 'bn' => 'POD-এ কম ডেলিভারি?'], 'a' => ['en' => 'Record short qty on POD. Invoice may reflect delivered qty only; returns/credit note for disputes.', 'bn' => 'POD-এ কম পরিমাণ লিখুন। ইনভয়েস সাধারণত ডেলিভার্ড পরিমাণে; বিতর্কে ক্রেডিট নোট।']],
    ],
];
