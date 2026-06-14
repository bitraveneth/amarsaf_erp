<?php

return [
    'steps_extra' => [
        ['title' => ['en' => 'Post salary distribution', 'bn' => 'বেতন বিতরণ পোস্ট'], 'body' => ['en' => 'Accounting → Salary distributions. Enter employee, period, base + allowances. System posts Dr Salaries & wages, Cr Bank/cash/salary payable. Use this for payroll — not duplicate in Expenses.', 'bn' => 'Accounting → Salary distributions। কর্মী, পিরিয়ড, বেতন + ভাতা। Dr Salaries & wages, Cr Bank/cash/payable। Expenses-এ দ্বিগুণ করবেন না।'], 'path' => '/admin/salary-distributions'],
    ],
    'tips_extra' => [
        ['en' => 'HR screens (contracts, leaves) do **not** post GL — only salary distributions do.', 'bn' => 'HR (চুক্তি, ছুটি) GL পোস্ট করে না — শুধু salary distributions।'],
        ['en' => 'Employee department can appear as analytic label on payroll journal lines.', 'bn' => 'কর্মীর department payroll journal line-এ analytic label হতে পারে।'],
    ],
    'deep_sections' => [
        [
            'id' => 'hr-vs-finance',
            'title' => ['en' => 'HR vs finance — what posts where', 'bn' => 'HR বনাম finance — কোথায় পোস্ট'],
            'type' => 'mapping_table',
            'rows' => [
                ['action' => ['en' => 'Employee master', 'bn' => 'Employee master'], 'when' => ['en' => 'Create / edit employee', 'bn' => 'তৈরি / সম্পাদনা'], 'debit' => ['en' => '— (no GL)', 'bn' => '— (GL নয়)'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['action' => ['en' => 'Contract / leave / badge', 'bn' => 'Contract / leave'], 'when' => ['en' => 'HR workflow', 'bn' => 'HR workflow'], 'debit' => ['en' => '— (no GL)', 'bn' => '— (GL নয়)'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['action' => ['en' => 'Allowance setup', 'bn' => 'Allowance setup'], 'when' => ['en' => 'Master data', 'bn' => 'Master data'], 'debit' => ['en' => '— (no GL)', 'bn' => '— (GL নয়)'], 'credit' => ['en' => '—', 'bn' => '—']],
                ['action' => ['en' => 'Salary distribution', 'bn' => 'Salary distribution'], 'when' => ['en' => 'Saved in accounting', 'bn' => 'Accounting-এ সেভ'], 'debit' => ['en' => 'Salaries & wages', 'bn' => 'Salaries & wages'], 'credit' => ['en' => 'Bank / cash / salary payable', 'bn' => 'Bank / cash / payable']],
            ],
        ],
    ],
    'faqs' => [
        ['q' => ['en' => 'Where is payroll on P&L?', 'bn' => 'P&L-এ পে-রোল কোথায়?'], 'a' => ['en' => 'Salaries & wages expense from posted payroll journals. Reports → Payroll summary cross-checks distributions vs GL.', 'bn' => 'পোস্ট করা payroll journal থেকে Salaries & wages। Reports → Payroll summary যাচাই করুন।']],
    ],
];
