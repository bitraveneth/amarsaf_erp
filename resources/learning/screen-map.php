<?php

/**
 * ERP screen mock definitions for learning walkthroughs.
 * Rendered as UI-friendly "screenshot-style" panels in courses.
 */
return [
    '/admin/products' => [
        'title' => ['en' => 'Products list', 'bn' => 'পণ্য তালিকা'],
        'menu' => ['en' => 'Master data → Products & catalog → Products', 'bn' => 'Master data → Products & catalog → Products'],
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
        'menu' => ['en' => 'Master data → Products & catalog → Add product', 'bn' => 'Master data → Products & catalog → Add product'],
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
        'menu' => ['en' => 'Master data → Products & catalog → Materials → Add', 'bn' => 'Master data → Products & catalog → Materials → Add'],
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
        'menu' => ['en' => 'Purchase → Purchase orders → Create', 'bn' => 'Purchase → Purchase orders → Create'],
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
        'menu' => ['en' => 'Purchase → Purchase orders', 'bn' => 'Purchase → Purchase orders'],
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
        'menu' => ['en' => 'Purchase → Goods receipts (GRN)', 'bn' => 'Purchase → Goods receipts (GRN)'],
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
        'menu' => ['en' => 'Sales & distribution → Orders → Create', 'bn' => 'Sales & distribution → Orders → Create'],
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
        'menu' => ['en' => 'Master data → Warehouses → Create', 'bn' => 'Master data → Warehouses → Create'],
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
        'menu' => ['en' => 'Sales & distribution → Orders', 'bn' => 'Sales & distribution → Orders'],
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
            ['label' => ['en' => 'Logistics / fleet', 'bn' => 'লজিস্টিক্স / ফ্লিট'], 'desc' => ['en' => 'Route cost vs sales', 'bn' => 'রুট খরচ বনাম বিক্রয়']],
            ['label' => ['en' => 'Export center', 'bn' => 'এক্সপোর্ট'], 'desc' => ['en' => 'CSV / PDF packs', 'bn' => 'CSV / PDF প্যাক']],
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
    '/admin/logistics' => [
        'title' => ['en' => 'Logistics dashboard', 'bn' => 'লজিস্টিক্স ড্যাশবোর্ড'],
        'menu' => ['en' => 'Master data → Logistics → Logistics dashboard', 'bn' => 'Master data → Logistics → Logistics dashboard'],
        'path' => '/admin/logistics',
        'highlights' => [
            ['label' => ['en' => 'Month-to-date fleet spend', 'bn' => 'মাসের ফ্লিট খরচ'], 'desc' => ['en' => 'Own-truck fuel and maintenance so far this month.', 'bn' => 'এই মাসের নিজস্ব ট্রাকের জ্বালানি ও রক্ষণাবেক্ষণ।']],
            ['label' => ['en' => 'Open logistics payables', 'bn' => 'বকেয়া ক্যারিয়ার বিল'], 'desc' => ['en' => 'Unpaid hired-carrier invoices.', 'bn' => 'অপরিশোধিত ভাড়া ক্যারিয়ার ইনভয়েস।']],
            ['label' => ['en' => 'New logistics bill', 'bn' => 'নতুন লজিস্টিক্স বিল'], 'desc' => ['en' => 'Post a carrier invoice from the top-right.', 'bn' => 'উপরের ডান থেকে ক্যারিয়ার ইনভয়েস।']],
        ],
        'table' => [
            ['col' => 'Bill #', 'sample' => 'LB-0041'],
            ['col' => 'Carrier', 'sample' => 'XYZ Logistics'],
            ['col' => 'Outstanding', 'sample' => '৳18,400'],
        ],
    ],
    '/admin/vehicles' => [
        'title' => ['en' => 'Vehicle registry', 'bn' => 'যান রেজিস্ট্রি'],
        'menu' => ['en' => 'Master data → Logistics → Vehicle registry', 'bn' => 'Master data → Logistics → Vehicle registry'],
        'path' => '/admin/vehicles',
        'highlights' => [
            ['label' => ['en' => 'Add vehicle', 'bn' => 'যান যোগ'], 'desc' => ['en' => 'Plate, capacity, active flag.', 'bn' => 'নম্বর প্লেট, ক্যাপাসিটি, Active।']],
            ['label' => ['en' => 'Active only', 'bn' => 'Active'], 'desc' => ['en' => 'Inactive vehicles cannot be used on loads.', 'bn' => 'নিষ্ক্রিয় যান লোডে ব্যবহার হয় না।']],
        ],
        'table' => [
            ['col' => 'Plate', 'sample' => 'DHAKA-METRO-GA-12-3456'],
            ['col' => 'Type', 'sample' => 'Van'],
            ['col' => 'Capacity', 'sample' => '120 crates'],
        ],
    ],
    '/admin/vehicle-load' => [
        'title' => ['en' => 'Vehicle loads', 'bn' => 'যান লোড'],
        'menu' => ['en' => 'Master data → Logistics → Vehicle loads', 'bn' => 'Master data → Logistics → Vehicle loads'],
        'path' => '/admin/vehicle-load',
        'highlights' => [
            ['label' => ['en' => 'Trip date + vehicle', 'bn' => 'ট্রিপ তারিখ + যান'], 'desc' => ['en' => 'Pick the truck and day first.', 'bn' => 'আগে ট্রাক ও দিন বেছে নিন।']],
            ['label' => ['en' => 'Assign deliveries', 'bn' => 'ডেলিভারি যোগ'], 'desc' => ['en' => 'Confirmed deliveries sharing this trip.', 'bn' => 'এই ট্রিপের নিশ্চিত ডেলিভারি।']],
            ['label' => ['en' => 'Capacity bar', 'bn' => 'ক্যাপাসিটি'], 'desc' => ['en' => 'Do not overload crates vs vehicle capacity.', 'bn' => 'ক্যাপাসিটির বেশি ক্রেট দেবেন না।']],
        ],
        'form' => [
            ['field' => 'Vehicle', 'required' => true, 'example' => 'DHAKA-METRO-GA-12-3456', 'hint' => ['en' => 'Must be active in registry.', 'bn' => 'রেজিস্ট্রিতে Active থাকতে হবে।']],
            ['field' => 'Route', 'required' => false, 'example' => 'Dhaka North loop', 'hint' => ['en' => 'Used later on route-cost report.', 'bn' => 'পরে রুট-খরচ রিপোর্টে।']],
            ['field' => 'Deliveries', 'required' => true, 'example' => 'SO-1082, SO-1088', 'hint' => ['en' => 'Confirmed orders ready to dispatch.', 'bn' => 'ডিসপ্যাচের নিশ্চিত অর্ডার।']],
        ],
    ],
    '/admin/fleet-expenses' => [
        'title' => ['en' => 'Fleet expenses', 'bn' => 'ফ্লিট খরচ'],
        'menu' => ['en' => 'Master data → Logistics → Fleet expenses', 'bn' => 'Master data → Logistics → Fleet expenses'],
        'path' => '/admin/fleet-expenses',
        'form' => [
            ['field' => 'Vehicle', 'required' => true, 'example' => 'DHAKA-METRO-GA-12-3456', 'hint' => ['en' => 'Own truck this cost belongs to.', 'bn' => 'যে নিজস্ব ট্রাকের খরচ।']],
            ['field' => 'Type', 'required' => true, 'example' => 'Fuel', 'hint' => ['en' => 'Fuel, maintenance, or rent.', 'bn' => 'জ্বালানি, রক্ষণাবেক্ষণ বা ভাড়া।']],
            ['field' => 'Amount', 'required' => true, 'example' => '4,800.00', 'hint' => ['en' => 'Operating cost — not a carrier invoice.', 'bn' => 'অপারেটিং খরচ — ক্যারিয়ার ইনভয়েস নয়।']],
        ],
        'highlights' => [
            ['label' => ['en' => 'Route (optional)', 'bn' => 'রুট (ঐচ্ছিক)'], 'desc' => ['en' => 'Tag the route so Route cost vs sales can group this spend.', 'bn' => 'রুট ট্যাগ করলে রিপোর্টে গ্রুপ হয়।']],
        ],
    ],
    '/admin/logistics/carriers' => [
        'title' => ['en' => 'Transport carriers', 'bn' => 'ট্রান্সপোর্ট ক্যারিয়ার'],
        'menu' => ['en' => 'Master data → Logistics → Transport carriers', 'bn' => 'Master data → Logistics → Transport carriers'],
        'path' => '/admin/logistics/carriers',
        'highlights' => [
            ['label' => ['en' => 'Add carrier', 'bn' => 'ক্যারিয়ার যোগ'], 'desc' => ['en' => 'Hired truck/courier — not a raw-material supplier.', 'bn' => 'ভাড়া ট্রাক/কুরিয়ার — কাঁচামাল সাপ্লায়ার নয়।']],
            ['label' => ['en' => 'Rate cards', 'bn' => 'রেট কার্ড'], 'desc' => ['en' => 'Quoted rates by route, kg, or trip.', 'bn' => 'রুট/কেজি/ট্রিপ অনুযায়ী রেট।']],
        ],
        'table' => [
            ['col' => 'Carrier', 'sample' => 'XYZ Logistics'],
            ['col' => 'Phone', 'sample' => '01711-000000'],
            ['col' => 'Bills', 'sample' => '12'],
        ],
    ],
    '/admin/logistics-bills' => [
        'title' => ['en' => 'Logistics bills', 'bn' => 'লজিস্টিক্স বিল'],
        'menu' => ['en' => 'Master data → Logistics → Logistics bills', 'bn' => 'Master data → Logistics → Logistics bills'],
        'path' => '/admin/logistics-bills',
        'form' => [
            ['field' => 'Carrier', 'required' => true, 'example' => 'XYZ Logistics', 'hint' => ['en' => 'Must exist under Transport carriers.', 'bn' => 'Transport carriers-এ থাকতে হবে।']],
            ['field' => 'Route / trip date', 'required' => false, 'example' => 'Chittagong / 12 Jun', 'hint' => ['en' => 'Links cost to route-cost report.', 'bn' => 'রুট-খরচ রিপোর্টে যুক্ত করে।']],
            ['field' => 'Lines', 'required' => true, 'example' => 'Freight ৳12,000 + loading ৳800', 'hint' => ['en' => 'Freight, loading, demurrage, VAT.', 'bn' => 'ফ্রেইট, লোডিং, ডিমারেজ, VAT।']],
        ],
        'highlights' => [
            ['label' => ['en' => 'Outstanding', 'bn' => 'বকেয়া'], 'desc' => ['en' => 'Pay from the bill — same idea as a supplier bill, different document.', 'bn' => 'বিল থেকে পরিশোধ — সাপ্লায়ার বিলের মতো, আলাদা ডকুমেন্ট।']],
        ],
    ],
    '/admin/reports/route-costs' => [
        'title' => ['en' => 'Route cost vs sales', 'bn' => 'রুট খরচ বনাম বিক্রয়'],
        'menu' => ['en' => 'Reports → Logistics reports → Route cost vs sales', 'bn' => 'Reports → Logistics reports → Route cost vs sales'],
        'path' => '/admin/reports/route-costs',
        'highlights' => [
            ['label' => ['en' => 'Sales by route', 'bn' => 'রুট অনুযায়ী বিক্রয়'], 'desc' => ['en' => 'Delivered/invoiced sales grouped by route.', 'bn' => 'ডেলিভার্ড/ইনভয়েসড বিক্রয় রুট অনুযায়ী।']],
            ['label' => ['en' => 'Fleet + carrier', 'bn' => 'ফ্লিট + ক্যারিয়ার'], 'desc' => ['en' => 'Own-truck expenses plus hired bills.', 'bn' => 'নিজস্ব ট্রাক খরচ + ভাড়া বিল।']],
            ['label' => ['en' => 'Margin after logistics', 'bn' => 'লজিস্টিক্সের পর মার্জিন'], 'desc' => ['en' => 'Negative means the route is costing more than it sells.', 'bn' => 'নেগেটিভ মানে রুট বিক্রয়ের চেয়ে বেশি খরচ।']],
        ],
        'table' => [
            ['col' => 'Route', 'sample' => 'Chittagong'],
            ['col' => 'Sales', 'sample' => '৳240,000'],
            ['col' => 'Logistics', 'sample' => '৳31,200'],
            ['col' => 'After logistics', 'sample' => '৳208,800'],
        ],
    ],
    '/admin/sales-targets' => [
        'title' => ['en' => 'Sales targets', 'bn' => 'বিক্রয় টার্গেট'],
        'menu' => ['en' => 'Sales & distribution → Sales targets', 'bn' => 'Sales & distribution → Sales targets'],
        'path' => '/admin/sales-targets',
        'highlights' => [
            ['label' => ['en' => 'Period + agent', 'bn' => 'পিরিয়ড + এজেন্ট'], 'desc' => ['en' => 'Set qty or value targets per agent.', 'bn' => 'এজেন্ট অনুযায়ী পরিমাণ বা মূল্য টার্গেট।']],
            ['label' => ['en' => 'Progress', 'bn' => 'অগ্রগতি'], 'desc' => ['en' => 'Compare confirmed/invoiced sales to target.', 'bn' => 'নিশ্চিত/ইনভয়েসড বিক্রয় টার্গেটের সাথে।']],
        ],
    ],
    '/admin/gifts' => [
        'title' => ['en' => 'Customer gifts', 'bn' => 'গ্রাহক গিফট'],
        'menu' => ['en' => 'Sales & distribution → Customer gifts', 'bn' => 'Sales & distribution → Customer gifts'],
        'path' => '/admin/gifts',
        'highlights' => [
            ['label' => ['en' => 'Promotional stock', 'bn' => 'প্রমোশনাল স্টক'], 'desc' => ['en' => 'Track free goods given with orders — not billed as normal sales.', 'bn' => 'অর্ডারের সাথে ফ্রি পণ্য — সাধারণ বিক্রয় নয়।']],
        ],
    ],
    '/admin/campaigns' => [
        'title' => ['en' => 'Marketing campaigns', 'bn' => 'মার্কেটিং ক্যাম্পেইন'],
        'menu' => ['en' => 'Sales & distribution → Marketing campaigns', 'bn' => 'Sales & distribution → Marketing campaigns'],
        'path' => '/admin/campaigns',
        'highlights' => [
            ['label' => ['en' => 'Campaign window', 'bn' => 'ক্যাম্পেইন সময়'], 'desc' => ['en' => 'Name, dates, and linked offers or gifts.', 'bn' => 'নাম, তারিখ ও যুক্ত অফার/গিফট।']],
        ],
    ],
    '/admin/accounting-dashboard' => [
        'title' => ['en' => 'Accounting dashboard', 'bn' => 'হিসাব ড্যাশবোর্ড'],
        'menu' => ['en' => 'Accounting → Accounting dashboard', 'bn' => 'Accounting → Accounting dashboard'],
        'path' => '/admin/accounting-dashboard',
        'highlights' => [
            ['label' => ['en' => 'AR / AP snapshot', 'bn' => 'AR / AP সারাংশ'], 'desc' => ['en' => 'What agents owe you and what you owe suppliers.', 'bn' => 'এজেন্টের বকেয়া ও সাপ্লায়ারের বকেয়া।']],
            ['label' => ['en' => 'Jump to invoices', 'bn' => 'ইনভয়েসে যান'], 'desc' => ['en' => 'Open customer invoices or bills from the cards.', 'bn' => 'কার্ড থেকে ইনভয়েস বা বিল খুলুন।']],
        ],
    ],
    '/admin/finance/reconciliation' => [
        'title' => ['en' => 'Bank reconciliation', 'bn' => 'ব্যাংক মিলকরণ'],
        'menu' => ['en' => 'Accounting → Bank reconciliation', 'bn' => 'Accounting → Bank reconciliation'],
        'path' => '/admin/finance/reconciliation',
        'highlights' => [
            ['label' => ['en' => 'Statement vs ledger', 'bn' => 'স্টেটমেন্ট বনাম লেজার'], 'desc' => ['en' => 'Match bank lines to receipts, payments, and expenses.', 'bn' => 'ব্যাংক লাইন রসিদ, পেমেন্ট ও খরচের সাথে মিলান।']],
            ['label' => ['en' => 'Unmatched', 'bn' => 'অমিল'], 'desc' => ['en' => 'Investigate missing receipts or duplicate payments.', 'bn' => 'হারানো রসিদ বা ডুপ্লিকেট পেমেন্ট খুঁজুন।']],
        ],
    ],
    '/admin/export-center' => [
        'title' => ['en' => 'Export center', 'bn' => 'এক্সপোর্ট সেন্টার'],
        'menu' => ['en' => 'Reports & analytics → Export center', 'bn' => 'Reports & analytics → Export center'],
        'path' => '/admin/export-center',
        'highlights' => [
            ['label' => ['en' => 'Module + dates', 'bn' => 'মডিউল + তারিখ'], 'desc' => ['en' => 'Pick invoices, stock, payroll, logistics, or Tally XML.', 'bn' => 'ইনভয়েস, স্টক, পে-রোল, লজিস্টিক্স বা Tally XML।']],
            ['label' => ['en' => 'CSV / PDF', 'bn' => 'CSV / PDF'], 'desc' => ['en' => 'One place for auditor packs — not per-page buttons.', 'bn' => 'অডিটর প্যাকের এক জায়গা।']],
        ],
    ],
    '/admin/packaging' => [
        'title' => ['en' => 'Packaging types', 'bn' => 'প্যাকেজিং'],
        'menu' => ['en' => 'Master data → Products & catalog → Packaging types', 'bn' => 'Master data → Products & catalog → Packaging types'],
        'path' => '/admin/packaging',
        'highlights' => [
            ['label' => ['en' => 'Pack size', 'bn' => 'প্যাক সাইজ'], 'desc' => ['en' => 'Carton, bottle, bulk — how you sell or store.', 'bn' => 'কার্টন, বোতল, বাল্ক — বিক্রি বা স্টোর।']],
        ],
    ],
    '/admin/material-categories' => [
        'title' => ['en' => 'Material categories', 'bn' => 'ম্যাটেরিয়াল ক্যাটাগরি'],
        'menu' => ['en' => 'Master data → Products & catalog → Material categories', 'bn' => 'Master data → Products & catalog → Material categories'],
        'path' => '/admin/material-categories',
        'highlights' => [
            ['label' => ['en' => 'Group materials', 'bn' => 'কাঁচামাল গ্রুপ'], 'desc' => ['en' => 'Bottles, caps, labels — filters POs and stock views.', 'bn' => 'বোতল, ক্যাপ, লেবেল — PO ও স্টক ফিল্টার।']],
        ],
    ],
    '/admin/warehouses-dashboard' => [
        'title' => ['en' => 'Warehouse dashboard', 'bn' => 'গুদাম ড্যাশবোর্ড'],
        'menu' => ['en' => 'Master data → Warehouses → Warehouse dashboard', 'bn' => 'Master data → Warehouses → Warehouse dashboard'],
        'path' => '/admin/warehouses-dashboard',
        'highlights' => [
            ['label' => ['en' => 'Stock snapshot', 'bn' => 'স্টক সারাংশ'], 'desc' => ['en' => 'On-hand by warehouse before you open transfers.', 'bn' => 'স্থানান্তরের আগে গুদাম অনুযায়ী অন-হ্যান্ড।']],
        ],
    ],
    '/admin/delivery-routes' => [
        'title' => ['en' => 'Delivery zones & routes', 'bn' => 'জোন ও রুট'],
        'menu' => ['en' => 'Master data → Logistics → Delivery zones & routes', 'bn' => 'Master data → Logistics → Delivery zones & routes'],
        'path' => '/admin/delivery-routes',
        'highlights' => [
            ['label' => ['en' => 'Zone', 'bn' => 'জোন'], 'desc' => ['en' => 'Links agents in an area to a named route.', 'bn' => 'এলাকার এজেন্টকে একটি রুটের সাথে যুক্ত করে।']],
            ['label' => ['en' => 'Used on', 'bn' => 'ব্যবহার'], 'desc' => ['en' => 'Deliveries, vehicle loads, and route-cost reports.', 'bn' => 'ডেলিভারি, যান লোড ও রুট-খরচ রিপোর্ট।']],
        ],
    ],
    '/admin/reports/logistics' => [
        'title' => ['en' => 'Logistics reports', 'bn' => 'লজিস্টিক্স রিপোর্ট'],
        'menu' => ['en' => 'Reports → Logistics reports', 'bn' => 'Reports → Logistics reports'],
        'path' => '/admin/reports/logistics',
        'highlights' => [
            ['label' => ['en' => 'Bills summary', 'bn' => 'বিল সারাংশ'], 'desc' => ['en' => 'Carrier invoices and payment status.', 'bn' => 'ক্যারিয়ার ইনভয়েস ও পরিশোধ স্ট্যাটাস।']],
            ['label' => ['en' => 'Fleet expenses', 'bn' => 'ফ্লিট খরচ'], 'desc' => ['en' => 'Fuel, maintenance, rent by vehicle.', 'bn' => 'যান অনুযায়ী জ্বালানি, রক্ষণাবেক্ষণ, ভাড়া।']],
            ['label' => ['en' => 'Route cost vs sales', 'bn' => 'রুট খরচ বনাম বিক্রয়'], 'desc' => ['en' => 'Is the route still profitable after logistics?', 'bn' => 'লজিস্টিক্সের পর রুট লাভজনক?']],
        ],
    ],
];
