<?php

/**
 * ERP screen mock definitions for learning walkthroughs.
 * Rendered as UI-friendly "screenshot-style" panels in courses.
 */
return [
    '/admin/products' => [
        'title' => ['en' => 'Products list', 'bn' => 'পণ্য তালিকা'],
        'menu' => ['en' => 'Control → Products → Products', 'bn' => 'Control → Products → Products'],
        'path' => '/admin/products',
        'highlights' => [
            ['label' => ['en' => 'Add product', 'bn' => 'Add product'], 'desc' => ['en' => 'Top-right button to create a new sellable SKU.', 'bn' => 'নতুন বিক্রয়যোগ্য SKU তৈরি।']],
            ['label' => ['en' => 'Search / filter', 'bn' => 'খুঁজুন / ফিল্টার'], 'desc' => ['en' => 'Find products by name or SKU.', 'bn' => 'নাম বা SKU দিয়ে খুঁজুন।']],
            ['label' => ['en' => 'Active toggle', 'bn' => 'Active'], 'desc' => ['en' => 'Inactive products cannot be sold.', 'bn' => 'নিষ্ক্রিয় পণ্য বিক্রি হয় না।']],
        ],
        'table' => [
            ['col' => 'SKU', 'sample' => 'SAF-500ML-CTN'],
            ['col' => 'Name', 'sample' => '500ml Water Carton'],
            ['col' => 'Base price', 'sample' => '৳450'],
            ['col' => 'Tax class', 'sample' => 'VAT 15%'],
        ],
    ],
    '/admin/products/create' => [
        'title' => ['en' => 'Add product form', 'bn' => 'পণ্য যোগ ফর্ম'],
        'menu' => ['en' => 'Control → Products → Add product', 'bn' => 'Control → Products → Add product'],
        'path' => '/admin/products/create',
        'form' => [
            ['field' => 'Product name', 'required' => true, 'example' => 'SAF 500ml Carton 12x', 'hint' => ['en' => 'Name agents recognise on orders.', 'bn' => 'অর্ডারে এজেন্ট যে নাম দেখে।']],
            ['field' => 'SKU / code', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Unique — never duplicate.', 'bn' => 'অনন্য — দুবার নয়।']],
            ['field' => 'Base price', 'required' => true, 'example' => '450.00', 'hint' => ['en' => 'Net excl. VAT unless price list overrides.', 'bn' => 'নেট VAT বাদে।']],
            ['field' => 'Tax class', 'required' => true, 'example' => 'Standard 15%', 'hint' => ['en' => 'Controls VAT on invoices.', 'bn' => 'ইনভয়েসে VAT নিয়ন্ত্রণ।']],
            ['field' => 'UOM', 'required' => false, 'example' => 'carton', 'hint' => ['en' => 'How you sell (carton, piece).', 'bn' => 'বিক্রয় ইউনিট।']],
        ],
    ],
    '/admin/materials/create' => [
        'title' => ['en' => 'Add material form', 'bn' => 'কাঁচামাল যোগ'],
        'menu' => ['en' => 'Control → Products → Materials → Add', 'bn' => 'Control → Products → Materials → Add'],
        'path' => '/admin/materials/create',
        'form' => [
            ['field' => 'Material name', 'required' => true, 'example' => 'PET Bottle 500ml', 'hint' => ['en' => 'What warehouse staff call it.', 'bn' => 'গুদাম স্টাফের নাম।']],
            ['field' => 'Material type', 'required' => true, 'example' => 'Raw', 'hint' => ['en' => 'Raw = stock. Service = no qty.', 'bn' => 'Raw = স্টক। Service = পরিমাণ নয়।']],
            ['field' => 'SKU', 'required' => true, 'example' => 'RM-PET-500', 'hint' => ['en' => 'Used on PO and BOM.', 'bn' => 'PO ও BOM-এ ব্যবহার।']],
            ['field' => 'Standard cost', 'required' => false, 'example' => '3.50', 'hint' => ['en' => 'Default for BOM costing.', 'bn' => 'BOM খরচের ডিফল্ট।']],
        ],
    ],
    '/admin/purchase-orders/create' => [
        'title' => ['en' => 'Create purchase order', 'bn' => 'ক্রয় অর্ডার তৈরি'],
        'menu' => ['en' => 'Control → Suppliers → Purchase orders → Create', 'bn' => 'Control → Suppliers → Purchase orders → Create'],
        'path' => '/admin/purchase-orders/create',
        'form' => [
            ['field' => 'Supplier', 'required' => true, 'example' => 'ABC Packaging Ltd', 'hint' => ['en' => 'Must exist in Suppliers.', 'bn' => 'Suppliers-এ থাকতে হবে।']],
            ['field' => 'Order date', 'required' => true, 'example' => 'Today', 'hint' => ['en' => 'When PO is raised.', 'bn' => 'PO তারিখ।']],
            ['field' => 'Line: Material', 'required' => true, 'example' => 'RM-PET-500 × 10,000', 'hint' => ['en' => 'Add rows for each material.', 'bn' => 'প্রতি কাঁচামাল আলাদা লাইন।']],
            ['field' => 'Status', 'required' => false, 'example' => 'Draft → Approve', 'hint' => ['en' => 'Only approved POs receive GRN.', 'bn' => 'অনুমোদিত PO-তেই GRN।']],
        ],
    ],
    '/admin/purchase-orders' => [
        'title' => ['en' => 'Purchase orders inbox', 'bn' => 'ক্রয় অর্ডার ইনবক্স'],
        'menu' => ['en' => 'Control → Suppliers → Purchase orders', 'bn' => 'Control → Suppliers → Purchase orders'],
        'path' => '/admin/purchase-orders',
        'highlights' => [
            ['label' => ['en' => 'Draft / Approved tabs', 'bn' => 'Draft / Approved'], 'desc' => ['en' => 'Filter by status.', 'bn' => 'স্ট্যাটাস ফিল্টার।']],
            ['label' => ['en' => 'Receive goods', 'bn' => 'Receive goods'], 'desc' => ['en' => 'On approved PO — starts GRN.', 'bn' => 'অনুমোদিত PO থেকে GRN।']],
        ],
        'statuses' => [
            ['status' => 'Draft', 'meaning' => ['en' => 'Being edited — no receipt yet', 'bn' => 'সম্পাদনা — গ্রহণ হয়নি']],
            ['status' => 'Approved', 'meaning' => ['en' => 'Ready for supplier delivery & GRN', 'bn' => 'ডেলিভারি ও GRN-এর জন্য প্রস্তুত']],
            ['status' => 'Partial received', 'meaning' => ['en' => 'Some qty already on GRN', 'bn' => 'আংশিক GRN হয়েছে']],
            ['status' => 'Received', 'meaning' => ['en' => 'Fully received', 'bn' => 'সম্পূর্ণ গ্রহণ']],
        ],
    ],
    '/admin/goods-receipts' => [
        'title' => ['en' => 'GRN — goods receipt', 'bn' => 'GRN — পণ্য গ্রহণ'],
        'menu' => ['en' => 'Control → Suppliers → GRN inbox', 'bn' => 'Control → Suppliers → GRN inbox'],
        'path' => '/admin/goods-receipts',
        'form' => [
            ['field' => 'Warehouse', 'required' => true, 'example' => 'Factory Main', 'hint' => ['en' => 'Where stock will land.', 'bn' => 'স্টক কোথায় যাবে।']],
            ['field' => 'Received qty', 'required' => true, 'example' => '6,000 of 10,000', 'hint' => ['en' => 'Partial receipt is OK.', 'bn' => 'আংশিক গ্রহণ চলে।']],
            ['field' => 'QC status', 'required' => true, 'example' => 'Approved', 'hint' => ['en' => 'Only Approved adds stock.', 'bn' => 'Approved-এই স্টক বাড়ে।']],
        ],
        'highlights' => [
            ['label' => ['en' => 'Pending approval', 'bn' => 'Pending approval'], 'desc' => ['en' => 'Dual sign-off before stock posts.', 'bn' => 'স্টক পোস্টের আগে দ্বৈত অনুমোদন।']],
        ],
    ],
    '/admin/production/create' => [
        'title' => ['en' => 'New production run', 'bn' => 'নতুন উৎপাদন রান'],
        'menu' => ['en' => 'Manufacturing → Production → Create', 'bn' => 'Manufacturing → Production → Create'],
        'path' => '/admin/production/create',
        'form' => [
            ['field' => 'Product', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Must have active BOM.', 'bn' => 'সক্রিয় BOM লাগে।']],
            ['field' => 'Batch / lot', 'required' => true, 'example' => 'LOT-2026-0607', 'hint' => ['en' => 'Traceability for QC.', 'bn' => 'QC ট্রেসেবিলিটি।']],
            ['field' => 'Quantity', 'required' => true, 'example' => '500 cartons', 'hint' => ['en' => 'Output qty to produce.', 'bn' => 'উৎপাদন পরিমাণ।']],
            ['field' => 'Line / shift', 'required' => false, 'example' => 'Line 1 / Morning', 'hint' => ['en' => 'Optional planning fields.', 'bn' => 'ঐচ্ছিক পরিকল্পনা।']],
        ],
    ],
    '/admin/production' => [
        'title' => ['en' => 'Production runs list', 'bn' => 'উৎপাদন রান তালিকা'],
        'menu' => ['en' => 'Manufacturing → Production runs', 'bn' => 'Manufacturing → Production runs'],
        'path' => '/admin/production',
        'highlights' => [
            ['label' => ['en' => 'QC column', 'bn' => 'QC'], 'desc' => ['en' => 'Must pass before stock confirm.', 'bn' => 'স্টক নিশ্চিতের আগে QC।']],
            ['label' => ['en' => 'Material cost', 'bn' => 'Material cost'], 'desc' => ['en' => 'Used for COGS estimate.', 'bn' => 'COGS অনুমানে ব্যবহার।']],
        ],
    ],
    '/admin/orders/create' => [
        'title' => ['en' => 'Create sales order', 'bn' => 'বিক্রয় অর্ডার তৈরি'],
        'menu' => ['en' => 'Sales → Orders → Create', 'bn' => 'Sales → Orders → Create'],
        'path' => '/admin/orders/create',
        'form' => [
            ['field' => 'Agent', 'required' => true, 'example' => 'Dhaka Distributor', 'hint' => ['en' => 'Must be active agent.', 'bn' => 'সক্রিয় এজেন্ট।']],
            ['field' => 'Product lines', 'required' => true, 'example' => 'SAF-500ML-CTN × 100', 'hint' => ['en' => 'FG qty ordered.', 'bn' => 'অর্ডার পরিমাণ।']],
            ['field' => 'Delivery date', 'required' => true, 'example' => '+2 days', 'hint' => ['en' => 'Check FG stock first.', 'bn' => 'আগে FG স্টক দেখুন।']],
        ],
    ],
    '/admin/finance' => [
        'title' => ['en' => 'Customer invoices', 'bn' => 'গ্রাহক ইনভয়েস'],
        'menu' => ['en' => 'Accounting → Customer invoices', 'bn' => 'Accounting → Customer invoices'],
        'path' => '/admin/finance',
        'highlights' => [
            ['label' => ['en' => 'Create from order', 'bn' => 'অর্ডার থেকে তৈরি'], 'desc' => ['en' => 'Pick delivered sales order.', 'bn' => 'ডেলিভার্ড অর্ডার বেছে নিন।']],
            ['label' => ['en' => 'Add receipt', 'bn' => 'রসিদ যোগ'], 'desc' => ['en' => 'Posts Dr Bank, Cr AR.', 'bn' => 'Dr Bank, Cr AR পোস্ট।']],
        ],
        'table' => [
            ['col' => 'Invoice #', 'sample' => 'INV-0042'],
            ['col' => 'Agent', 'sample' => 'Dhaka Dist.'],
            ['col' => 'Net', 'sample' => '৳50,000'],
            ['col' => 'VAT', 'sample' => '৳7,500'],
            ['col' => 'Outstanding', 'sample' => '৳57,500'],
        ],
    ],
    '/admin/reports/pl' => [
        'title' => ['en' => 'Profit & loss report', 'bn' => 'লাভ-ক্ষতি রিপোর্ট'],
        'menu' => ['en' => 'Reports → Profit & loss', 'bn' => 'Reports → Profit & loss'],
        'path' => '/admin/reports/pl',
        'highlights' => [
            ['label' => ['en' => 'Net sales', 'bn' => 'নেট বিক্রয়'], 'desc' => ['en' => 'Revenue excl. VAT in period.', 'bn' => 'সময়কালের VAT বাদে আয়।']],
            ['label' => ['en' => 'COGS', 'bn' => 'COGS'], 'desc' => ['en' => 'GL or estimated from production.', 'bn' => 'GL বা উৎপাদন অনুমান।']],
            ['label' => ['en' => 'Gross / Net profit', 'bn' => 'মোট / নিট লাভ'], 'desc' => ['en' => 'Bottom line after expenses.', 'bn' => 'খরচের পর নিট ফলাফল।']],
        ],
    ],
    '/admin/boms/create' => [
        'title' => ['en' => 'Create BOM', 'bn' => 'BOM তৈরি'],
        'menu' => ['en' => 'Manufacturing → BOMs → Create', 'bn' => 'Manufacturing → BOMs → Create'],
        'path' => '/admin/boms/create',
        'form' => [
            ['field' => 'Finished product', 'required' => true, 'example' => 'SAF-500ML-CTN', 'hint' => ['en' => 'Output SKU', 'bn' => 'আউটপুট']],
            ['field' => 'Material lines + qty', 'required' => true, 'example' => 'Per 1 unit', 'hint' => ['en' => 'Recipe components', 'bn' => 'রেসিপি']],
        ],
    ],
    '/admin/agents/create' => [
        'title' => ['en' => 'Add agent', 'bn' => 'এজেন্ট যোগ'],
        'menu' => ['en' => 'Sales → Agents → Create', 'bn' => 'Sales → Agents → Create'],
        'path' => '/admin/agents/create',
        'form' => [
            ['field' => 'Agent name', 'required' => true, 'example' => 'Dhaka Distributor', 'hint' => ['en' => 'Legal/trade name', 'bn' => 'নাম']],
            ['field' => 'Zone', 'required' => true, 'example' => 'Dhaka North', 'hint' => ['en' => 'For routes and pricing', 'bn' => 'রুট ও মূল্য']],
            ['field' => 'Credit limit', 'required' => false, 'example' => '৳500,000', 'hint' => ['en' => 'Max outstanding AR', 'bn' => 'সর্বোচ্চ বকেয়া']],
        ],
    ],
    '/admin/warehouses/create' => [
        'title' => ['en' => 'Add warehouse', 'bn' => 'গুদাম যোগ'],
        'menu' => ['en' => 'Control → Warehouses → Create', 'bn' => 'Control → Warehouses → Create'],
        'path' => '/admin/warehouses/create',
        'form' => [
            ['field' => 'Name', 'required' => true, 'example' => 'Factory Main', 'hint' => ['en' => 'Recognisable name', 'bn' => 'নাম']],
            ['field' => 'Type', 'required' => true, 'example' => 'Factory', 'hint' => ['en' => 'Factory vs depot', 'bn' => 'Factory বনাম depot']],
        ],
    ],
    '/admin/inventory' => [
        'title' => ['en' => 'Inventory dashboard', 'bn' => 'ইনভেন্টরি ড্যাশবোর্ড'],
        'menu' => ['en' => 'Inventory → Dashboard', 'bn' => 'Inventory → Dashboard'],
        'path' => '/admin/inventory',
        'highlights' => [
            ['label' => ['en' => 'FG / RM tabs', 'bn' => 'FG / RM'], 'desc' => ['en' => 'Finished vs raw stock', 'bn' => 'তৈরি বনাম কাঁচামাল']],
            ['label' => ['en' => 'Low stock alerts', 'bn' => 'লো স্টক'], 'desc' => ['en' => 'Plan procurement', 'bn' => 'PO পরিকল্পনা']],
        ],
    ],
    '/admin/orders' => [
        'title' => ['en' => 'Sales orders list', 'bn' => 'বিক্রয় অর্ডার'],
        'menu' => ['en' => 'Sales → Orders', 'bn' => 'Sales → Orders'],
        'path' => '/admin/orders',
        'highlights' => [
            ['label' => ['en' => 'Confirm', 'bn' => 'Confirm'], 'desc' => ['en' => 'Reserves stock', 'bn' => 'স্টক রিজার্ভ']],
            ['label' => ['en' => 'Status column', 'bn' => 'স্ট্যাটাস'], 'desc' => ['en' => 'Draft → Confirmed → Delivered', 'bn' => 'ধাপে ধাপে']],
        ],
    ],
    '/admin/reports-dashboard' => [
        'title' => ['en' => 'Reports dashboard', 'bn' => 'রিপোর্ট ড্যাশবোর্ড'],
        'menu' => ['en' => 'Reports → Dashboard', 'bn' => 'Reports → Dashboard'],
        'path' => '/admin/reports-dashboard',
        'highlights' => [
            ['label' => ['en' => 'P&L', 'bn' => 'P&L'], 'desc' => ['en' => 'Profit and loss', 'bn' => 'লাভ-ক্ষতি']],
            ['label' => ['en' => 'Stock valuation', 'bn' => 'স্টক'], 'desc' => ['en' => 'Inventory value', 'bn' => 'মজুদ মূল্য']],
            ['label' => ['en' => 'AR aging', 'bn' => 'AR aging'], 'desc' => ['en' => 'Collections', 'bn' => 'আদায়']],
        ],
    ],
    '/admin/deliveries/pod' => [
        'title' => ['en' => 'Proof of delivery (POD)', 'bn' => 'POD'],
        'menu' => ['en' => 'Delivery → POD', 'bn' => 'Delivery → POD'],
        'path' => '/admin/deliveries/pod',
        'form' => [
            ['field' => 'Delivered qty', 'required' => true, 'example' => '98 of 100', 'hint' => ['en' => 'Actual qty agent received.', 'bn' => 'এজেন্ট যা পেয়েছে।']],
            ['field' => 'Short / damage', 'required' => false, 'example' => '2 short', 'hint' => ['en' => 'Record exceptions.', 'bn' => 'ব্যতিক্রম লিখুন।']],
            ['field' => 'Receiver name', 'required' => true, 'example' => 'Rahim Uddin', 'hint' => ['en' => 'Who signed at agent.', 'bn' => 'গ্রহীতার নাম।']],
        ],
    ],
];
