<?php

return [
    'technical_tab' => ['en' => 'Reports pack', 'bn' => 'রিপোর্ট প্যাক'],
    'steps_extra' => [
        ['title' => ['en' => 'Month-end pack', 'bn' => 'মাস শেষ প্যাক'], 'body' => ['en' => 'Run: inventory valuation → AR aging → AP aging → trial balance → P&L → VAT report.', 'bn' => 'চালান: inventory valuation → AR aging → AP aging → trial balance → P&L → VAT।'], 'path' => '/admin/reports-dashboard'],
        ['title' => ['en' => 'Export data', 'bn' => 'ডেটা এক্সপোর্ট'], 'body' => ['en' => 'Export center: CSV/PDF by module and date range for auditors.', 'bn' => 'এক্সপোর্ট সেন্টার: মডিউল ও তারিখ অনুযায়ী CSV/PDF।'], 'path' => '/admin/export-center'],
        ['title' => ['en' => 'Logistics & fleet reports', 'bn' => 'লজিস্টিক্স ও ফ্লিট রিপোর্ট'], 'body' => ['en' => 'Reports → Logistics reports: bills summary, fleet expenses, and route cost vs sales.', 'bn' => 'Reports → Logistics reports: বিল সারাংশ, ফ্লিট খরচ ও রুট খরচ বনাম বিক্রয়।'], 'path' => '/admin/reports/logistics'],
    ],
    'deep_sections' => [
        [
            'id' => 'month-end',
            'title' => ['en' => 'Month-end checklist (finance)', 'bn' => 'মাস শেষ চেকলিস্ট (হিসাব)'],
            'type' => 'checklist',
            'items' => [
                ['en' => 'All GRNs and production confirms posted for the month.', 'bn' => 'মাসের সব GRN ও উৎপাদন নিশ্চিত পোস্ট।'],
                ['en' => 'All delivered orders invoiced; receipts posted.', 'bn' => 'ডেলিভার্ড অর্ডার ইনভয়েস; রসিদ পোস্ট।'],
                ['en' => 'Supplier bills entered; payments recorded.', 'bn' => 'সাপ্লায়ার বিল ও পেমেন্ট রেকর্ড।'],
                ['en' => 'Trial balance balances; AR/AP aging matches sub-ledgers.', 'bn' => 'ট্রায়াল ব্যালেন্স মিল; AR/AP aging সাব-লেজারের সাথে।'],
                ['en' => 'VAT report reviewed; export Tally XML if using external CA.', 'bn' => 'VAT রিপোর্ট রিভিউ; Tally XML এক্সপোর্ট।'],
                ['en' => 'Logistics bills paid or accrued; fleet expenses posted; scan Route cost vs sales.', 'bn' => 'লজিস্টিক্স বিল পরিশোধ/accrue; ফ্লিট খরচ পোস্ট; Route cost vs sales দেখুন।'],
                ['en' => 'Close accounting period.', 'bn' => 'অ্যাকাউন্টিং পিরিয়ড বন্ধ।'],
            ],
        ],
    ],
    'report_links' => [
        ['title' => ['en' => 'Trial balance', 'bn' => 'ট্রায়াল ব্যালেন্স'], 'path' => '/admin/reports/trial-balance', 'desc' => ['en' => 'GL balances by period', 'bn' => 'পিরিয়ড অনুযায়ী GL']],
        ['title' => ['en' => 'AR aging', 'bn' => 'AR aging'], 'path' => '/admin/reports/ar-aging', 'desc' => ['en' => 'Receivables by bucket', 'bn' => 'পাওনা বকেট']],
        ['title' => ['en' => 'Inventory valuation', 'bn' => 'স্টক মূল্যায়ন'], 'path' => '/admin/reports/inventory-valuation', 'desc' => ['en' => 'Stock value report', 'bn' => 'স্টক মূল্য রিপোর্ট']],
        ['title' => ['en' => 'Logistics reports', 'bn' => 'লজিস্টিক্স রিপোর্ট'], 'path' => '/admin/reports/logistics', 'desc' => ['en' => 'Bills, fleet, route profit', 'bn' => 'বিল, ফ্লিট, রুট লাভ']],
        ['title' => ['en' => 'Export center', 'bn' => 'এক্সপোর্ট সেন্টার'], 'path' => '/admin/export-center', 'desc' => ['en' => 'CSV / PDF / Tally XML', 'bn' => 'CSV / PDF / Tally XML']],
        ['title' => ['en' => 'Production summary', 'bn' => 'উৎপাদন সারাংশ'], 'path' => '/admin/manufacturing-dashboard', 'desc' => ['en' => 'Output and runs', 'bn' => 'আউটপুট ও রান']],
    ],
    'faqs' => [
        ['q' => ['en' => 'P&L does not match expectations?', 'bn' => 'P&L প্রত্যাশা মিলছে না?'], 'a' => ['en' => 'Check if COGS is from GL journals or estimate; ensure all invoices and expenses posted in period.', 'bn' => 'COGS GL থেকে না অনুমান — চেক করুন; পিরিয়ডের সব ইনভয়েস ও খরচ পোস্ট হয়েছে কিনা।']],
    ],
];
