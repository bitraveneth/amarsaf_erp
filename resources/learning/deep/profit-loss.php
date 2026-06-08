<?php

/**
 * How profit & loss is calculated — one-unit produce-to-sell walkthrough.
 */
return [
    'audience' => [
        'en' => 'Accounts officers, managers, and tax advisors who need to see how one sold unit becomes profit on the P&L',
        'bn' => 'হিসাব কর্মকর্তা, ম্যানেজার ও কর পরামর্শক — এক বিক্রিত ইউনিট কীভাবে P&L-এ লাভ হয়',
    ],
    'technical_tab' => [
        'en' => 'Ledger entries & P&L math',
        'bn' => 'লেজার এন্ট্রি ও P&L হিসাব',
    ],
    'flowchart_technical' => [
        'en' => <<<'MERMAID'
flowchart LR
    subgraph cost ["Cost build-up"]
        RM["GRN raw materials"]
        WIP["Production run"]
        FG["FG stock 1310"]
    end
    subgraph sell ["Sale"]
        DEL["Order delivered"]
        INV["Sales invoice"]
        REV["Cr Sales Revenue 4000"]
        VAT["Cr VAT Payable 2000"]
        COGS["Dr COGS 5000"]
    end
    subgraph pl ["P and L report"]
        NS["Net sales"]
        GP["Gross profit"]
        OP["Operating costs"]
        NP["Net profit"]
    end
    RM --> WIP --> FG --> DEL --> INV
    INV --> REV
    INV --> VAT
    INV --> COGS
    REV --> NS
    NS --> GP
    COGS --> GP
    GP --> OP --> NP
MERMAID,
        'bn' => <<<'MERMAID'
flowchart LR
    subgraph cost ["খরচ গঠন"]
        RM["GRN কাঁচামাল"]
        WIP["উৎপাদন রান"]
        FG["FG স্টক 1310"]
    end
    subgraph sell ["বিক্রয়"]
        DEL["অর্ডার ডেলিভার্ড"]
        INV["বিক্রয় ইনভয়েস"]
        REV["Cr Sales Revenue 4000"]
        VAT["Cr VAT Payable 2000"]
        COGS["Dr COGS 5000"]
    end
    subgraph pl ["P&L রিপোর্ট"]
        NS["নেট বিক্রয়"]
        GP["মোট লাভ"]
        OP["অপারেটিং খরচ"]
        NP["নিট লাভ"]
    end
    RM --> WIP --> FG --> DEL --> INV
    INV --> REV
    INV --> VAT
    INV --> COGS
    REV --> NS
    NS --> GP
    COGS --> GP
    GP --> OP --> NP
MERMAID,
    ],
    'deep_sections' => [
        [
            'id' => 'story',
            'title' => [
                'en' => 'The idea in plain English',
                'bn' => 'সহজ ভাষায় ধারণা',
            ],
            'type' => 'prose',
            'body' => [
                'en' => "Profit is **not** “cash in the bank minus cash out”. In Saf ERP, profit on the **Profit & Loss** report (`/admin/reports/pl`) is built from **posted ledger accounts** in a period:\n\n1. **Net sales** — how much revenue you earned (excluding VAT).\n2. **Minus COGS** — what that sold stock cost you to make or buy.\n3. **= Gross profit** — margin on products themselves.\n4. **Minus operating costs** — commission, expenses, payroll, etc.\n5. **= Net profit** — bottom line for the period.\n\nVAT is collected for NBR — it flows through AR and VAT Payable but is **not** part of net sales or profit.\n\nBelow we follow **one finished unit** (1 carton SAF-1L-BTL) from raw material purchase → production → sale → P&L line.",
                'bn' => "লাভ মানে **“ব্যাংকে টাকা − বের হওয়া টাকা”** নয়। Saf ERP-তে **লাভ-ক্ষতি** রিপোর্ট (`/admin/reports/pl`) নির্দিষ্ট সময়ের **পোস্ট করা লেজার অ্যাকাউন্ট** থেকে গড়ে ওঠে:\n\n1. **নেট বিক্রয়** — আয় (VAT বাদে)।\n2. **বিয়োগ COGS** — বিক্রিত স্টক তৈরি/কেনতে খরচ।\n3. **= মোট লাভ** — পণ্যের মার্জিন।\n4. **বিয়োগ অপারেটিং খরচ** — কমিশন, খরচ, বেতন ইত্যাদি।\n5. **= নিট লাভ** — সময়ের নিট ফলাফল।\n\nVAT NBR-এর জন্য — AR ও VAT Payable দিয়ে যায়, **নেট বিক্রয় বা লাভের অংশ নয়**।\n\nনিচে **এক তৈরি ইউনিট** (১ কার্টন SAF-1L-BTL) কাঁচামাল → উৎপাদন → বিক্রয় → P&L লাইন পর্যন্ত দেখানো হয়েছে।",
            ],
        ],
        [
            'id' => 'unit-scenario',
            'title' => [
                'en' => 'Worked example — 1 unit produced and sold',
                'bn' => 'কাজের উদাহরণ — ১ ইউনিট উৎপাদন ও বিক্রয়',
            ],
            'type' => 'scenario',
            'intro' => [
                'en' => 'Assumptions: active BOM, inventory GL on, COGS at invoice time. Amounts in BDT per 1 carton (24×1L bottles).',
                'bn' => 'ধরে নেওয়া: সক্রিয় BOM, inventory GL চালু, ইনভয়েসে COGS। পরিমাণ BDT (১ কার্টন = ২৪×১L)।',
            ],
            'rows' => [
                ['label' => ['en' => 'BOM material cost (from production)', 'bn' => 'BOM কাঁচামাল খরচ (উৎপাদন থেকে)'], 'value' => ['en' => '৳42.00', 'bn' => '৳৪২.০০'], 'role' => 'cost'],
                ['label' => ['en' => 'FG moves to stock (account 1310)', 'bn' => 'FG স্টকে (অ্যাকাউন্ট ১৩১০)'], 'value' => ['en' => '৳42.00', 'bn' => '৳৪২.০০'], 'role' => 'cost'],
                ['label' => ['en' => 'Agent net selling price (excl. VAT)', 'bn' => 'এজেন্ট নেট বিক্রয় মূল্য (VAT বাদে)'], 'value' => ['en' => '৳80.00', 'bn' => '৳৮০.০০'], 'role' => 'revenue'],
                ['label' => ['en' => 'Output VAT 15%', 'bn' => 'আউটপুট VAT ১৫%'], 'value' => ['en' => '৳12.00', 'bn' => '৳১২.০০'], 'role' => 'vat'],
                ['label' => ['en' => 'Invoice total (AR debit)', 'bn' => 'ইনভয়েস মোট (AR ডেবিট)'], 'value' => ['en' => '৳92.00', 'bn' => '৳৯২.০০'], 'role' => 'neutral'],
                ['label' => ['en' => 'COGS posted on invoice (account 5000)', 'bn' => 'ইনভয়েসে COGS (অ্যাকাউন্ট ৫০০০)'], 'value' => ['en' => '৳42.00', 'bn' => '৳৪২.০০'], 'role' => 'cost'],
                ['label' => ['en' => 'Gross profit per unit', 'bn' => 'প্রতি ইউনিট মোট লাভ'], 'value' => ['en' => '৳38.00', 'bn' => '৳৩৮.০০'], 'role' => 'profit'],
                ['label' => ['en' => 'Agent commission 5% on net', 'bn' => 'এজেন্ট কমিশন ৫% (নেটে)'], 'value' => ['en' => '৳4.00', 'bn' => '৳৪.০০'], 'role' => 'cost'],
                ['label' => ['en' => 'Contribution before other overhead', 'bn' => 'অন্যান্য ওভারহেডের আগে অবদান'], 'value' => ['en' => '৳34.00', 'bn' => '৳৩৪.০০'], 'role' => 'profit'],
            ],
            'note' => [
                'en' => '**Gross profit per unit** = Net sales (৳80) − COGS (৳42) = **৳38**. Commission and monthly expenses are deducted on the full P&L for the period, not only this unit.',
                'bn' => '**প্রতি ইউনিট মোট লাভ** = নেট বিক্রয় (৳৮০) − COGS (৳৪২) = **৳৩৮**। কমিশন ও মাসিক খরচ পুরো P&L-এ বিয়োগ হয়, শুধু এই ইউনিটে নয়।',
            ],
        ],
        [
            'id' => 'timeline',
            'title' => [
                'en' => 'Step-by-step: what posts when (1 unit)',
                'bn' => 'ধাপে ধাপে: কখন কী পোস্ট হয় (১ ইউনিট)',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => '**GRN posted** — Dr Raw Materials Inventory (1300) ৳42, Cr GRNI (2190) ৳42. Stock exists; no P&L effect yet.', 'bn' => '**GRN পোস্ট** — Dr কাঁচামাল (1300) ৳৪২, Cr GRNI (2190) ৳৪২। স্টক আছে; P&L-এ এখনো প্রভাব নেই।'],
                ['en' => '**Production confirmed** — materials consumed from 1300, FG ৳42 added to Finished Goods (1310). Still balance sheet only.', 'bn' => '**উৎপাদন নিশ্চিত** — 1300 থেকে কাঁচামাল কাটা, FG ৳৪২ যোগ হয় 1310-এ। এখনও ব্যালেন্স শিট।'],
                ['en' => '**Order delivered + POD** — FG qty reduced in warehouse; no revenue yet.', 'bn' => '**অর্ডার ডেলিভার্ড + POD** — গুদামে FG কমে; আয় এখনো নয়।'],
                ['en' => '**Sales invoice issued** — Dr AR ৳92, Cr Sales Revenue ৳80, Cr VAT Payable ৳12. **Revenue hits P&L.**', 'bn' => '**বিক্রয় ইনভয়েস** — Dr AR ৳৯২, Cr Sales Revenue ৳৮০, Cr VAT Payable ৳১২। **আয় P&L-এ আসে।**'],
                ['en' => '**COGS journal (same invoice)** — Dr COGS ৳42, Cr Finished Goods ৳42. **Expense hits P&L** — gross profit ৳38 on this unit.', 'bn' => '**COGS জার্নাল (একই ইনভয়েস)** — Dr COGS ৳৪২, Cr FG ৳৪২। **খরচ P&L-এ** — এই ইউনিটে মোট লাভ ৳৩৮।'],
                ['en' => '**Customer receipt** — Dr Bank ৳92, Cr AR ৳92. Cash moves; **no extra P&L line** (you already recognised revenue).', 'bn' => '**গ্রাহক রসিদ** — Dr Bank ৳৯২, Cr AR ৳৯২। নগদ আসে; **অতিরিক্ত P&L লাইন নেই**।'],
                ['en' => '**Commission settlement** — Dr Commission Expense, Cr AP/Bank. Reduces **net profit** on period P&L.', 'bn' => '**কমিশন নিষ্পত্তি** — Dr Commission Expense। **নিট লাভ** কমায়।'],
            ],
        ],
        [
            'id' => 'invoice-journal',
            'title' => [
                'en' => 'Journal at invoice (revenue + COGS) — 1 unit',
                'bn' => 'ইনভয়েসে জার্নাল (আয় + COGS) — ১ ইউনিট',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Two journals typically fire when you invoice 1 carton: sales_invoice and inventory COGS (if inventory GL enabled).',
                'bn' => '১ কার্টন ইনভয়েসে সাধারণত দুটি জার্নাল: sales_invoice ও inventory COGS (inventory GL চালু থাকলে)।',
            ],
            'rows' => [
                ['account' => ['en' => 'Accounts Receivable (1100)', 'bn' => 'Accounts Receivable (1100)'], 'debit' => ['en' => '৳92.00', 'bn' => '৳৯২.০০'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Sales Revenue (4000)', 'bn' => 'Sales Revenue (4000)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳80.00', 'bn' => '৳৮০.০০']],
                ['account' => ['en' => 'VAT Payable (2000)', 'bn' => 'VAT Payable (2000)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳12.00', 'bn' => '৳১২.০০']],
                ['account' => ['en' => 'Cost of Goods Sold (5000)', 'bn' => 'Cost of Goods Sold (5000)'], 'debit' => ['en' => '৳42.00', 'bn' => '৳৪২.০০'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Finished Goods Inventory (1310)', 'bn' => 'Finished Goods Inventory (1310)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => '৳42.00', 'bn' => '৳৪২.০০']],
            ],
        ],
        [
            'id' => 'pl-formula',
            'title' => [
                'en' => 'How the P&L report calculates profit (Saf ERP)',
                'bn' => 'P&L রিপোর্ট কীভাবে লাভ হিসাব করে (Saf ERP)',
            ],
            'type' => 'formula',
            'items' => [
                [
                    'label' => ['en' => 'Net sales (period)', 'bn' => 'নেট বিক্রয় (সময়কাল)'],
                    'formula' => ['en' => 'Sum of Sales Revenue (4000) credits − Sales Returns (4050) debits in date range', 'bn' => 'তারিখ পরিসরে Sales Revenue (4000) ক্রেডিট − Sales Returns (4050) ডেবিট'],
                ],
                [
                    'label' => ['en' => 'COGS (period)', 'bn' => 'COGS (সময়কাল)'],
                    'formula' => ['en' => 'If COGS journals exist: sum debits to account 5000. Else: estimated from production material_unit_cost × invoiced qty', 'bn' => 'COGS জার্নাল থাকলে: অ্যাকাউন্ট ৫০০০ ডেবিট যোগ। না থাকলে: material_unit_cost × ইনভয়েস পরিমাণ অনুমান'],
                ],
                [
                    'label' => ['en' => 'Gross profit', 'bn' => 'মোট লাভ'],
                    'formula' => ['en' => 'Net sales − COGS  →  Example: ৳80 − ৳42 = ৳38 per unit above', 'bn' => 'নেট বিক্রয় − COGS  →  উদাহরণ: ৳৮০ − ৳৪২ = ৳৩৮'],
                ],
                [
                    'label' => ['en' => 'Operating costs', 'bn' => 'অপারেটিং খরচ'],
                    'formula' => ['en' => 'Commission Expense + Expenses module + Salary distributions + other expense accounts in period', 'bn' => 'Commission Expense + Expenses মডিউল + বেতন বিতরণ + অন্যান্য খরচ অ্যাকাউন্ট'],
                ],
                [
                    'label' => ['en' => 'Net profit (bottom line)', 'bn' => 'নিট লাভ'],
                    'formula' => ['en' => 'Gross profit − commissions − other expenses − payroll', 'bn' => 'মোট লাভ − কমিশন − অন্যান্য খরচ − পে-রোল'],
                ],
            ],
            'note' => [
                'en' => 'Open **Profit & loss** at `/admin/reports/pl`. The report shows whether COGS came from **GL** or **estimated** — always prefer posted journals for audit.',
                'bn' => '**লাভ ও ক্ষতি** `/admin/reports/pl`-এ খুলুন। রিপোর্ট দেখায় COGS **GL** না **অনুমান** — নিরীক্ষায় পোস্ট করা জার্নালই নিন।',
            ],
        ],
        [
            'id' => 'common-mistakes',
            'title' => [
                'en' => 'Common mistakes when reading profit',
                'bn' => 'লাভ পড়ার সময় সাধারণ ভুল',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => 'Treating **invoice total (incl. VAT)** as revenue — revenue is **net excl. VAT** (৳80 not ৳92).', 'bn' => '**ইনভয়েস মোট (VAT সহ)** আয় ধরা — আয় **নেট VAT বাদে** (৳৯২ নয় ৳৮০)।'],
                ['en' => 'Expecting profit when only **GRN or production** happened — P&L moves at **invoice** (revenue + COGS).', 'bn' => 'শুধু **GRN/উৎপাদন** হলেই লাভ — P&L **ইনভয়েসে** (আয় + COGS)।'],
                ['en' => 'Ignoring **commission and payroll** — gross profit is healthy but net profit can still be low.', 'bn' => '**কমিশন ও বেতন** উপেক্ষা — মোট লাভ ভালো কিন্তু নিট লাভ কম হতে পারে।'],
                ['en' => 'Comparing **bank balance** to P&L — receipts clear AR but do not double-count revenue.', 'bn' => '**ব্যাংক ব্যালেন্স** P&L-এর সাথে তুলনা — রসিদ AR ক্লিয়ার করে, আয় দ্বিগুণ হয় না।'],
            ],
        ],
    ],
    'report_links' => [
        ['title' => ['en' => 'Profit & loss report', 'bn' => 'লাভ-ক্ষতি রিপোর্ট'], 'path' => '/admin/reports/pl', 'desc' => ['en' => 'See net sales, COGS, gross and net profit for any period', 'bn' => 'যেকোনো সময়কালে নেট বিক্রয়, COGS, মোট ও নিট লাভ']],
        ['title' => ['en' => 'General ledger', 'bn' => 'জেনারেল লেজার'], 'path' => '/admin/reports/general-ledger', 'desc' => ['en' => 'Drill Sales Revenue and COGS accounts', 'bn' => 'Sales Revenue ও COGS অ্যাকাউন্ট দেখুন']],
        ['title' => ['en' => 'Production runs', 'bn' => 'উৎপাদন রান'], 'path' => '/admin/production', 'desc' => ['en' => 'Check material_unit_cost used for COGS estimate', 'bn' => 'COGS অনুমানের material_unit_cost দেখুন']],
        ['title' => ['en' => 'Customer invoices', 'bn' => 'গ্রাহক ইনভয়েস'], 'path' => '/admin/finance', 'desc' => ['en' => 'Invoice that triggers revenue and COGS journals', 'bn' => 'আয় ও COGS জার্নাল ট্রিগার করে এমন ইনভয়েস']],
    ],
    'faqs' => [
        [
            'q' => ['en' => 'Why is my P&L COGS “estimated” not GL?', 'bn' => 'P&L-এ COGS কেন “estimated” GL নয়?'],
            'a' => ['en' => 'No debits were posted to COGS (5000) in the period — usually inventory GL is off, or invoices were not issued. Turn on inventory GL and invoice delivered orders; then COGS source becomes **gl**.', 'bn' => 'সময়কালে COGS (5000)-এ ডেবিট পোস্ট হয়নি — সাধারণত inventory GL বন্ধ বা ইনভয়েস হয়নি। inventory GL চালু করে ডেলিভার্ড অর্ডার ইনভয়েস করুন।'],
        ],
        [
            'q' => ['en' => 'If I sell 100 units like the example, what is gross profit?', 'bn' => 'উদাহরণের মতো ১০০ ইউনিট বিক্রি করলে মোট লাভ?'],
            'a' => ['en' => 'Scale the unit economics: 100 × ৳38 gross profit = **৳3,800** before commission and overhead (if price and BOM cost are unchanged).', 'bn' => 'ইউনিট হিসাব × ১০০: ১০০ × ৳৩৮ = **৳৩,৮০০** মোট লাভ (কমিশন/ওভারহেডের আগে, মূল্য ও BOM একই হলে)।'],
        ],
    ],
    'glossary' => [
        ['term' => ['en' => 'Gross profit', 'bn' => 'মোট লাভ'], 'def' => ['en' => 'Net sales minus COGS — margin on products before office/payroll costs.', 'bn' => 'নেট বিক্রয় − COGS — অফিস/বেতনের আগে পণ্য মার্জিন।']],
        ['term' => ['en' => 'Net profit', 'bn' => 'নিট লাভ'], 'def' => ['en' => 'Gross profit minus all operating expenses in the P&L period.', 'bn' => 'মোট লাভ − সময়কালের সব অপারেটিং খরচ।']],
        ['term' => ['en' => 'Unit economics', 'bn' => 'ইউনিট ইকোনমিক্স'], 'def' => ['en' => 'Profit and cost for a single SKU quantity — useful to sanity-check the full P&L.', 'bn' => 'একটি SKU-র লাভ ও খরচ — পুরো P&L যাচাইয়ে কাজে লাগে।']],
    ],
];
