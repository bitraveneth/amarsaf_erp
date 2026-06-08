<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Commission rules', 'bn' => 'কমিশন নিয়ম'], 'body' => ['en' => 'Set % or fixed commission per product/zone. Settlements run from delivered/invoiced orders.', 'bn' => 'পণ্য/জোন অনুযায়ী % বা ফিক্সড কমিশন। ডেলিভার্ড/ইনভয়েস অর্ডার থেকে সেটেলমেন্ট।'], 'path' => '/admin/commission-rules'],
        ['title' => ['en' => 'Agent advances', 'bn' => 'এজেন্ট অগ্রিম'], 'body' => ['en' => 'Track money given to agents upfront; applied against future invoices.', 'bn' => 'আগে দেওয়া টাকা ট্র্যাক; পরের ইনভয়েসে কাটা হয়।'], 'path' => '/admin/agent-advances'],
    ],
    'tips_extra' => [
        ['en' => 'Set credit limit — system can block orders over limit.', 'bn' => 'ক্রেডিট লিমিট দিন — লিমিটের বেশি অর্ডার ব্লক হতে পারে।'],
        ['en' => 'Withholding rate on agent affects cash collection on invoices (see Accounting).', 'bn' => 'এজেন্টে উৎসে কর হার ইনভয়েসে নগদ আদায়ে প্রভাব ফেলে।'],
    ],
    'faqs' => [
        ['q' => ['en' => 'Agent inactive — what happens?', 'bn' => 'এজেন্ট নিষ্ক্রিয় হলে?'], 'a' => ['en' => 'Cannot select on new sales orders. Existing open orders stay until closed.', 'bn' => 'নতুন বিক্রয় অর্ডারে বেছে নেওয়া যায় না। খোলা অর্ডার থাকে যতক্ষণ বন্ধ না হয়।']],
    ],
];
