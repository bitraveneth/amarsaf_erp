<?php

return [
    'technical_tab' => ['en' => 'PO & GRN reference', 'bn' => 'PO ও GRN রেফারেন্স'],
    'steps_extra' => [
        ['title' => ['en' => 'Add supplier', 'bn' => 'সাপ্লায়ার'], 'body' => ['en' => 'Register supplier with payment terms before first PO.', 'bn' => 'প্রথম PO-র আগে সাপ্লায়ার নিবন্ধন ও পেমেন্ট টার্ম।'], 'path' => '/admin/suppliers/create'],
        ['title' => ['en' => 'GRN approval', 'bn' => 'GRN অনুমোদন'], 'body' => ['en' => 'Some GRNs need approver sign-off before stock posts. Check pending tabs.', 'bn' => 'কিছু GRN-এ অনুমোদন ছাড়া স্টক পোস্ট হয় না।'], 'path' => '/admin/goods-receipts'],
        ['title' => ['en' => 'Supplier bill', 'bn' => 'সাপ্লায়ার বিল'], 'body' => ['en' => 'After GRN, record supplier invoice for AP and input VAT.', 'bn' => 'GRN-এর পর সাপ্লায়ার ইনভয়েস AP ও ইনপুট VAT-এর জন্য।'], 'path' => '/admin/bills'],
    ],
    'deep_sections' => [
        [
            'id' => 'po-status',
            'title' => ['en' => 'PO status meanings', 'bn' => 'PO স্ট্যাটাস'],
            'type' => 'checklist',
            'items' => [
                ['en' => '**Draft** — editable, no commitment.', 'bn' => '**Draft** — সম্পাদনাযোগ্য।'],
                ['en' => '**Approved** — ready to receive on GRN.', 'bn' => '**Approved** — GRN গ্রহণের জন্য প্রস্তুত।'],
                ['en' => '**Partial received** — some lines still open.', 'bn' => '**Partial received** — কিছু লাইন খোলা।'],
                ['en' => '**Received** — all stock lines complete.', 'bn' => '**Received** — সব স্টক লাইন সম্পূর্ণ।'],
            ],
        ],
    ],
    'faqs' => [
        ['q' => ['en' => 'GRN without PO?', 'bn' => 'PO ছাড়া GRN?'], 'a' => ['en' => 'Possible for ad-hoc receipts but PO-linked GRN is best for audit trail and three-way match.', 'bn' => 'অ্যাড-হক গ্রহণ সম্ভব কিন্তু PO-লিংকড GRN নিরীক্ষার জন্য ভালো।']],
    ],
    'glossary' => [
        ['term' => ['en' => 'Three-way match', 'bn' => 'থ্রি-ওয়ে ম্যাচ'], 'def' => ['en' => 'PO qty/price vs GRN qty vs supplier bill — should align.', 'bn' => 'PO বনাম GRN বনাম সাপ্লায়ার বিল — মিলতে হবে।']],
    ],
];
