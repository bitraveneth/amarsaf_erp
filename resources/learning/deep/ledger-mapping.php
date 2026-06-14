<?php

/**
 * Transaction → ledger reference for client demos and finance training.
 */
return [
    'audience' => [
        'en' => 'Owners, accounts officers, and anyone asked “where does this money go?”',
        'bn' => 'মালিক, হিসাব কর্মকর্তা, ও যে কেউ জিজ্ঞেস করে “এই টাকা কোন লেজারে যায়?”',
    ],
    'technical_tab' => [
        'en' => 'Full transaction map',
        'bn' => 'সম্পূর্ণ লেনদেন মানচিত্র',
    ],
    'flowchart_technical' => [
        'en' => <<<'MERMAID'
flowchart LR
    subgraph posts ["Posts to GL automatically"]
        INV[Sales invoice]
        RCT[Customer receipt]
        BILL[Supplier bill]
        PAY[Bill payment]
        EXP[Expense]
        PAYR[Payroll]
        GRN[GRN approved]
        PRD[Production complete]
        COM[Commission]
    end
    subgraph no_gl ["Operational only — no GL"]
        PO[Purchase order]
        SO[Sales order draft]
        DEL[Delivery in transit]
    end
    posts --> COA[Chart of accounts]
MERMAID,
        'bn' => <<<'MERMAID'
flowchart LR
    subgraph posts ["স্বয়ংক্রিয় GL পোস্ট"]
        INV[বিক্রয় ইনভয়েস]
        RCT[গ্রাহক রসিদ]
        BILL[সাপ্লায়ার বিল]
        PAY[বিল পেমেন্ট]
        EXP[খরচ]
        PAYR[পে-রোল]
        GRN[GRN অনুমোদন]
        PRD[উৎপাদন সম্পন্ন]
        COM[কমিশন]
    end
    subgraph no_gl ["অপারেশনাল — GL নয়"]
        PO[ক্রয় অর্ডার]
        SO[বিক্রয় অর্ডার ড্রাফট]
        DEL[ডেলিভারি চলমান]
    end
    posts --> COA[চার্ট অফ অ্যাকাউন্টস]
MERMAID,
    ],
    'deep_sections' => [
        [
            'id' => 'explain',
            'title' => [
                'en' => 'How ledger routing works (simple)',
                'bn' => 'লেজার রাউটিং কীভাবে কাজ করে (সহজ)',
            ],
            'type' => 'prose',
            'body' => [
                'en' => "When you save a **finance document** (invoice, bill, expense, payroll, etc.), Saf ERP creates a **balanced journal entry**: total debits = total credits.\n\n**Three rules to remember:**\n\n1. **Sales side** — Invoice increases what agents owe you (Trade debtors). Receipt moves money to Bank and clears debtors.\n2. **Purchase side** — Supplier bill increases what you owe (Trade creditors). Payment clears creditors and reduces Bank.\n3. **Operating costs** — Expenses and payroll debit an **expense ledger** (from category mapping) and credit Bank, Cash, or a **payable** if not paid yet.\n\nUse **Chart of accounts → Balances** to see live ledger totals. Use **Journal entries** to audit any posting.",
                'bn' => "**আর্থিক ডকুমেন্ট** (ইনভয়েস, বিল, খরচ, পে-রোল ইত্যাদি) সেভ করলে Saf ERP **ভারসাম্যপূর্ণ জার্নাল** তৈরি করে: মোট ডেবিট = মোট ক্রেডিট।\n\n**তিনটি নিয়ম:**\n\n1. **বিক্রয়** — ইনভয়েসে এজেন্টের দেনা (Trade debtors) বাড়ে। রসিদে Bank বাড়ে, debtors কমে।\n2. **ক্রয়** — সাপ্লায়ার বিলে আপনার দেনা (Trade creditors) বাড়ে। পেমেন্টে creditors কমে, Bank কমে।\n3. **খরচ** — Expense ও payroll **expense ledger**-এ ডেবিট (ক্যাটাগরি ম্যাপিং), Bank/Cash/Payable-এ ক্রেডিট।\n\n**Chart of accounts → Balances**-এ লাইভ ব্যালেন্স দেখুন। **Journal entries**-এ নিরীক্ষা করুন।",
            ],
        ],
        [
            'id' => 'master-map',
            'title' => [
                'en' => 'Master table — every transaction and its ledger',
                'bn' => 'মাস্টার টেবিল — প্রতিটি লেনদেন ও লেজার',
            ],
            'type' => 'mapping_table',
            'intro' => [
                'en' => 'Use this table when a client asks “where does this go in the books?” Documents marked operational do not post until the next step.',
                'bn' => 'ক্লায়েন্ট জিজ্ঞেস করলে “বইতে কোথায় যায়?” — এই টেবিল ব্যবহার করুন। অপারেশনাল ডকুমেন্ট পরের ধাপ পর্যন্ত GL-এ যায় না।',
            ],
            'rows' => [
                ['action' => ['en' => 'Sales invoice', 'bn' => 'বিক্রয় ইনভয়েস'], 'when' => ['en' => 'Issued from delivered order', 'bn' => 'ডেলিভার্ড অর্ডার থেকে'], 'debit' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors'], 'credit' => ['en' => 'Product sales + VAT payable', 'bn' => 'Product sales + VAT payable']],
                ['action' => ['en' => 'Withholding on invoice', 'bn' => 'ইনভয়েসে উৎসে কর'], 'when' => ['en' => 'Agent has WHT %', 'bn' => 'এজেন্টে WHT %'], 'debit' => ['en' => 'WHT receivable', 'bn' => 'WHT receivable'], 'credit' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors']],
                ['action' => ['en' => 'Customer receipt', 'bn' => 'গ্রাহক রসিদ'], 'when' => ['en' => 'Payment recorded on invoice', 'bn' => 'ইনভয়েসে পেমেন্ট'], 'debit' => ['en' => 'Bank (default BRAC)', 'bn' => 'Bank (BRAC)'], 'credit' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors']],
                ['action' => ['en' => 'Credit note', 'bn' => 'ক্রেডিট নোট'], 'when' => ['en' => 'Return or adjustment', 'bn' => 'ফেরত বা সমন্বয়'], 'debit' => ['en' => 'Sales returns (+ VAT)', 'bn' => 'Sales returns (+ VAT)'], 'credit' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors']],
                ['action' => ['en' => 'Agent advance given', 'bn' => 'এজেন্ট অগ্রিম'], 'when' => ['en' => 'Advance saved', 'bn' => 'অগ্রিম সেভ'], 'debit' => ['en' => 'Bank', 'bn' => 'Bank'], 'credit' => ['en' => 'Agent advances', 'bn' => 'Agent advances']],
                ['action' => ['en' => 'Advance applied', 'bn' => 'অগ্রিম প্রয়োগ'], 'when' => ['en' => 'Auto on invoice', 'bn' => 'ইনভয়েসে স্বয়ং'], 'debit' => ['en' => 'Agent advances', 'bn' => 'Agent advances'], 'credit' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors']],
                ['action' => ['en' => 'Purchase bill (non-stock)', 'bn' => 'ক্রয় বিল (নন-স্টক)'], 'when' => ['en' => 'Bill saved', 'bn' => 'বিল সেভ'], 'debit' => ['en' => 'Purchases + Input VAT', 'bn' => 'Purchases + Input VAT'], 'credit' => ['en' => 'Trade creditors', 'bn' => 'Trade creditors']],
                ['action' => ['en' => 'Purchase bill (stock)', 'bn' => 'ক্রয় বিল (স্টক)'], 'when' => ['en' => 'Bill saved', 'bn' => 'বিল সেভ'], 'debit' => ['en' => 'GRNI accrual + Input VAT', 'bn' => 'GRNI + Input VAT'], 'credit' => ['en' => 'Trade creditors', 'bn' => 'Trade creditors']],
                ['action' => ['en' => 'Supplier payment', 'bn' => 'সাপ্লায়ার পেমেন্ট'], 'when' => ['en' => 'Bill paid', 'bn' => 'বিল পরিশোধ'], 'debit' => ['en' => 'Trade creditors', 'bn' => 'Trade creditors'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'GRN (goods receipt)', 'bn' => 'GRN'], 'when' => ['en' => 'QC approved, inventory GL on', 'bn' => 'QC অনুমোদন, inventory GL'], 'debit' => ['en' => 'Raw materials / FG inventory', 'bn' => 'RM / FG inventory'], 'credit' => ['en' => 'GRNI accrual', 'bn' => 'GRNI accrual']],
                ['action' => ['en' => 'Production complete', 'bn' => 'উৎপাদন সম্পন্ন'], 'when' => ['en' => 'Stock confirmed', 'bn' => 'স্টক নিশ্চিত'], 'debit' => ['en' => 'WIP → FG', 'bn' => 'WIP → FG'], 'credit' => ['en' => 'Raw materials consumed', 'bn' => 'RM consumed']],
                ['action' => ['en' => 'COGS on invoice', 'bn' => 'ইনভয়েসে COGS'], 'when' => ['en' => 'Invoice issued', 'bn' => 'ইনভয়েস'], 'debit' => ['en' => 'Raw material consumption', 'bn' => 'RM consumption'], 'credit' => ['en' => 'Finished goods', 'bn' => 'Finished goods']],
                ['action' => ['en' => 'Expense (paid by bank)', 'bn' => 'খরচ (ব্যাংক)'], 'when' => ['en' => 'Expense saved', 'bn' => 'Expense সেভ'], 'debit' => ['en' => 'Category expense ledger', 'bn' => 'ক্যাটাগরি expense'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'Expense (unpaid / accrued)', 'bn' => 'খরচ (বকেয়া)'], 'when' => ['en' => 'Payment type = payable', 'bn' => 'Payment = payable'], 'debit' => ['en' => 'Category expense ledger', 'bn' => 'ক্যাটাগরি expense'], 'credit' => ['en' => 'Utility / rent / salary payable', 'bn' => 'Payable ledger']],
                ['action' => ['en' => 'Payroll / salary distribution', 'bn' => 'পে-রোল'], 'when' => ['en' => 'Distribution saved', 'bn' => 'Distribution সেভ'], 'debit' => ['en' => 'Salaries & wages', 'bn' => 'Salaries & wages'], 'credit' => ['en' => 'Bank / cash / salary payable', 'bn' => 'Bank / cash / payable']],
                ['action' => ['en' => 'Commission accrual', 'bn' => 'কমিশন accrual'], 'when' => ['en' => 'Settlement accrued', 'bn' => 'Settlement accrued'], 'debit' => ['en' => 'Commission expense', 'bn' => 'Commission expense'], 'credit' => ['en' => 'Commission payable', 'bn' => 'Commission payable']],
                ['action' => ['en' => 'Commission payment', 'bn' => 'কমিশন পেমেন্ট'], 'when' => ['en' => 'Settlement paid', 'bn' => 'Settlement paid'], 'debit' => ['en' => 'Commission payable', 'bn' => 'Commission payable'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'Marketing campaign', 'bn' => 'মার্কেটিং'], 'when' => ['en' => 'Campaign cost posted', 'bn' => 'Campaign cost'], 'debit' => ['en' => 'Marketing expense', 'bn' => 'Marketing expense'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'Customer gift', 'bn' => 'গিফট'], 'when' => ['en' => 'Gift status = given', 'bn' => 'Gift given'], 'debit' => ['en' => 'Selling & distribution', 'bn' => 'Selling & distribution'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'Stock write-off', 'bn' => 'স্টক write-off'], 'when' => ['en' => 'Adjustment / movement', 'bn' => 'Adjustment'], 'debit' => ['en' => 'Inventory write-off', 'bn' => 'Write-off expense'], 'credit' => ['en' => 'Inventory asset', 'bn' => 'Inventory']],
                ['action' => ['en' => 'Manual journal', 'bn' => 'ম্যানুয়াল জার্নাল'], 'when' => ['en' => 'User posts draft', 'bn' => 'User posts'], 'debit' => ['en' => 'User-selected ledgers', 'bn' => 'বেছে নেওয়া ledger'], 'credit' => ['en' => 'User-selected ledgers', 'bn' => 'বেছে নেওয়া ledger']],
            ],
        ],
        [
            'id' => 'no-gl',
            'title' => [
                'en' => 'What does NOT post to the ledger',
                'bn' => 'যা GL-এ পোস্ট হয় না',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => '**Purchase orders (draft/approved)** — stock changes only on GRN.', 'bn' => '**ক্রয় অর্ডার** — স্টক GRN-এ পরিবর্তন।'],
                ['en' => '**Sales orders (until invoiced)** — reserves stock but no revenue yet.', 'bn' => '**বিক্রয় অর্ডার** — স্টক রিজার্ভ, আয় এখনো নয়।'],
                ['en' => '**Deliveries in transit** — stock leaves warehouse; revenue waits for invoice.', 'bn' => '**ডেলিভারি চলমান** — স্টক বের; আয় ইনভয়েসে।'],
                ['en' => '**HR records** (contracts, leaves, badges) — only **salary distributions** post payroll.', 'bn' => '**HR রেকর্ড** — শুধু **salary distributions** পে-রোল পোস্ট করে।'],
                ['en' => '**Stock transfers between warehouses** — moves qty; usually no P&L unless write-off.', 'bn' => '**গুদাম间 transfer** — পরিমাণ সরে; সাধারণত P&L নয়।'],
                ['en' => '**Sales targets, MRP suggestions** — planning only.', 'bn' => '**Sales targets, MRP** — পরিকল্পনা মাত্র।'],
            ],
        ],
        [
            'id' => 'expense-categories',
            'title' => [
                'en' => 'Expense category → ledger mapping',
                'bn' => 'Expense ক্যাটাগরি → লেজার',
            ],
            'type' => 'mapping_table',
            'intro' => [
                'en' => 'Configure at Accounting → Expense category mapping (`/admin/expense-categories`). User also picks payment: bank, cash, or accrued.',
                'bn' => 'Accounting → Expense category mapping (`/admin/expense-categories`)। ব্যবহারকারী payment: bank, cash, বা accrued বেছে নেয়।',
            ],
            'rows' => [
                ['action' => ['en' => 'General', 'bn' => 'General'], 'when' => ['en' => 'Paid', 'bn' => 'Paid'], 'debit' => ['en' => 'Selling & distribution', 'bn' => 'Selling & distribution'], 'credit' => ['en' => 'Bank', 'bn' => 'Bank']],
                ['action' => ['en' => 'General', 'bn' => 'General'], 'when' => ['en' => 'Unpaid (payable)', 'bn' => 'Unpaid'], 'debit' => ['en' => 'Selling & distribution', 'bn' => 'Selling & distribution'], 'credit' => ['en' => 'Trade creditors', 'bn' => 'Trade creditors']],
                ['action' => ['en' => 'Marketing', 'bn' => 'Marketing'], 'when' => ['en' => 'Any payment', 'bn' => 'Any'], 'debit' => ['en' => 'Marketing expense', 'bn' => 'Marketing expense'], 'credit' => ['en' => 'Bank or payable', 'bn' => 'Bank / payable']],
                ['action' => ['en' => 'Utilities', 'bn' => 'Utilities'], 'when' => ['en' => 'Unpaid', 'bn' => 'Unpaid'], 'debit' => ['en' => 'Utilities expense', 'bn' => 'Utilities expense'], 'credit' => ['en' => 'Utility bill payable', 'bn' => 'Utility payable']],
                ['action' => ['en' => 'Salary (expense form)', 'bn' => 'Salary'], 'when' => ['en' => 'Unpaid', 'bn' => 'Unpaid'], 'debit' => ['en' => 'Salaries & wages', 'bn' => 'Salaries & wages'], 'credit' => ['en' => 'Salary payable', 'bn' => 'Salary payable']],
                ['action' => ['en' => 'Rent', 'bn' => 'Rent'], 'when' => ['en' => 'Unpaid', 'bn' => 'Unpaid'], 'debit' => ['en' => 'Office rent', 'bn' => 'Office rent'], 'credit' => ['en' => 'Rent payable', 'bn' => 'Rent payable']],
                ['action' => ['en' => 'Travel & delivery', 'bn' => 'Travel'], 'when' => ['en' => 'Any', 'bn' => 'Any'], 'debit' => ['en' => 'Delivery expense', 'bn' => 'Delivery expense'], 'credit' => ['en' => 'Bank or payable', 'bn' => 'Bank / payable']],
            ],
        ],
        [
            'id' => 'example-desco',
            'icon' => 'journal',
            'title' => [
                'en' => 'Client example: DESCO bill ৳15,000 paid by bank',
                'bn' => 'ক্লায়েন্ট উদাহরণ: DESCO ৳১৫,০০০ ব্যাংকে',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Category = Utilities. Payment = Bank. One journal entry on save.',
                'bn' => 'Category = Utilities। Payment = Bank। সেভে এক জার্নাল।',
            ],
            'rows' => [
                ['account' => ['en' => 'Utilities expense', 'bn' => 'Utilities expense'], 'debit' => ['en' => '৳15,000', 'bn' => '৳১৫,০০০'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Bank (BRAC)', 'bn' => 'Bank (BRAC)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳15,000', 'bn' => '৳১৫,০০০']],
            ],
        ],
        [
            'id' => 'example-agent',
            'icon' => 'journal',
            'title' => [
                'en' => 'Client example: agent sale ৳100,000 net + 15% VAT',
                'bn' => 'ক্লায়েন্ট উদাহরণ: ৳১,০০,০০০ নেট + ১৫% VAT',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Invoice posts revenue and VAT. If 3% withholding on net (৳3,000), cash expectation is ৳112,500 − ৳3,000 = ৳109,500 after full receipt.',
                'bn' => 'ইনভয়েসে revenue ও VAT। ৩% WHT (৳৩,০০০) হলে নগদ প্রত্যাশা ৳১০৯,৫০০।',
            ],
            'rows' => [
                ['account' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors'], 'debit' => ['en' => '৳115,000 (net + VAT)', 'bn' => '৳১১৫,০০০'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Product sales', 'bn' => 'Product sales'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳100,000 net', 'bn' => '৳১,০০,০০০']],
                ['account' => ['en' => 'VAT payable', 'bn' => 'VAT payable'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳15,000', 'bn' => '৳১৫,০০০']],
                ['account' => ['en' => 'WHT receivable (if agent has 3%)', 'bn' => 'WHT receivable'], 'debit' => ['en' => '৳3,000', 'bn' => '৳৩,০০০'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Trade debtors (WHT offset)', 'bn' => 'Trade debtors'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳3,000', 'bn' => '৳৩,০০০']],
            ],
        ],
    ],
    'report_links' => [
        ['title' => ['en' => 'Chart of accounts (Balances)', 'bn' => 'COA Balances'], 'path' => '/admin/accounts', 'desc' => ['en' => 'See ledger totals live', 'bn' => 'লাইভ ledger ব্যালেন্স']],
        ['title' => ['en' => 'Journal entries', 'bn' => 'Journal entries'], 'path' => '/admin/journals', 'desc' => ['en' => 'Audit trail for every posting', 'bn' => 'প্রতিটি পোস্টের নিরীক্ষা']],
        ['title' => ['en' => 'Expense category mapping', 'bn' => 'Expense mapping'], 'path' => '/admin/expense-categories', 'desc' => ['en' => 'Category → expense ledger', 'bn' => 'ক্যাটাগরি → expense ledger']],
        ['title' => ['en' => 'Trial balance', 'bn' => 'Trial balance'], 'path' => '/admin/reports/trial-balance', 'desc' => ['en' => 'Verify debits = credits', 'bn' => 'ডেবিট = ক্রেডিট যাচাই']],
    ],
    'faqs' => [
        [
            'q' => ['en' => 'How do I answer “where does my electricity bill go?”', 'bn' => '“বিদ্যুৎ বিল কোথায় যায়?” — কী বলব?'],
            'a' => ['en' => 'Utilities category → Dr Utilities expense, Cr Bank (if paid) or Cr Utility bill payable (if unpaid). Show them Chart of accounts Balances view on Utilities expense.', 'bn' => 'Utilities category → Dr Utilities expense, Cr Bank (paid) বা Cr Utility payable (unpaid)। COA Balances-এ Utilities expense দেখান।'],
        ],
        [
            'q' => ['en' => 'Why is there no GL entry for my sales order?', 'bn' => 'বিক্রয় অর্ডারে GL এন্ট্রি নেই কেন?'],
            'a' => ['en' => 'Revenue posts on **invoice**, not on order or delivery. This matches standard accounting — you earn revenue when you bill delivered goods.', 'bn' => 'আয় **ইনভয়েস**-এ পোস্ট — অর্ডার বা ডেলিভারিতে নয়। ডেলিভার্ড পণ্য বিল করলেই revenue।'],
        ],
        [
            'q' => ['en' => 'Should I enter payroll in Expenses AND Salary distributions?', 'bn' => 'Expenses ও Salary distributions দুটোতেই পে-রোল?'],
            'a' => ['en' => 'No — use **Salary distributions** for payroll GL. Expenses “salary” category is for one-off salary-like costs if needed. Double entry duplicates payroll on P&L.', 'bn' => 'না — **Salary distributions** ব্যবহার করুন। দুটোতেই দিলে P&L-এ পে-রোল দ্বিগুণ হয়।'],
        ],
    ],
    'glossary' => [
        ['term' => ['en' => 'Trade debtors', 'bn' => 'Trade debtors'], 'def' => ['en' => 'Accounts receivable — what agents/customers owe you after invoice.', 'bn' => 'পাওনা — ইনভয়েসের পর এজেন্টের দেনা।']],
        ['term' => ['en' => 'Trade creditors', 'bn' => 'Trade creditors'], 'def' => ['en' => 'Accounts payable — what you owe suppliers after bill.', 'bn' => 'দেনা — বিলের পর সাপ্লায়ারকে যা দিতে হবে।']],
        ['term' => ['en' => 'GRNI', 'bn' => 'GRNI'], 'def' => ['en' => 'Goods received not invoiced — bridge between GRN stock and supplier bill.', 'bn' => 'মাল এসেছে, বিল হয়নি — GRN ও বিলের মধ্যবর্তী accrual।']],
    ],
];
