<?php

/**
 * Merged into Learning Hub "Accounting module test" course.
 */
return [
    'audience' => [
        'en' => 'Owners, accounts officers, and anyone who needs to trust the books before month-end',
        'bn' => 'মালিক, অ্যাকাউন্টস অফিসার, এবং মাস শেষের আগে বই বিশ্বাস করতে চান যারা',
    ],
    'deep_sections' => [
        [
            'id' => 'pass-fail',
            'icon' => 'shield',
            'title' => [
                'en' => 'Pass / fail scorecard',
                'bn' => 'পাস / ফেল স্কোরকার্ড',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => '**Expense save** creates a **posted** journal (not draft).', 'bn' => '**খরচ সেভ** → **posted** জার্নাল (ড্রাফট নয়)।'],
                ['en' => '**Dashboard Expenses** rises by the same amount.', 'bn' => '**Dashboard Expenses** একই অঙ্কে বাড়ে।'],
                ['en' => '**Dashboard Net profit** falls by the same amount (Sales − Expenses − Payroll).', 'bn' => '**Net profit** একই অঙ্কে কমে।'],
                ['en' => '**Income statement** includes the expense in the period.', 'bn' => '**Income statement**-এ পিরিয়ডে খরচ আছে।'],
                ['en' => '**Cash / bank** on Chart of accounts falls if paid now.', 'bn' => 'পেইড হলে COA-তে **Cash / bank** কমে।'],
                ['en' => '**Reverse** of a posted journal returns cash to the prior balance.', 'bn' => 'জার্নাল **Reverse** করলে নগদ আগের ব্যালেন্সে ফেরে।'],
            ],
        ],
        [
            'id' => 'two-layers',
            'icon' => 'journal',
            'title' => [
                'en' => 'Why dashboard and P&L can disagree',
                'bn' => 'ড্যাশবোর্ড ও P&L কেন আলাদা হতে পারে',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => 'Accounting dashboard reads **invoice / expense / payroll tables** (fast estimate).', 'bn' => 'ড্যাশবোর্ড **ইনভয়েস / খরচ / পে-রোল** টেবিল পড়ে (দ্রুত অনুমান)।'],
                ['en' => 'Income statement reads **posted journal lines** only (formal books).', 'bn' => 'Income statement শুধু **পোস্ট করা জার্নাল** পড়ে।'],
                ['en' => 'If expense is saved but journal failed → dashboard can move, P&L may not. Fix with Sync / open period / missing account.', 'bn' => 'খরচ সেভ কিন্তু জার্নাল ব্যর্থ → ড্যাশবোর্ড বদলাতে পারে, P&L নাও পারে। Sync / খোলা মাস / অ্যাকাউন্ট ঠিক করুন।'],
                ['en' => 'Trust month-end numbers from **P&L + Trial balance + AR/AP aging**, not only the dashboard.', 'bn' => 'মাস শেষে **P&L + Trial balance + AR/AP aging** বিশ্বাস করুন — শুধু ড্যাশবোর্ড নয়।'],
            ],
        ],
    ],
    'report_links' => [
        ['title' => ['en' => 'Accounting dashboard', 'bn' => 'হিসাব ড্যাশবোর্ড'], 'path' => '/admin/accounting-dashboard', 'desc' => ['en' => 'Write down Expenses and Net profit first', 'bn' => 'আগে Expenses ও Net profit লিখুন']],
        ['title' => ['en' => 'Expenses', 'bn' => 'খরচ'], 'path' => '/admin/expenses', 'desc' => ['en' => 'Post the ৳1,000 test expense', 'bn' => '৳১,০০০ টেস্ট খরচ পোস্ট']],
        ['title' => ['en' => 'Income statement', 'bn' => 'আয় বিবৃতি'], 'path' => '/admin/reports/pl', 'desc' => ['en' => 'Confirm formal P&L moved', 'bn' => 'আনুষ্ঠানিক P&L বদলেছে কিনা']],
        ['title' => ['en' => 'Journal entries', 'bn' => 'জার্নাল'], 'path' => '/admin/journals', 'desc' => ['en' => 'Find posted voucher + reverse test', 'bn' => 'পোস্ট করা ভাউচার ও রিভার্স পরীক্ষা']],
        ['title' => ['en' => 'Chart of accounts', 'bn' => 'চার্ট অফ অ্যাকাউন্টস'], 'path' => '/admin/accounts', 'desc' => ['en' => 'Cash / bank balance check', 'bn' => 'নগদ / ব্যাংক ব্যালেন্স']],
        ['title' => ['en' => 'Trial balance', 'bn' => 'ট্রায়াল ব্যালেন্স'], 'path' => '/admin/reports/trial-balance', 'desc' => ['en' => 'Debits must equal credits', 'bn' => 'ডেবিট = ক্রেডিট']],
    ],
    'faqs' => [
        [
            'q' => ['en' => 'Do I need to delete the test expense after?', 'bn' => 'টেস্ট খরচ পরে মুছতে হবে?'],
            'a' => [
                'en' => 'On **demo**, leave it or reseed. On **live**, reverse the journal (or post an offsetting journal) — do not leave fake costs in the year.',
                'bn' => '**ডেমো**-তে রেখে দিতে বা reseed করতে পারেন। **লাইভ**-এ জার্নাল রিভার্স করুন — বছরে ভুয়া খরচ রাখবেন না।',
            ],
        ],
        [
            'q' => ['en' => 'Bank recon passed but cash is still wrong — why?', 'bn' => 'ব্যাংক রিকন পাস তবু নগদ ভুল — কেন?'],
            'a' => [
                'en' => 'Reconciliation only marks that a receipt/payment appeared on the bank. It does not fix missing fees or wrong journals. Post the missing expense/journal first, then tick reconcile.',
                'bn' => 'রিকন শুধু বলে সারিটি ব্যাংকে দেখা গেছে। বাদ পড়া চার্জ বা ভুল জার্নাল ঠিক করে না। আগে Expense/জার্নাল, পরে টিক।',
            ],
        ],
    ],
];
