<?php

/**
 * Per-course quiz questions. correct = option id (a, b, c, d).
 */
return [
    'overview' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'ov1',
                'prompt' => ['en' => 'What is the usual order after buying raw materials?', 'bn' => 'কাঁচামাল কেনার পর সাধারণ ক্রম কী?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Invoice → GRN → production', 'bn' => 'ইনভয়েস → GRN → উৎপাদন']],
                    ['id' => 'b', 'text' => ['en' => 'PO → GRN → production', 'bn' => 'PO → GRN → উৎপাদন']],
                    ['id' => 'c', 'text' => ['en' => 'Sales order → GRN', 'bn' => 'বিক্রয় অর্ডার → GRN']],
                    ['id' => 'd', 'text' => ['en' => 'Production → PO', 'bn' => 'উৎপাদন → PO']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'You approve a PO, receive with GRN, then manufacture finished goods.', 'bn' => 'PO অনুমোদন, GRN গ্রহণ, তারপর তৈরি পণ্য উৎপাদন।'],
            ],
            [
                'id' => 'ov2',
                'prompt' => ['en' => 'When does revenue usually appear on the P&L?', 'bn' => 'P&L-এ আয় সাধারণত কখন আসে?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'When GRN is posted', 'bn' => 'GRN পোস্টে']],
                    ['id' => 'b', 'text' => ['en' => 'When production is confirmed', 'bn' => 'উৎপাদন নিশ্চিতে']],
                    ['id' => 'c', 'text' => ['en' => 'When sales invoice is issued', 'bn' => 'বিক্রয় ইনভয়েসে']],
                    ['id' => 'd', 'text' => ['en' => 'When PO is drafted', 'bn' => 'PO ড্রাফটে']],
                ],
                'correct' => 'c',
                'explain' => ['en' => 'Revenue and COGS typically post when you invoice delivered goods.', 'bn' => 'ডেলিভার্ড পণ্য ইনভয়েস করলে আয় ও COGS পোস্ট হয়।'],
            ],
            [
                'id' => 'ov3',
                'prompt' => ['en' => 'If stock is wrong, what should you check first?', 'bn' => 'স্টক ভুল হলে আগে কী দেখবেন?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Agent commission rules', 'bn' => 'এজেন্ট কমিশন নিয়ম']],
                    ['id' => 'b', 'text' => ['en' => 'Master data or GRN receipts', 'bn' => 'মাস্টার ডেটা বা GRN']],
                    ['id' => 'c', 'text' => ['en' => 'Payroll', 'bn' => 'পে-রোল']],
                    ['id' => 'd', 'text' => ['en' => 'VAT report only', 'bn' => 'শুধু VAT রিপোর্ট']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Stock traces back to receipts, transfers, and production — fix source documents first.', 'bn' => 'স্টক GRN, স্থানান্তর ও উৎপাদন থেকে আসে — আগে উৎস ঠিক করুন।'],
            ],
            [
                'id' => 'ov4',
                'prompt' => ['en' => 'What document proves goods reached the agent?', 'bn' => 'পণ্য এজেন্টের কাছে পৌঁছেছে তার প্রমাণ কী?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Purchase order', 'bn' => 'ক্রয় অর্ডার']],
                    ['id' => 'b', 'text' => ['en' => 'BOM', 'bn' => 'BOM']],
                    ['id' => 'c', 'text' => ['en' => 'Proof of delivery (POD)', 'bn' => 'POD']],
                    ['id' => 'd', 'text' => ['en' => 'Supplier bill', 'bn' => 'সাপ্লায়ার বিল']],
                ],
                'correct' => 'c',
                'explain' => ['en' => 'POD records delivered qty before you invoice.', 'bn' => 'ইনভয়েসের আগে POD ডেলিভার্ড পরিমাণ রেকর্ড করে।'],
            ],
            [
                'id' => 'ov5',
                'prompt' => ['en' => 'What does Logistics record that Delivery POD does not?', 'bn' => 'Delivery POD যা করে না, Logistics কী রেকর্ড করে?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Proof the agent received goods', 'bn' => 'এজেন্ট পণ্য পেয়েছে তার প্রমাণ']],
                    ['id' => 'b', 'text' => ['en' => 'How much the trip cost (fleet or carrier)', 'bn' => 'ট্রিপ কত খরচ (ফ্লিট বা ক্যারিয়ার)']],
                    ['id' => 'c', 'text' => ['en' => 'BOM material lines', 'bn' => 'BOM কাঁচামাল লাইন']],
                    ['id' => 'd', 'text' => ['en' => 'Agent credit limit', 'bn' => 'এজেন্ট ক্রেডিট লিমিট']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'POD is proof of arrival. Logistics is fleet spend and hired-carrier bills.', 'bn' => 'POD পৌঁছানোর প্রমাণ। Logistics ফ্লিট খরচ ও ভাড়া ক্যারিয়ার বিল।'],
            ],
        ],
    ],
    'products' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'pr1',
                'prompt' => ['en' => 'Materials are mainly used for…', 'bn' => 'কাঁচামাল মূলত ব্যবহার…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Direct sale to agents', 'bn' => 'এজেন্টকে সরাসরি বিক্রি']],
                    ['id' => 'b', 'text' => ['en' => 'Production and purchasing', 'bn' => 'উৎপাদন ও ক্রয়']],
                    ['id' => 'c', 'text' => ['en' => 'VAT filing only', 'bn' => 'শুধু VAT ফাইলিং']],
                    ['id' => 'd', 'text' => ['en' => 'Payroll', 'bn' => 'পে-রোল']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Materials are bought and consumed; products are what you sell.', 'bn' => 'কাঁচামাল কেনা ও ব্যবহার; পণ্য বিক্রি হয়।'],
            ],
            [
                'id' => 'pr2',
                'prompt' => ['en' => 'Why must SKUs be unique?', 'bn' => 'SKU অনন্য কেন?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'For email templates', 'bn' => 'ইমেইল টেমপ্লেটের জন্য']],
                    ['id' => 'b', 'text' => ['en' => 'So stock, BOM, and orders never mix items', 'bn' => 'স্টক, BOM, অর্ডারে আইটেম গুলিয়ে না যায়']],
                    ['id' => 'c', 'text' => ['en' => 'NBR requires it', 'bn' => 'NBR চায়']],
                    ['id' => 'd', 'text' => ['en' => 'Only for reports', 'bn' => 'শুধু রিপোর্টে']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'SKU is the key across inventory, production, and sales.', 'bn' => 'SKU ইনভেন্টরি, উৎপাদন ও বিক্রয়ে মূল চাবি।'],
            ],
            [
                'id' => 'pr3',
                'prompt' => ['en' => 'SAF-500ML-CTN in the examples is…', 'bn' => 'উদাহরণে SAF-500ML-CTN হলো…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'A raw bottle material', 'bn' => 'কাঁচা বোতল']],
                    ['id' => 'b', 'text' => ['en' => 'A sellable finished product', 'bn' => 'বিক্রয়যোগ্য তৈরি পণ্য']],
                    ['id' => 'c', 'text' => ['en' => 'A tax class', 'bn' => 'ট্যাক্স ক্লাস']],
                    ['id' => 'd', 'text' => ['en' => 'A warehouse', 'bn' => 'গুদাম']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Product SKUs represent finished goods you invoice.', 'bn' => 'পণ্য SKU তৈরি পণ্য যা ইনভয়েস করেন।'],
            ],
        ],
    ],
    'agents' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'ag1',
                'prompt' => ['en' => 'Inactive agents…', 'bn' => 'নিষ্ক্রিয় এজেন্ট…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Can still get new orders', 'bn' => 'নতুন অর্ডার পেতে পারে']],
                    ['id' => 'b', 'text' => ['en' => 'Cannot be selected on new orders', 'bn' => 'নতুন অর্ডারে বেছে নেওয়া যায় না']],
                    ['id' => 'c', 'text' => ['en' => 'Auto-delete from system', 'bn' => 'স্বয়ং মুছে যায়']],
                    ['id' => 'd', 'text' => ['en' => 'Skip POD', 'bn' => 'POD এড়ায়']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Deactivate agents you no longer trade with.', 'bn' => 'যাদের সাথে লেনদেন নেই তাদের নিষ্ক্রিয় করুন।'],
            ],
            [
                'id' => 'ag2',
                'prompt' => ['en' => 'Price lists let you…', 'bn' => 'প্রাইস লিস্ট দিয়ে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Set different prices per agent/product', 'bn' => 'এজেন্ট/পণ্য অনুযায়ী আলাদা মূল্য']],
                    ['id' => 'b', 'text' => ['en' => 'Post GRN', 'bn' => 'GRN পোস্ট']],
                    ['id' => 'c', 'text' => ['en' => 'Run payroll', 'bn' => 'পে-রোল চালান']],
                    ['id' => 'd', 'text' => ['en' => 'Close accounting period', 'bn' => 'পিরিয়ড বন্ধ']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Zone or agent-specific pricing is common in distribution.', 'bn' => 'জোন বা এজেন্ট ভিত্তিক মূল্য সাধারণ।'],
            ],
        ],
    ],
    'procurement' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'pc1',
                'prompt' => ['en' => 'GRN increases stock when…', 'bn' => 'GRN স্টক বাড়ায় যখন…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'PO is still draft', 'bn' => 'PO ড্রাফট']],
                    ['id' => 'b', 'text' => ['en' => 'Lines are approved/received and posted', 'bn' => 'লাইন গ্রহণ ও পোস্ট হয়']],
                    ['id' => 'c', 'text' => ['en' => 'Sales order is confirmed', 'bn' => 'বিক্রয় অর্ডার নিশ্চিত']],
                    ['id' => 'd', 'text' => ['en' => 'Invoice is sent to agent', 'bn' => 'এজেন্টকে ইনভয়েস']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Posted GRN moves qty into warehouse inventory.', 'bn' => 'পোস্ট GRN পরিমাণ গুদামে যোগ করে।'],
            ],
            [
                'id' => 'pc2',
                'prompt' => ['en' => 'Partial GRN means…', 'bn' => 'আংশিক GRN মানে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'PO must be cancelled', 'bn' => 'PO বাতিল']],
                    ['id' => 'b', 'text' => ['en' => 'You can receive remaining qty on a later GRN', 'bn' => 'বাকি পরিমাণ পরে GRN-এ নিতে পারেন']],
                    ['id' => 'c', 'text' => ['en' => 'Stock goes negative', 'bn' => 'স্টক ঋণাত্মক']],
                    ['id' => 'd', 'text' => ['en' => 'No supplier bill allowed', 'bn' => 'সাপ্লায়ার বিল নয়']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Multiple GRNs against one PO are normal.', 'bn' => 'এক PO-তে একাধিক GRN স্বাভাবিক।'],
            ],
            [
                'id' => 'pc3',
                'prompt' => ['en' => 'Service/labour PO lines…', 'bn' => 'সার্ভিস/শ্রম PO লাইন…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Always add warehouse qty', 'bn' => 'সবসময় গুদামে পরিমাণ']],
                    ['id' => 'b', 'text' => ['en' => 'Confirm without stock quantity', 'bn' => 'স্টক ছাড়াই নিশ্চিত']],
                    ['id' => 'c', 'text' => ['en' => 'Skip approval', 'bn' => 'অনুমোদন এড়ায়']],
                    ['id' => 'd', 'text' => ['en' => 'Create sales orders', 'bn' => 'বিক্রয় অর্ডার তৈরি']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Non-stock services are expensed, not inventoried.', 'bn' => 'নন-স্টক সার্ভিস খরচ, স্টক নয়।'],
            ],
        ],
    ],
    'warehouses' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'wh1',
                'prompt' => ['en' => 'Factory-type warehouse is usually for…', 'bn' => 'Factory টাইপ গুদাম সাধারণত…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Agent showroom only', 'bn' => 'শুধু শোরুম']],
                    ['id' => 'b', 'text' => ['en' => 'Production and FG output', 'bn' => 'উৎপাদন ও FG']],
                    ['id' => 'c', 'text' => ['en' => 'VAT payments', 'bn' => 'VAT পরিশোধ']],
                    ['id' => 'd', 'text' => ['en' => 'Payroll', 'bn' => 'পে-রোল']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Production runs consume and produce stock at factory warehouses.', 'bn' => 'উৎপাদন ফ্যাক্টরি গুদামে স্টক কাটে ও যোগ করে।'],
            ],
            [
                'id' => 'wh2',
                'prompt' => ['en' => 'Warehouse locations (bins) are for…', 'bn' => 'গুদাম লোকেশন (বিন) কী জন্য?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Finer pick accuracy inside a warehouse', 'bn' => 'গুদামের ভিতরে সঠিক পিক']],
                    ['id' => 'b', 'text' => ['en' => 'Posting carrier freight invoices', 'bn' => 'ক্যারিয়ার ফ্রেইট ইনভয়েস']],
                    ['id' => 'c', 'text' => ['en' => 'VAT to COGS only', 'bn' => 'শুধু VAT ও COGS']],
                    ['id' => 'd', 'text' => ['en' => 'Agent commission rules', 'bn' => 'এজেন্ট কমিশন নিয়ম']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Bins and aisles live under Warehouses. Vehicles, carriers, and route cost live in Logistics.', 'bn' => 'বিন ও অ্যাসল Warehouses-এ। যান, ক্যারিয়ার ও রুট খরচ Logistics-এ।'],
            ],
        ],
    ],
    'manufacturing' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'mf1',
                'prompt' => ['en' => 'BOM defines…', 'bn' => 'BOM নির্ধারণ করে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Agent credit limit', 'bn' => 'এজেন্ট ক্রেডিট']],
                    ['id' => 'b', 'text' => ['en' => 'Materials needed per 1 finished unit', 'bn' => 'প্রতি ১ তৈরি ইউনিটে কাঁচামাল']],
                    ['id' => 'c', 'text' => ['en' => 'VAT rate', 'bn' => 'VAT হার']],
                    ['id' => 'd', 'text' => ['en' => 'Delivery POD', 'bn' => 'POD']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'BOM is the recipe for production consumption.', 'bn' => 'BOM উৎপাদনের রেসিপি।'],
            ],
            [
                'id' => 'mf2',
                'prompt' => ['en' => 'Without an active BOM, confirming production…', 'bn' => 'সক্রিয় BOM ছাড়া উৎপাদন নিশ্চিত করলে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Auto-creates sales orders', 'bn' => 'বিক্রয় অর্ডার তৈরি']],
                    ['id' => 'b', 'text' => ['en' => 'May not consume materials correctly', 'bn' => 'কাঁচামাল সঠিক কাটতে নাও পারে']],
                    ['id' => 'c', 'text' => ['en' => 'Posts revenue', 'bn' => 'আয় পোস্ট']],
                    ['id' => 'd', 'text' => ['en' => 'Closes VAT period', 'bn' => 'VAT পিরিয়ড বন্ধ']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Material consumption depends on a valid BOM.', 'bn' => 'কাঁচামাল কাটা বৈধ BOM-এ নির্ভর।'],
            ],
            [
                'id' => 'mf3',
                'prompt' => ['en' => 'After QC approval, finished goods go to…', 'bn' => 'QC অনুমোদনের পর তৈরি পণ্য…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Pending receipts → confirm stock', 'bn' => 'Pending receipts → স্টক নিশ্চিত']],
                    ['id' => 'b', 'text' => ['en' => 'Direct agent invoice', 'bn' => 'সরাসরি ইনভয়েস']],
                    ['id' => 'c', 'text' => ['en' => 'Supplier AP', 'bn' => 'সাপ্লায়ার AP']],
                    ['id' => 'd', 'text' => ['en' => 'Payroll', 'bn' => 'পে-রোল']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Warehouse confirms FG into sellable stock.', 'bn' => 'গুদাম FG বিক্রয়যোগ্য স্টকে নিশ্চিত করে।'],
            ],
        ],
    ],
    'inventory' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'iv1',
                'prompt' => ['en' => 'Low stock report helps you…', 'bn' => 'লো স্টক রিপোর্ট সাহায্য করে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Plan purchase orders', 'bn' => 'PO পরিকল্পনা']],
                    ['id' => 'b', 'text' => ['en' => 'File VAT only', 'bn' => 'শুধু VAT']],
                    ['id' => 'c', 'text' => ['en' => 'Set agent zones', 'bn' => 'এজেন্ট জোন']],
                    ['id' => 'd', 'text' => ['en' => 'Run payroll', 'bn' => 'পে-রোল']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Reorder raw materials before production stops.', 'bn' => 'উৎপাদন থামার আগে কাঁচামাল অর্ডার।'],
            ],
            [
                'id' => 'iv2',
                'prompt' => ['en' => 'Stock transfer moves qty…', 'bn' => 'স্টক স্থানান্তর পরিমাণ সরায়…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Between warehouses', 'bn' => 'গুদামের মধ্যে']],
                    ['id' => 'b', 'text' => ['en' => 'From agent to supplier', 'bn' => 'এজেন্ট থেকে সাপ্লায়ার']],
                    ['id' => 'c', 'text' => ['en' => 'Into P&L revenue', 'bn' => 'P&L আয়ে']],
                    ['id' => 'd', 'text' => ['en' => 'Into VAT payable only', 'bn' => 'শুধু VAT Payable']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Transfers rebalance depot vs factory stock.', 'bn' => 'স্থানান্তর ডিপো ও ফ্যাক্টরি স্টক ভারসাম্য।'],
            ],
        ],
    ],
    'sales' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'sl1',
                'prompt' => ['en' => 'Confirming a sales order typically…', 'bn' => 'বিক্রয় অর্ডার নিশ্চিত করলে সাধারণত…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Posts customer receipt', 'bn' => 'গ্রাহক রসিদ পোস্ট']],
                    ['id' => 'b', 'text' => ['en' => 'Reserves stock for picking', 'bn' => 'পিকিংয়ের জন্য স্টক রিজার্ভ']],
                    ['id' => 'c', 'text' => ['en' => 'Creates supplier bill', 'bn' => 'সাপ্লায়ার বিল']],
                    ['id' => 'd', 'text' => ['en' => 'Closes accounting period', 'bn' => 'পিরিয়ড বন্ধ']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Reservation prevents overselling FG you do not have.', 'bn' => 'রিজার্ভেশন অতিরিক্ত বিক্রি রোধ করে।'],
            ],
            [
                'id' => 'sl2',
                'prompt' => ['en' => 'Sample order type is for…', 'bn' => 'Sample অর্ডার টাইপ…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Free promotional goods (not billed)', 'bn' => 'ফ্রি প্রমো (বিল নয়)']],
                    ['id' => 'b', 'text' => ['en' => 'Supplier returns', 'bn' => 'সাপ্লায়ার ফেরত']],
                    ['id' => 'c', 'text' => ['en' => 'Payroll advances', 'bn' => 'পে-রোল অগ্রিম']],
                    ['id' => 'd', 'text' => ['en' => 'GRN corrections', 'bn' => 'GRN সংশোধন']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Samples are tracked but not invoiced like normal sales.', 'bn' => 'স্যাম্পল ট্র্যাক হয় কিন্তু সাধারণ বিক্রির মতো বিল নয়।'],
            ],
        ],
    ],
    'delivery' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'dl1',
                'prompt' => ['en' => 'You should usually invoice when status is…', 'bn' => 'সাধারণত ইনভয়েস করবেন যখন স্ট্যাটাস…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Draft order', 'bn' => 'ড্রাফট']],
                    ['id' => 'b', 'text' => ['en' => 'Delivered (after POD)', 'bn' => 'Delivered (POD পর)']],
                    ['id' => 'c', 'text' => ['en' => 'PO approved', 'bn' => 'PO অনুমোদিত']],
                    ['id' => 'd', 'text' => ['en' => 'BOM created', 'bn' => 'BOM তৈরি']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Revenue should follow proof goods reached the agent.', 'bn' => 'আয় পণ্য পৌঁছানোর প্রমাণের পর।'],
            ],
            [
                'id' => 'dl2',
                'prompt' => ['en' => 'POD should capture…', 'bn' => 'POD-এ থাকা উচিত…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Delivered qty, shorts, damage, receiver', 'bn' => 'ডেলিভার্ড, কমতি, ক্ষতি, গ্রহীতা']],
                    ['id' => 'b', 'text' => ['en' => 'Supplier VAT only', 'bn' => 'শুধু সাপ্লায়ার VAT']],
                    ['id' => 'c', 'text' => ['en' => 'Payroll deductions', 'bn' => 'পে-রোল কাটা']],
                    ['id' => 'd', 'text' => ['en' => 'BOM lines', 'bn' => 'BOM লাইন']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'POD is the field record of what actually arrived.', 'bn' => 'POD মাঠে যা পৌঁছেছে তার রেকর্ড।'],
            ],
        ],
    ],
    'logistics' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'lg1',
                'prompt' => ['en' => 'A hired truck invoice should be posted as…', 'bn' => 'ভাড়া ট্রাকের ইনভয়েস পোস্ট হবে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'A purchase bill on a bottle supplier', 'bn' => 'বোতল সাপ্লায়ারের ক্রয় বিল']],
                    ['id' => 'b', 'text' => ['en' => 'A logistics bill on a transport carrier', 'bn' => 'ট্রান্সপোর্ট ক্যারিয়ারের লজিস্টিক্স বিল']],
                    ['id' => 'c', 'text' => ['en' => 'A sales invoice to the agent', 'bn' => 'এজেন্টের বিক্রয় ইনভয়েস']],
                    ['id' => 'd', 'text' => ['en' => 'A POD only — no bill', 'bn' => 'শুধু POD — বিল নয়']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Hired transport uses Logistics bills, not raw-material purchase bills.', 'bn' => 'ভাড়া পরিবহন Logistics bill — কাঁচামাল ক্রয় বিল নয়।'],
            ],
            [
                'id' => 'lg2',
                'prompt' => ['en' => 'Fuel for your own van is recorded as…', 'bn' => 'নিজস্ব ভ্যানের জ্বালানি রেকর্ড হয়…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'A logistics bill', 'bn' => 'লজিস্টিক্স বিল']],
                    ['id' => 'b', 'text' => ['en' => 'A fleet expense', 'bn' => 'ফ্লিট খরচ']],
                    ['id' => 'c', 'text' => ['en' => 'A customer receipt', 'bn' => 'গ্রাহক রসিদ']],
                    ['id' => 'd', 'text' => ['en' => 'A GRN', 'bn' => 'GRN']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Own-truck running costs are fleet expenses, not carrier invoices.', 'bn' => 'নিজস্ব ট্রাকের চলতি খরচ fleet expense — ক্যারিয়ার ইনভয়েস নয়।'],
            ],
            [
                'id' => 'lg3',
                'prompt' => ['en' => 'Vehicle load sheets are mainly for…', 'bn' => 'যান লোড শিট মূলত…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Posting VAT to NBR', 'bn' => 'NBR-এ VAT পোস্ট']],
                    ['id' => 'b', 'text' => ['en' => 'Planning which deliveries share one truck trip', 'bn' => 'কোন ডেলিভারি এক ট্রাক ট্রিপে']],
                    ['id' => 'c', 'text' => ['en' => 'Creating a sales order', 'bn' => 'বিক্রয় অর্ডার তৈরি']],
                    ['id' => 'd', 'text' => ['en' => 'Closing the accounting period', 'bn' => 'অ্যাকাউন্টিং পিরিয়ড বন্ধ']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Loads are operational. Ledger impact comes from fleet expenses or logistics bills.', 'bn' => 'লোড অপারেশনাল। GL প্রভাব fleet expense বা logistics bill-এ।'],
            ],
            [
                'id' => 'lg4',
                'prompt' => ['en' => 'Route cost vs sales helps you see…', 'bn' => 'Route cost vs sales দেখায়…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Whether a route’s fleet + carrier spend is covered by sales', 'bn' => 'রুটের ফ্লিট + ক্যারিয়ার খরচ বিক্রয় দিয়ে উঠেছে কিনা']],
                    ['id' => 'b', 'text' => ['en' => 'Employee leave balances', 'bn' => 'কর্মী ছুটির ব্যালেন্স']],
                    ['id' => 'c', 'text' => ['en' => 'BOM recipes', 'bn' => 'BOM রেসিপি']],
                    ['id' => 'd', 'text' => ['en' => 'Tax classes on products', 'bn' => 'পণ্যের ট্যাক্স ক্লাস']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'The report compares logistics cost to sales by delivery route.', 'bn' => 'রিপোর্ট রুট অনুযায়ী লজিস্টিক্স খরচ ও বিক্রয় তুলনা করে।'],
            ],
        ],
    ],
    'accounting' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'ac1',
                'prompt' => ['en' => 'Sales invoice posts (typical)…', 'bn' => 'বিক্রয় ইনভয়েস পোস্ট করে (সাধারণত)…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Dr AR, Cr Revenue, Cr VAT Payable', 'bn' => 'Dr AR, Cr Revenue, Cr VAT Payable']],
                    ['id' => 'b', 'text' => ['en' => 'Dr Bank only', 'bn' => 'শুধু Dr Bank']],
                    ['id' => 'c', 'text' => ['en' => 'Cr COGS only', 'bn' => 'শুধু Cr COGS']],
                    ['id' => 'd', 'text' => ['en' => 'Dr Payroll', 'bn' => 'Dr Payroll']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'AR is gross invoice; revenue is net of VAT.', 'bn' => 'AR মোট ইনভয়েস; আয় VAT বাদে।'],
            ],
            [
                'id' => 'ac2',
                'prompt' => ['en' => 'Customer receipt posts…', 'bn' => 'গ্রাহক রসিদ পোস্ট…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Dr Bank, Cr AR', 'bn' => 'Dr Bank, Cr AR']],
                    ['id' => 'b', 'text' => ['en' => 'Dr Revenue again', 'bn' => 'আবার Dr Revenue']],
                    ['id' => 'c', 'text' => ['en' => 'Cr COGS', 'bn' => 'Cr COGS']],
                    ['id' => 'd', 'text' => ['en' => 'Dr VAT Payable only', 'bn' => 'শুধু Dr VAT Payable']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Receipt clears AR — it does not double-count revenue.', 'bn' => 'রসিদ AR ক্লিয়ার করে — আয় দ্বিগুণ নয়।'],
            ],
            [
                'id' => 'ac3',
                'prompt' => ['en' => 'Output VAT minus Input VAT (period) gives…', 'bn' => 'আউটপুট VAT − ইনপুট VAT (সময়কাল) দেয়…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Net VAT payable to NBR', 'bn' => 'NBR-এ নেট VAT প্রদেয়']],
                    ['id' => 'b', 'text' => ['en' => 'Gross profit', 'bn' => 'মোট লাভ']],
                    ['id' => 'c', 'text' => ['en' => 'FG stock value', 'bn' => 'FG স্টক মূল্য']],
                    ['id' => 'd', 'text' => ['en' => 'Agent commission', 'bn' => 'এজেন্ট কমিশন']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'VAT report reconciles sales vs purchase VAT.', 'bn' => 'VAT রিপোর্ট বিক্রয় ও ক্রয় VAT মিলায়।'],
            ],
        ],
    ],
    'profit-loss' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'pl1',
                'prompt' => ['en' => 'In the 1-carton example, gross profit per unit is…', 'bn' => '১ কার্টন উদাহরণে প্রতি ইউনিট মোট লাভ…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => '৳92', 'bn' => '৳৯২']],
                    ['id' => 'b', 'text' => ['en' => '৳38 (80 − 42)', 'bn' => '৳৩৮ (৮০ − ৪২)']],
                    ['id' => 'c', 'text' => ['en' => '৳12', 'bn' => '৳১২']],
                    ['id' => 'd', 'text' => ['en' => '৳42', 'bn' => '৳৪২']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Gross profit = net sales − COGS, excluding VAT.', 'bn' => 'মোট লাভ = নেট বিক্রয় − COGS, VAT বাদে।'],
            ],
            [
                'id' => 'pl2',
                'prompt' => ['en' => 'VAT on the invoice is…', 'bn' => 'ইনভয়েসের VAT…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Part of your revenue and profit', 'bn' => 'আয় ও লাভের অংশ']],
                    ['id' => 'b', 'text' => ['en' => 'Collected for NBR — not revenue', 'bn' => 'NBR-এর জন্য — আয় নয়']],
                    ['id' => 'c', 'text' => ['en' => 'Same as COGS', 'bn' => 'COGS-এর মতো']],
                    ['id' => 'd', 'text' => ['en' => 'Posted to payroll', 'bn' => 'পে-রোলে']],
                ],
                'correct' => 'b',
                'explain' => ['en' => '৳80 is revenue; ৳12 is VAT liability.', 'bn' => '৳৮০ আয়; ৳১২ VAT দায়।'],
            ],
            [
                'id' => 'pl3',
                'prompt' => ['en' => 'COGS on P&L usually posts when…', 'bn' => 'P&L-এ COGS সাধারণত পোস্ট হয়…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'GRN is received', 'bn' => 'GRN গ্রহণে']],
                    ['id' => 'b', 'text' => ['en' => 'Sales invoice is issued', 'bn' => 'বিক্রয় ইনভয়েসে']],
                    ['id' => 'c', 'text' => ['en' => 'PO is drafted', 'bn' => 'PO ড্রাফটে']],
                    ['id' => 'd', 'text' => ['en' => 'Payroll runs', 'bn' => 'পে-রোলে']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'COGS pairs with revenue at invoice time.', 'bn' => 'ইনভয়েসে COGS আয়ের সাথে যায়।'],
            ],
        ],
    ],
    'ledger-mapping' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'lm1',
                'prompt' => ['en' => 'When does sales revenue post to the ledger?', 'bn' => 'বিক্রয় আয় ledger-এ কখন পোস্ট?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'When sales order is confirmed', 'bn' => 'অর্ডার নিশ্চিতে']],
                    ['id' => 'b', 'text' => ['en' => 'When delivery is dispatched', 'bn' => 'ডিসপ্যাচে']],
                    ['id' => 'c', 'text' => ['en' => 'When sales invoice is issued', 'bn' => 'বিক্রয় ইনভয়েসে']],
                    ['id' => 'd', 'text' => ['en' => 'When PO is approved', 'bn' => 'PO অনুমোদনে']],
                ],
                'correct' => 'c',
                'explain' => ['en' => 'Revenue and VAT post on invoice, not on order or delivery.', 'bn' => 'আয় ও VAT ইনভয়েসে — অর্ডার বা ডেলিভারিতে নয়।'],
            ],
            [
                'id' => 'lm2',
                'prompt' => ['en' => 'A paid utilities expense posts…', 'bn' => 'পরিশোধিত utilities expense পোস্ট…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Dr Bank, Cr Utilities expense', 'bn' => 'Dr Bank, Cr Utilities']],
                    ['id' => 'b', 'text' => ['en' => 'Dr Utilities expense, Cr Bank', 'bn' => 'Dr Utilities, Cr Bank']],
                    ['id' => 'c', 'text' => ['en' => 'Dr Trade debtors, Cr Bank', 'bn' => 'Dr AR, Cr Bank']],
                    ['id' => 'd', 'text' => ['en' => 'No journal', 'bn' => 'জার্নাল নয়']],
                ],
                'correct' => 'b',
                'explain' => ['en' => 'Expenses debit the category expense account and credit bank (when paid).', 'bn' => 'Expense Dr category expense, Cr bank (paid হলে)।'],
            ],
        ],
    ],
    'hr-payroll' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'hr1',
                'prompt' => ['en' => 'Which screen posts payroll to the general ledger?', 'bn' => 'কোন স্ক্রিন payroll GL-এ পোস্ট করে?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Employee contracts', 'bn' => 'Contracts']],
                    ['id' => 'b', 'text' => ['en' => 'Leave requests', 'bn' => 'Leaves']],
                    ['id' => 'c', 'text' => ['en' => 'Salary distributions', 'bn' => 'Salary distributions']],
                    ['id' => 'd', 'text' => ['en' => 'Badges', 'bn' => 'Badges']],
                ],
                'correct' => 'c',
                'explain' => ['en' => 'Only salary distributions create payroll journals.', 'bn' => 'শুধু salary distributions payroll journal তৈরি করে।'],
            ],
        ],
    ],
    'reports' => [
        'pass_percent' => 70,
        'questions' => [
            [
                'id' => 'rp1',
                'prompt' => ['en' => 'Month-end pack often includes…', 'bn' => 'মাস শেষ প্যাকে প্রায়ই থাকে…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Inventory valuation + AR aging + P&L', 'bn' => 'Inventory valuation + AR aging + P&L']],
                    ['id' => 'b', 'text' => ['en' => 'BOM only', 'bn' => 'শুধু BOM']],
                    ['id' => 'c', 'text' => ['en' => 'Agent phone list', 'bn' => 'এজেন্ট ফোন তালিকা']],
                    ['id' => 'd', 'text' => ['en' => 'Vehicle colour chart', 'bn' => 'যানের রঙ']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Finance reviews stock, collections, and profit together.', 'bn' => 'অর্থ স্টক, আদায় ও লাভ একসাথে দেখে।'],
            ],
            [
                'id' => 'rp2',
                'prompt' => ['en' => 'Reports dashboard is…', 'bn' => 'রিপোর্ট ড্যাশবোর্ড…'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Entry point to standard ERP reports', 'bn' => 'স্ট্যান্ডার্ড রিপোর্টের প্রবেশ']],
                    ['id' => 'b', 'text' => ['en' => 'Where you create BOMs', 'bn' => 'BOM তৈরির জায়গা']],
                    ['id' => 'c', 'text' => ['en' => 'GRN approval inbox', 'bn' => 'GRN ইনবক্স']],
                    ['id' => 'd', 'text' => ['en' => 'Payroll only', 'bn' => 'শুধু পে-রোল']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'Use it to jump to P&L, VAT, stock, aging, logistics, and fleet.', 'bn' => 'P&L, VAT, স্টক, aging, লজিস্টিক্স ও ফ্লিট-এ যেতে ব্যবহার করুন।'],
            ],
            [
                'id' => 'rp3',
                'prompt' => ['en' => 'Export center is for…', 'bn' => 'Export center কী জন্য?'],
                'options' => [
                    ['id' => 'a', 'text' => ['en' => 'Downloading CSV/PDF by module and date range', 'bn' => 'মডিউল ও তারিখ অনুযায়ী CSV/PDF']],
                    ['id' => 'b', 'text' => ['en' => 'Creating BOMs', 'bn' => 'BOM তৈরি']],
                    ['id' => 'c', 'text' => ['en' => 'Posting POD', 'bn' => 'POD পোস্ট']],
                    ['id' => 'd', 'text' => ['en' => 'Registering vehicles', 'bn' => 'যান নিবন্ধন']],
                ],
                'correct' => 'a',
                'explain' => ['en' => 'One hub for auditor packs — not scattered per-page export buttons.', 'bn' => 'অডিটর প্যাকের এক কেন্দ্র — পেজ-পেজ এক্সপোর্ট বোতাম নয়।'],
            ],
        ],
    ],
];
