<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Set tax class', 'bn' => 'ট্যাক্স ক্লাস'], 'body' => ['en' => 'Each product needs a tax class for correct VAT on invoices. Wrong class = wrong NBR figures.', 'bn' => 'প্রতি পণ্যে ট্যাক্স ক্লাস লাগে সঠিক VAT-এর জন্য। ভুল ক্লাস = ভুল NBR হিসাব।'], 'path' => '/admin/tax-classes'],
        ['title' => ['en' => 'Packaging types', 'bn' => 'প্যাকেজিং'], 'body' => ['en' => 'Define carton, bottle, bulk UOM conversions if you sell in multiple pack sizes.', 'bn' => 'কার্টন, বোতল, বাল্ক UOM রূপান্তর সংজ্ঞায়িত করুন।'], 'path' => '/admin/packaging'],
        ['title' => ['en' => 'Material categories', 'bn' => 'ম্যাটেরিয়াল ক্যাটাগরি'], 'body' => ['en' => 'Group raw items (bottles, caps, labels) so POs and stock views stay filterable.', 'bn' => 'কাঁচামাল গ্রুপ করুন (বোতল, ক্যাপ, লেবেল) যাতে PO ও স্টক ফিল্টার করা যায়।'], 'path' => '/admin/material-categories'],
    ],
    'examples_extra' => [
        ['title' => ['en' => 'Raw vs finished', 'bn' => 'কাঁচা বনাম তৈরি'], 'body' => ['en' => 'RM-WATER = bought and used in BOM. SAF-1L-BTL = sold to agents. Never sell a raw material SKU on a sales order unless it is a trading item.', 'bn' => 'RM-WATER = কিনে BOM-এ ব্যবহার। SAF-1L-BTL = এজেন্টকে বিক্রি। কাঁচামাল SKU বিক্রয় অর্ডারে দেবেন না (ট্রেডিং না হলে)।']],
    ],
    'faqs' => [
        ['q' => ['en' => 'Can one SKU be both material and product?', 'bn' => 'এক SKU কাঁচামাল ও পণ্য দুটোই হতে পারে?'], 'a' => ['en' => 'No. Keep separate SKUs. Materials go to BOM/PO; products go to sales and manufacturing output.', 'bn' => 'না। আলাদা SKU রাখুন। কাঁচামাল BOM/PO-তে; পণ্য বিক্রয় ও উৎপাদন আউটপুটে।']],
        ['q' => ['en' => 'Why does invoice VAT look wrong?', 'bn' => 'ইনভয়েস VAT ভুল কেন?'], 'a' => ['en' => 'Check the product tax class first — VAT is pulled from there on every invoice line.', 'bn' => 'আগে পণ্যের ট্যাক্স ক্লাস দেখুন — VAT সেখান থেকে আসে।']],
    ],
    'glossary' => [
        ['term' => ['en' => 'SKU', 'bn' => 'SKU'], 'def' => ['en' => 'Stock Keeping Unit — unique code; never duplicate.', 'bn' => 'স্টক কিপিং ইউনিট — অনন্য কোড।']],
        ['term' => ['en' => 'UOM', 'bn' => 'UOM'], 'def' => ['en' => 'Unit of measure — pcs, L, kg, carton.', 'bn' => 'পরিমাপের একক — পিস, লিটার, কেজি, কার্টন।']],
    ],
];
