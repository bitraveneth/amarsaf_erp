<?php

/**
 * Accounting technical reference — for finance officers and tax advisors.
 * Merged into Learning Hub accounting module by LearningHubRepository.
 */
return [
    'audience' => [
        'en' => 'Finance officers, auditors, and income-tax advisors',
        'bn' => 'হিসাব কর্মকর্তা, নিরীক্ষক ও আয়কর পরামর্শক',
    ],
    'steps_extra' => [
        ['title' => ['en' => 'Accounting dashboard', 'bn' => 'হিসাব ড্যাশবোর্ড'], 'body' => ['en' => 'Start here for AR/AP snapshot, then jump into invoices, bills, or journals.', 'bn' => 'AR/AP সারাংশ এখান থেকে — তারপর ইনভয়েস, বিল বা জার্নালে যান।'], 'path' => '/admin/accounting-dashboard'],
        ['title' => ['en' => 'Bank reconciliation', 'bn' => 'ব্যাংক মিলকরণ'], 'body' => ['en' => 'Match bank statement lines to receipts, payments, and expenses before month-end close.', 'bn' => 'মাস শেষের আগে ব্যাংক স্টেটমেন্ট রসিদ, পেমেন্ট ও খরচের সাথে মিলান।'], 'path' => '/admin/finance/reconciliation'],
    ],
    'technical_tab' => [
        'en' => 'Tax & ledger reference',
        'bn' => 'কর ও লেজার রেফারেন্স',
    ],
    'flowchart_technical' => [
        'en' => <<<'MERMAID'
flowchart TB
    subgraph ops ["Operational documents"]
        SO["Sales order delivered"]
        INV["Customer invoice"]
        RCT["Receipt"]
        BILL["Supplier bill"]
        GRN["GRN approved"]
    end
    subgraph gl ["General ledger journals"]
        JE["Journal entry posted"]
        TB["Trial balance"]
        GL["General ledger drill-down"]
    end
    subgraph tax ["Tax reporting"]
        VAT["VAT report output minus input"]
        AR["AR aging"]
        PL["Profit and loss"]
    end
    SO --> INV --> JE
    RCT --> JE
    BILL --> JE
    GRN --> JE
    JE --> TB --> GL
    JE --> VAT
    JE --> AR
    JE --> PL
MERMAID,
        'bn' => <<<'MERMAID'
flowchart TB
    subgraph ops ["অপারেশনাল ডকুমেন্ট"]
        SO["ডেলিভার্ড বিক্রয় অর্ডার"]
        INV["গ্রাহক ইনভয়েস"]
        RCT["রসিদ"]
        BILL["সাপ্লায়ার বিল"]
        GRN["অনুমোদিত GRN"]
    end
    subgraph gl ["জেনারেল লেজার জার্নাল"]
        JE["জার্নাল পোস্ট"]
        TB["ট্রায়াল ব্যালেন্স"]
        GL["GL ড্রিল-ডাউন"]
    end
    subgraph tax ["কর রিপোর্টিং"]
        VAT["VAT রিপোর্ট"]
        AR["AR aging"]
        PL["লাভ-ক্ষতি"]
    end
    SO --> INV --> JE
    RCT --> JE
    BILL --> JE
    GRN --> JE
    JE --> TB --> GL
    JE --> VAT
    JE --> AR
    JE --> PL
MERMAID,
    ],
    'deep_sections' => [
        [
            'id' => 'model',
            'icon' => 'ledger',
            'title' => [
                'en' => 'What kind of accounting system is this?',
                'bn' => 'এটি কী ধরনের হিসাব ব্যবস্থা?',
            ],
            'type' => 'prose',
            'body' => [
                'en' => "Saf ERP is an **operational ERP with a journal-based general ledger (GL)**. Business events (invoice, receipt, GRN, production, bill payment) create **balanced journal entries** — total debits must equal total credits before posting.\n\nFor a tax advisor, think of three layers:\n\n1. **Sub-ledgers** — invoices, receipts, purchase bills, expenses (what staff use day to day).\n2. **General ledger** — `journal_entries` + `journal_entry_lines` linked to chart of accounts (`accounts` table).\n3. **Management & tax reports** — P&L, trial balance, VAT report, AR/AP aging, Tally XML export.\n\nThis is suitable for **management accounts and VAT reconciliation**. Statutory audit may still require export to external accounting software — use **Tally XML** (`/admin/tally-export`) or CSV/PDF exports from reports.",
                'bn' => "Saf ERP একটি **জার্নাল-ভিত্তিক জেনারেল লেজার (GL) সহ অপারেশনাল ERP**। ইনভয়েস, রসিদ, GRN, উৎপাদন, বিল পেমেন্ট ইত্যাদি ঘটনায় **ভারসাম্যপূর্ণ জার্নাল এন্ট্রি** তৈরি হয় — পোস্টের আগে মোট ডেবিট = মোট ক্রেডিট।\n\nআয়কর পরামর্শকের জন্য তিন স্তর:\n\n1. **সাব-লেজার** — ইনভয়েস, রসিদ, ক্রয় বিল, খরচ।\n2. **জেনারেল লেজার** — `journal_entries` + `journal_entry_lines`, চার্ট অফ অ্যাকাউন্টসের সাথে যুক্ত।\n3. **রিপোর্ট** — P&L, ট্রায়াল ব্যালেন্স, VAT, AR/AP aging, Tally XML।",
            ],
        ],
        [
            'id' => 'coa',
            'icon' => 'table',
            'title' => [
                'en' => 'Chart of accounts (seeded defaults)',
                'bn' => 'চার্ট অফ অ্যাকাউন্টস (ডিফল্ট)',
            ],
            'type' => 'coa_table',
            'intro' => [
                'en' => 'Accounts are typed as asset, liability, equity, income, or expense. Screen: `/admin/accounts`.',
                'bn' => 'অ্যাকাউন্টগুলো asset, liability, equity, income বা expense টাইপ। স্ক্রিন: `/admin/accounts`।',
            ],
            'rows' => [
                ['code' => '1000', 'name' => ['en' => 'Bank', 'bn' => 'ব্যাংক'], 'type' => 'asset'],
                ['code' => '1100', 'name' => ['en' => 'Accounts Receivable', 'bn' => 'পাওনা (AR)'], 'type' => 'asset'],
                ['code' => '1150', 'name' => ['en' => 'Input VAT', 'bn' => 'ইনপুট VAT'], 'type' => 'asset'],
                ['code' => '1160', 'name' => ['en' => 'Withholding Tax Receivable', 'bn' => 'উৎসে কর কাটা প্রাপ্য'], 'type' => 'asset'],
                ['code' => '1200', 'name' => ['en' => 'Agent Advances', 'bn' => 'এজেন্ট অগ্রিম'], 'type' => 'asset'],
                ['code' => '1300', 'name' => ['en' => 'Raw Materials Inventory', 'bn' => 'কাঁচামাল ইনভেন্টরি'], 'type' => 'asset'],
                ['code' => '1310', 'name' => ['en' => 'Finished Goods Inventory', 'bn' => 'তৈরি পণ্য ইনভেন্টরি'], 'type' => 'asset'],
                ['code' => '1350', 'name' => ['en' => 'Work in Progress', 'bn' => 'চলমান কাজ (WIP)'], 'type' => 'asset'],
                ['code' => '2000', 'name' => ['en' => 'VAT Payable', 'bn' => 'প্রদেয় VAT'], 'type' => 'liability'],
                ['code' => '2100', 'name' => ['en' => 'Accounts Payable', 'bn' => 'দেনা (AP)'], 'type' => 'liability'],
                ['code' => '2190', 'name' => ['en' => 'GRNI Accrual', 'bn' => 'GRNI অ্যাক্রুয়াল'], 'type' => 'liability'],
                ['code' => '4000', 'name' => ['en' => 'Sales Revenue', 'bn' => 'বিক্রয় আয়'], 'type' => 'income'],
                ['code' => '4050', 'name' => ['en' => 'Sales Returns', 'bn' => 'বিক্রয় ফেরত'], 'type' => 'income'],
                ['code' => '5000', 'name' => ['en' => 'Cost of Goods Sold', 'bn' => 'বিক্রিত পণ্যের খরচ (COGS)'], 'type' => 'expense'],
                ['code' => '5050', 'name' => ['en' => 'Purchases', 'bn' => 'ক্রয়'], 'type' => 'expense'],
                ['code' => '5350', 'name' => ['en' => 'Commission Expense', 'bn' => 'কমিশন খরচ'], 'type' => 'expense'],
            ],
        ],
        [
            'id' => 'sales-invoice',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: customer sales invoice',
                'bn' => 'জার্নাল: গ্রাহক বিক্রয় ইনভয়েস',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Triggered when an invoice is issued from a **delivered** sales order (`journal_type: sales_invoice`). VAT rate comes from product **tax class**. Withholding is a separate adjustment if the agent has a withholding rate.',
                'bn' => '**ডেলিভার্ড** বিক্রয় অর্ডার থেকে ইনভয়েস ইস্যু হলে (`sales_invoice`)। VAT পণ্যের **ট্যাক্স ক্লাস** থেকে। এজেন্টে উৎসে কর থাকলে আলাদা অ্যাডজাস্টমেন্ট।',
            ],
            'rows' => [
                ['account' => ['en' => 'Accounts Receivable (1100)', 'bn' => 'Accounts Receivable (1100)'], 'debit' => ['en' => 'Net sales + Output VAT', 'bn' => 'নেট বিক্রয় + আউটপুট VAT'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Sales Revenue (4000)', 'bn' => 'Sales Revenue (4000)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Net sales (excl. VAT)', 'bn' => 'নেট বিক্রয় (VAT বাদে)']],
                ['account' => ['en' => 'VAT Payable (2000)', 'bn' => 'VAT Payable (2000)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Output VAT amount', 'bn' => 'আউটপুট VAT']],
            ],
            'note' => [
                'en' => '**Withholding (AIT/TDS-style):** If agent withholding applies, system posts Dr Withholding Tax Receivable (1160) and Cr Accounts Receivable — reducing cash collectible while creating a receivable from tax authority.',
                'bn' => '**উৎসে কর:** এজেন্টে withholding থাকলে Dr Withholding Tax Receivable (1160), Cr Accounts Receivable — নগদ আদায় কমে, কর কর্তৃপক্ষের কাছে প্রাপ্য তৈরি হয়।',
            ],
        ],
        [
            'id' => 'receipt',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: customer receipt (collection)',
                'bn' => 'জার্নাল: গ্রাহক রসিদ (আদায়)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Posted when cash/bank is received against an invoice (`journal_type: customer_receipt`). Amount cannot exceed invoice outstanding.',
                'bn' => 'ইনভয়েসের বিপরীতে নগদ/ব্যাংক আদায় হলে (`customer_receipt`)। পরিমাণ বকেয়ার বেশি হতে পারে না।',
            ],
            'rows' => [
                ['account' => ['en' => 'Bank (1000)', 'bn' => 'Bank (1000)'], 'debit' => ['en' => 'Amount received', 'bn' => 'আদায়কৃত পরিমাণ'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Accounts Receivable (1100)', 'bn' => 'Accounts Receivable (1100)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Amount received', 'bn' => 'আদায়কৃত পরিমাণ']],
            ],
        ],
        [
            'id' => 'credit-note',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: credit note (returns / adjustments)',
                'bn' => 'জার্নাল: ক্রেডিট নোট (ফেরত / সমন্বয়)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Reverses part of revenue and output VAT; reduces AR. Used for sales returns or billing corrections.',
                'bn' => 'আয় ও আউটপুট VAT আংশিক বিপরীত; AR কমায়। বিক্রয় ফেরত বা বিল সংশোধনে।',
            ],
            'rows' => [
                ['account' => ['en' => 'Sales Returns (4050)', 'bn' => 'Sales Returns (4050)'], 'debit' => ['en' => 'Net credit amount', 'bn' => 'নেট ক্রেডিট'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'VAT Payable (2000)', 'bn' => 'VAT Payable (2000)'], 'debit' => ['en' => 'VAT portion reversed', 'bn' => 'বিপরীত VAT'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Accounts Receivable (1100)', 'bn' => 'Accounts Receivable (1100)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Total credit (net + VAT)', 'bn' => 'মোট ক্রেডিট']],
            ],
        ],
        [
            'id' => 'supplier-bill',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: supplier purchase bill (AP)',
                'bn' => 'জার্নাল: সাপ্লায়ার ক্রয় বিল (AP)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Records supplier invoice linked to PO/GRN. **Input VAT** is debited (recoverable asset). Net goes to Purchases or inventory path depending on bill type.',
                'bn' => 'PO/GRN-এর সাথে যুক্ত সাপ্লায়ার ইনভয়েস। **ইনপুট VAT** ডেবিট (প্রাপ্য সম্পদ)। নেট Purchases বা ইনভেন্টরি পথে।',
            ],
            'rows' => [
                ['account' => ['en' => 'Purchases (5050) or Inventory', 'bn' => 'Purchases (5050) বা Inventory'], 'debit' => ['en' => 'Net amount', 'bn' => 'নেট পরিমাণ'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Input VAT (1150)', 'bn' => 'Input VAT (1150)'], 'debit' => ['en' => 'Input VAT', 'bn' => 'ইনপুট VAT'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Accounts Payable (2100)', 'bn' => 'Accounts Payable (2100)'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Net + VAT', 'bn' => 'নেট + VAT']],
            ],
        ],
        [
            'id' => 'inventory-gl',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: inventory & COGS (perpetual GL)',
                'bn' => 'জার্নাল: ইনভেন্টরি ও COGS (পার্পেচুয়াল GL)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'When `ACCOUNTING_INVENTORY_GL=true`, `InventoryAccountingService` posts additional journals:',
                'bn' => '`ACCOUNTING_INVENTORY_GL=true` হলে `InventoryAccountingService` অতিরিক্ত জার্নাল পোস্ট করে:',
            ],
            'rows' => [
                ['account' => ['en' => 'GRN approved', 'bn' => 'GRN অনুমোদন'], 'debit' => ['en' => 'Dr Raw Materials Inventory (1300)', 'bn' => 'Dr Raw Materials (1300)'], 'credit' => ['en' => 'Cr GRNI Accrual (2190)', 'bn' => 'Cr GRNI Accrual (2190)']],
                ['account' => ['en' => 'Production confirmed', 'bn' => 'উৎপাদন নিশ্চিত'], 'debit' => ['en' => 'Dr Finished Goods / WIP moves', 'bn' => 'Dr FG / WIP স্থানান্তর'], 'credit' => ['en' => 'Cr Raw Materials consumed', 'bn' => 'Cr কাঁচামাল ব্যবহার']],
                ['account' => ['en' => 'Sales invoice issued', 'bn' => 'বিক্রয় ইনভয়েস'], 'debit' => ['en' => 'Dr Cost of Goods Sold (5000)', 'bn' => 'Dr COGS (5000)'], 'credit' => ['en' => 'Cr Finished Goods Inventory (1310)', 'bn' => 'Cr FG Inventory (1310)']],
            ],
            'note' => [
                'en' => 'COGS recognition defaults to **invoice** time (`ACCOUNTING_COGS_RECOGNITION=invoice`) — Anglo-Saxon style. P&L uses posted GL COGS when journals exist; otherwise falls back to production cost estimate.',
                'bn' => 'COGS ডিফল্ট **ইনভয়েস** সময়ে (`invoice`) — Anglo-Saxon। জার্নাল থাকলে P&L GL COGS ব্যবহার করে; না থাকলে উৎপাদন অনুমান।',
            ],
        ],
        [
            'id' => 'vat',
            'icon' => 'formula',
            'title' => [
                'en' => 'VAT logic (Bangladesh context)',
                'bn' => 'VAT যুক্তি (বাংলাদেশ প্রসঙ্গ)',
            ],
            'type' => 'formula',
            'items' => [
                [
                    'label' => ['en' => 'Output VAT (sales)', 'bn' => 'আউটপুট VAT (বিক্রয়)'],
                    'formula' => ['en' => 'Sum of VAT on invoice lines → credited to VAT Payable (2000)', 'bn' => 'ইনভয়েস লাইনের VAT যোগ → VAT Payable (2000) ক্রেডিট'],
                ],
                [
                    'label' => ['en' => 'Input VAT (purchases)', 'bn' => 'ইনপুট VAT (ক্রয়)'],
                    'formula' => ['en' => 'Sum of VAT on supplier bills → debited to Input VAT (1150)', 'bn' => 'সাপ্লায়ার বিলের VAT → Input VAT (1150) ডেবিট'],
                ],
                [
                    'label' => ['en' => 'VAT report (period)', 'bn' => 'VAT রিপোর্ট (পিরিয়ড)'],
                    'formula' => ['en' => 'Net VAT liability ≈ Output VAT − Input VAT (see /admin/reports/vat)', 'bn' => 'নেট VAT দায় ≈ আউটপুট VAT − ইনপুট VAT'],
                ],
                [
                    'label' => ['en' => 'Invoice outstanding (AR sub-ledger)', 'bn' => 'ইনভয়েস বকেয়া (AR সাব-লেজার)'],
                    'formula' => ['en' => '(Net + VAT − Withholding) − Receipts − Credit notes − Advance applied', 'bn' => '(নেট + VAT − উৎসে কর) − রসিদ − ক্রেডিট নোট − অগ্রিম প্রয়োগ'],
                ],
            ],
        ],
        [
            'id' => 'pl',
            'icon' => 'formula',
            'title' => [
                'en' => 'Profit & Loss construction',
                'bn' => 'লাভ-ক্ষতি গঠন',
            ],
            'type' => 'formula',
            'items' => [
                ['label' => ['en' => 'Net sales', 'bn' => 'নেট বিক্রয়'], 'formula' => ['en' => 'Sales Revenue (4000 credits) − Sales Returns (4050 debits)', 'bn' => 'Sales Revenue − Sales Returns']],
                ['label' => ['en' => 'COGS', 'bn' => 'COGS'], 'formula' => ['en' => 'Posted COGS journals (5000) OR estimated from production material cost if GL empty', 'bn' => 'পোস্ট করা COGS জার্নাল অথবা GL খালি হলে উৎপাদন অনুমান']],
                ['label' => ['en' => 'Gross profit', 'bn' => 'মোট লাভ'], 'formula' => ['en' => 'Net sales − COGS', 'bn' => 'নেট বিক্রয় − COGS']],
                ['label' => ['en' => 'Operating costs', 'bn' => 'অপারেটিং খরচ'], 'formula' => ['en' => 'Commission + Expenses module + Payroll + Marketing + Utilities + Gifts', 'bn' => 'কমিশন + Expenses + পে-রোল + মার্কেটিং + ইউটিলিটি + গিফট']],
                ['label' => ['en' => 'Net profit', 'bn' => 'নিট লাভ'], 'formula' => ['en' => 'Gross profit − Operating costs', 'bn' => 'মোট লাভ − অপারেটিং খরচ']],
            ],
        ],
        [
            'id' => 'expense',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: expense (category routing)',
                'bn' => 'জার্নাল: expense (category routing)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Posted when expense is saved (`journal_type: expense`). Debit ledger comes from **expense category mapping** or manual account override. Credit = bank, cash, or payable per payment type.',
                'bn' => 'Expense সেভে (`expense`)। ডেবিট **expense category mapping** থেকে। ক্রেডিট = bank, cash, বা payable।',
            ],
            'rows' => [
                ['account' => ['en' => 'Category expense (e.g. Utilities)', 'bn' => 'Category expense'], 'debit' => ['en' => 'Expense amount', 'bn' => 'পরিমাণ'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Bank / Cash / Payable', 'bn' => 'Bank / Cash / Payable'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Expense amount', 'bn' => 'পরিমাণ']],
            ],
        ],
        [
            'id' => 'payroll',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: salary distribution (payroll)',
                'bn' => 'জার্নাল: salary distribution (payroll)',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Posted from Accounting → Salary distributions (`journal_type: payroll`). Total = base + bonus + TA + DA + commission on distribution.',
                'bn' => 'Salary distributions থেকে (`payroll`)। মোট = base + bonus + allowances।',
            ],
            'rows' => [
                ['account' => ['en' => 'Salaries & wages', 'bn' => 'Salaries & wages'], 'debit' => ['en' => 'Total payroll', 'bn' => 'মোট পে-রোল'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Bank / Cash / Salary payable', 'bn' => 'Bank / Cash / Payable'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Total payroll', 'bn' => 'মোট পে-রোল']],
            ],
        ],
        [
            'id' => 'commission',
            'icon' => 'journal',
            'title' => [
                'en' => 'Journal: agent commission',
                'bn' => 'জার্নাল: এজেন্ট কমিশন',
            ],
            'type' => 'journal',
            'intro' => [
                'en' => 'Accrual on settlement: Dr Commission expense, Cr Commission payable. Payment reverses payable to bank.',
                'bn' => 'Settlement accrual: Dr Commission expense, Cr Commission payable। পেমেন্টে payable → bank।',
            ],
            'rows' => [
                ['account' => ['en' => 'Commission expense', 'bn' => 'Commission expense'], 'debit' => ['en' => 'Accrued amount', 'bn' => 'Accrued'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['account' => ['en' => 'Commission payable', 'bn' => 'Commission payable'], 'debit' => ['en' => '—', 'bn' => '—'], 'credit' => ['en' => 'Accrued amount', 'bn' => 'Accrued']],
            ],
        ],
        [
            'id' => 'controls',
            'icon' => 'shield',
            'title' => [
                'en' => 'Period close & audit controls',
                'bn' => 'পিরিয়ড ক্লোজ ও নিরীক্ষা নিয়ন্ত্রণ',
            ],
            'type' => 'checklist',
            'items' => [
                ['en' => '**Accounting periods** (`/admin/accounting-periods`) — posting blocked into closed periods.', 'bn' => '**অ্যাকাউন্টিং পিরিয়ড** — বন্ধ পিরিয়ডে পোস্টিং ব্লক।'],
                ['en' => '**Trial balance** (`/admin/reports/trial-balance`) — verify debits = credits per period.', 'bn' => '**ট্রায়াল ব্যালেন্স** — পিরিয়ডে ডেবিট = ক্রেডিট যাচাই।'],
                ['en' => '**General ledger** (`/admin/reports/general-ledger`) — drill from account to journal lines.', 'bn' => '**জেনারেল লেজার** — অ্যাকাউন্ট থেকে জার্নাল লাইনে ড্রিল।'],
                ['en' => '**AR aging** compares sub-ledger outstanding to GL balance on Accounts Receivable — variance shown on report.', 'bn' => '**AR aging** সাব-লেজার বকেয়া GL AR ব্যালেন্সের সাথে তুলনা — variance দেখায়।'],
                ['en' => '**Manual journals** (`/admin/journals`) — draft → post → reverse for year-end adjustments.', 'bn' => '**ম্যানুয়াল জার্নাল** — ড্রাফট → পোস্ট → রিভার্স।'],
                ['en' => '**Bank reconciliation** (`/admin/finance/reconciliation`) — match bank CSV to receipts and payments.', 'bn' => '**ব্যাংক রিকনসিলিয়েশন** — ব্যাংক CSV মিলান।'],
            ],
        ],
    ],
    'report_links' => [
        ['title' => ['en' => 'Trial balance', 'bn' => 'ট্রায়াল ব্যালেন্স'], 'path' => '/admin/reports/trial-balance', 'desc' => ['en' => 'All account balances for a period', 'bn' => 'পিরিয়ডের সব অ্যাকাউন্ট ব্যালেন্স']],
        ['title' => ['en' => 'General ledger', 'bn' => 'জেনারেল লেজার'], 'path' => '/admin/reports/general-ledger', 'desc' => ['en' => 'Journal line drill-down by account', 'bn' => 'অ্যাকাউন্ট অনুযায়ী জার্নাল']],
        ['title' => ['en' => 'VAT report', 'bn' => 'VAT রিপোর্ট'], 'path' => '/admin/reports/vat', 'desc' => ['en' => 'Output vs input VAT for filing', 'bn' => 'ফাইলিংয়ের জন্য আউটপুট বনাম ইনপুট']],
        ['title' => ['en' => 'AR aging', 'bn' => 'AR aging'], 'path' => '/admin/reports/ar-aging', 'desc' => ['en' => 'Receivables by agent and bucket', 'bn' => 'এজেন্ট ও বকেট অনুযায়ী পাওনা']],
        ['title' => ['en' => 'AP aging', 'bn' => 'AP aging'], 'path' => '/admin/reports/ap-aging', 'desc' => ['en' => 'Payables by supplier', 'bn' => 'সাপ্লায়ার অনুযায়ী দেনা']],
        ['title' => ['en' => 'P&L', 'bn' => 'লাভ-ক্ষতি'], 'path' => '/admin/reports/pl', 'desc' => ['en' => 'Management profit and loss', 'bn' => 'ম্যানেজমেন্ট P&L']],
        ['title' => ['en' => 'Inventory valuation', 'bn' => 'ইনভেন্টরি মূল্যায়ন'], 'path' => '/admin/reports/inventory-valuation', 'desc' => ['en' => 'Stock value tied to GL inventory accounts', 'bn' => 'GL ইনভেন্টরি অ্যাকাউন্টে স্টক মূল্য']],
        ['title' => ['en' => 'Tally XML export', 'bn' => 'Tally XML এক্সপোর্ট'], 'path' => '/admin/exports/tally', 'desc' => ['en' => 'Export posted journals for external CA software', 'bn' => 'বাহ্যিক CA সফটওয়্যারের জন্য জার্নাল এক্সপোর্ট']],
    ],
    'glossary' => [
        ['term' => ['en' => 'Journal entry', 'bn' => 'জার্নাল এন্ট্রি'], 'def' => ['en' => 'A balanced set of debit/credit lines posted on a date, with type e.g. sales_invoice, customer_receipt.', 'bn' => 'এক তারিখে ভারসাম্যপূর্ণ ডেবিট/ক্রেডিট লাইনের সেট।']],
        ['term' => ['en' => 'Sub-ledger', 'bn' => 'সাব-লেজার'], 'def' => ['en' => 'Operational document trail (invoice #, receipt #) before you drill to GL.', 'bn' => 'অপারেশনাল ডকুমেন্ট (ইনভয়েস, রসিদ) — GL-এর আগে।']],
        ['term' => ['en' => 'GRNI', 'bn' => 'GRNI'], 'def' => ['en' => 'Goods Received Not Invoiced — accrual liability when stock is received before supplier bill.', 'bn' => 'মাল গ্রহণ কিন্তু বিল হয়নি — অ্যাক্রুয়াল দায়।']],
        ['term' => ['en' => 'Withholding', 'bn' => 'উৎসে কর'], 'def' => ['en' => 'Tax deducted at source on agent invoice; reduces cash collection, creates receivable from authority.', 'bn' => 'এজেন্ট ইনভয়েসে উৎসে কর; নগদ আদায় কমায়।']],
        ['term' => ['en' => 'COGS', 'bn' => 'COGS'], 'def' => ['en' => 'Cost of Goods Sold — expense recognized when finished goods are sold (invoice by default).', 'bn' => 'বিক্রিত পণ্যের খরচ — বিক্রয়/ইনভয়েসে স্বীকৃত।']],
    ],
    'faqs' => [
        [
            'q' => ['en' => 'Can this replace full statutory books for income tax assessment?', 'bn' => 'আয়কর নির্ধারণের জন্য পূর্ণ স্ট্যাচুটরি বই এর বিকল্প হতে পারে?'],
            'a' => ['en' => 'It provides journal-based GL, VAT reports, and Tally export — strong for management and VAT support. Many clients still give their CA exported journals + invoice registers for final tax filing. Verify period close and trial balance before handover.', 'bn' => 'জার্নাল GL, VAT রিপোর্ট ও Tally এক্সপোর্ট আছে — ম্যানেজমেন্ট ও VAT-এর জন্য শক্তিশালী। অনেক ক্লায়েন্ট CA-কে এক্সপোর্ট + ইনভয়েস রেজিস্টার দেন। হ্যান্ডওভার前 পিরিয়ড ক্লোজ ও ট্রায়াল ব্যালেন্স যাচাই করুন।'],
        ],
        [
            'q' => ['en' => 'Where is output VAT stored?', 'bn' => 'আউটপুট VAT কোথায় থাকে?'],
            'a' => ['en' => 'Credited to account 2000 VAT Payable on each sales invoice journal. Credit notes debit VAT Payable to reverse.', 'bn' => 'প্রতি বিক্রয় ইনভয়েস জার্নালে 2000 VAT Payable ক্রেডিট। ক্রেডিট নোট বিপরীত করে ডেবিট।'],
        ],
        [
            'q' => ['en' => 'How do I trace one invoice to GL?', 'bn' => 'এক ইনভয়েস GL-এ কীভাবে ট্রেস করব?'],
            'a' => ['en' => 'Open invoice in `/admin/finance` → note invoice ID → search journals filtered by source, or use General Ledger report on Accounts Receivable and filter by date/description.', 'bn' => '`/admin/finance`-এ ইনভয়েস খুলুন → জার্নালে source/description দিয়ে খুঁজুন অথবা AR GL রিপোর্টে তারিখ/বিবরণ ফিল্টার করুন।'],
        ],
    ],
];
