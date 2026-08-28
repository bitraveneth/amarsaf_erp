<?php

/**
 * Bilingual Learning Hub module catalog.
 * Each module: slug, order, icon, title, summary, flowchart (mermaid), steps, examples, tips.
 * Edit here to update in-app Learning Hub + keep docs/ in sync for PDF/export.
 */
return [
    [
        'slug' => 'overview',
        'order' => 0,
        'icon' => 'overview',
        'title' => [
            'en' => 'How the whole system works',
            'bn' => 'পুরো সিস্টেম কীভাবে চলে',
        ],
        'summary' => [
            'en' => 'Saf ERP connects buying, making, storing, selling, delivering, logistics cost, and billing in one flow.',
            'bn' => 'Saf ERP কেনাকাটা, উৎপাদন, মজুদ, বিক্রয়, ডেলিভারি, লজিস্টিক্স খরচ ও হিসাব এক ধারায় জোড়ে।',
        ],
        'flowchart' => [
            'en' => <<<'MERMAID'
flowchart LR
    A["Buy materials"] --> B["Receive GRN"]
    B --> C["Produce with BOM"]
    C --> D["QC and stock"]
    D --> E["Agent order"]
    E --> F["Deliver POD"]
    F --> G["Logistics cost"]
    G --> H["Invoice and receipt"]
MERMAID,
            'bn' => <<<'MERMAID'
flowchart LR
    A["কাঁচামাল কিনুন"] --> B["GRN গ্রহণ"]
    B --> C["BOM দিয়ে উৎপাদন"]
    C --> D["QC ও স্টক"]
    D --> E["এজেন্ট অর্ডার"]
    E --> F["ডেলিভারি POD"]
    F --> G["লজিস্টিক্স খরচ"]
    G --> H["ইনভয়েস ও রসিদ"]
MERMAID,
        ],
        'steps' => [
            [
                'title' => ['en' => 'Set up master data', 'bn' => 'মাস্টার ডেটা সেটআপ'],
                'body' => ['en' => 'Add materials, products, agents, suppliers, and warehouses first.', 'bn' => 'আগে কাঁচামাল, পণ্য, এজেন্ট, সাপ্লায়ার ও গুদাম যোগ করুন।'],
                'path' => '/admin/products',
            ],
            [
                'title' => ['en' => 'Fill the factory', 'bn' => 'কারখানায় মজুদ করুন'],
                'body' => ['en' => 'Purchase order → GRN puts raw materials in stock.', 'bn' => 'ক্রয় অর্ডার → GRN দিয়ে কাঁচামাল স্টকে আসে।'],
                'path' => '/admin/purchase-orders',
            ],
            [
                'title' => ['en' => 'Make finished goods', 'bn' => 'তৈরি পণ্য উৎপাদন'],
                'body' => ['en' => 'BOM → production run → QC → warehouse confirms stock.', 'bn' => 'BOM → উৎপাদন রান → QC → গুদাম স্টক নিশ্চিত করে।'],
                'path' => '/admin/production',
            ],
            [
                'title' => ['en' => 'Sell, deliver, and move', 'bn' => 'বিক্রি, ডেলিভারি ও সরান'],
                'body' => ['en' => 'Sales order → delivery POD → logistics cost (fleet or carrier) → invoice → receipt from agent.', 'bn' => 'বিক্রয় অর্ডার → ডেলিভারি POD → লজিস্টিক্স খরচ (ফ্লিট বা ক্যারিয়ার) → ইনভয়েস → এজেন্টের কাছ থেকে টাকা।'],
                'path' => '/admin/orders',
            ],
        ],
        'examples' => [
            [
                'title' => ['en' => 'Example: one carton of water', 'bn' => 'উদাহরণ: এক কার্টন পানি'],
                'body' => [
                    'en' => 'You buy bottles and caps (PO + GRN). BOM says 12 bottles + 1 carton per output. Production makes SAF-500ML-CTN. Agent orders 100 cartons. You deliver and invoice.',
                    'bn' => 'আপনি বোতল ও ক্যাপ কিনেন (PO + GRN)। BOM বলে প্রতি আউটপুটে ১২ বোতল + ১ কার্টন। উৎপাদনে SAF-500ML-CTN তৈরি। এজেন্ট ১০০ কার্টন অর্ডার দেয়। আপনি ডেলিভারি ও ইনভয়েস করেন।',
                ],
            ],
        ],
        'tips' => [
            ['en' => 'Every document links to the next: PO → GRN → production → sales → delivery → logistics bill / fleet cost → invoice.', 'bn' => 'প্রতিটি ডকুমেন্ট পরেরটির সাথে যুক্ত: PO → GRN → উৎপাদন → বিক্রয় → ডেলিভারি → লজিস্টিক্স বিল / ফ্লিট খরচ → ইনভয়েস।'],
            ['en' => 'If stock is wrong, fix master data or GRN before blaming sales.', 'bn' => 'স্টক ভুল হলে বিক্রয় নয়, আগে মাস্টার ডেটা বা GRN ঠিক করুন।'],
        ],
    ],
    [
        'slug' => 'products',
        'order' => 1,
        'icon' => 'products',
        'title' => ['en' => 'Products and materials', 'bn' => 'পণ্য ও কাঁচামাল'],
        'summary' => [
            'en' => 'Materials are what you buy and use. Products are what you sell.',
            'bn' => 'কাঁচামাল যা আপনি কিনে ব্যবহার করেন। পণ্য যা আপনি বিক্রি করেন।',
        ],
        'flowchart' => [
            'en' => "flowchart TD\n    A[\"Add materials\"] --> B[\"Add finished products\"]\n    B --> C[\"Ready for BOM and sales\"]",
            'bn' => "flowchart TD\n    A[\"কাঁচামাল যোগ\"] --> B[\"তৈরি পণ্য যোগ\"]\n    B --> C[\"BOM ও বিক্রয়ের জন্য প্রস্তুত\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Add a material', 'bn' => 'কাঁচামাল যোগ'], 'body' => ['en' => 'Control → Materials → Add. Set SKU, type (raw/service), UOM, standard cost.', 'bn' => 'Control → Materials → Add। SKU, ধরন (raw/service), UOM, স্ট্যান্ডার্ড কস্ট দিন।'], 'path' => '/admin/materials/create'],
            ['title' => ['en' => 'Add a product', 'bn' => 'পণ্য যোগ'], 'body' => ['en' => 'Control → Products → Add. Name, SKU, base price, tax class.', 'bn' => 'Control → Products → Add। নাম, SKU, বেস প্রাইস, ট্যাক্স ক্লাস।'], 'path' => '/admin/products/create'],
        ],
        'examples' => [
            ['title' => ['en' => 'SKU example', 'bn' => 'SKU উদাহরণ'], 'body' => ['en' => 'RM-PET-500 = raw bottle. SAF-500ML-CTN = sellable carton of 12.', 'bn' => 'RM-PET-500 = কাঁচা বোতল। SAF-500ML-CTN = বিক্রয়যোগ্য ১২-বোতলের কার্টন।']],
        ],
        'tips' => [
            ['en' => 'Never use the same SKU twice.', 'bn' => 'একই SKU দুবার ব্যবহার করবেন না।'],
        ],
    ],
    [
        'slug' => 'agents',
        'order' => 2,
        'icon' => 'agents',
        'title' => ['en' => 'Agents and pricing', 'bn' => 'এজেন্ট ও মূল্য'],
        'summary' => ['en' => 'Register agents, set prices and commission rules.', 'bn' => 'এজেন্ট নিবন্ধন, মূল্য ও কমিশন নিয়ম সেট করুন।'],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Create agent\"] --> B[\"Price list\"] --> C[\"Sales order\"]",
            'bn' => "flowchart LR\n    A[\"এজেন্ট তৈরি\"] --> B[\"প্রাইস লিস্ট\"] --> C[\"বিক্রয় অর্ডার\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create agent', 'bn' => 'এজেন্ট তৈরি'], 'body' => ['en' => 'Add name, zone, credit limit, phone.', 'bn' => 'নাম, জোন, ক্রেডিট লিমিট, ফোন দিন।'], 'path' => '/admin/agents/create'],
            ['title' => ['en' => 'Price list', 'bn' => 'প্রাইস লিস্ট'], 'body' => ['en' => 'Set special prices per agent and product if needed.', 'bn' => 'প্রয়োজনে এজেন্ট ও পণ্য অনুযায়ী আলাদা মূল্য দিন।'], 'path' => '/admin/products-price-list'],
        ],
        'examples' => [['title' => ['en' => 'Zone pricing', 'bn' => 'জোন ভিত্তিক মূল্য'], 'body' => ['en' => 'Dhaka agent gets base price; rural agent may have +5% on transport.', 'bn' => 'ঢাকার এজেন্ট বেস প্রাইস; গ্রামীণ এজেন্টে পরিবহনে +৫% হতে পারে।']]],
        'tips' => [['en' => 'Inactive agents cannot be selected on new orders.', 'bn' => 'নিষ্ক্রিয় এজেন্ট নতুন অর্ডারে আসে না।']],
    ],
    [
        'slug' => 'procurement',
        'order' => 3,
        'icon' => 'suppliers',
        'title' => ['en' => 'Procurement — PO and GRN', 'bn' => 'ক্রয় — PO ও GRN'],
        'summary' => ['en' => 'Buy from suppliers and receive into warehouse stock.', 'bn' => 'সাপ্লায়ার থেকে কিনে গুদামে স্টকে নিন।'],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Draft PO\"] --> B[\"Approve\"] --> C[\"GRN receive\"] --> D[\"Stock in\"]",
            'bn' => "flowchart LR\n    A[\"ড্রাফট PO\"] --> B[\"অনুমোদন\"] --> C[\"GRN গ্রহণ\"] --> D[\"স্টকে প্রবেশ\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create PO', 'bn' => 'PO তৈরি'], 'body' => ['en' => 'Pick supplier, add material lines, save as draft, then approve.', 'bn' => 'সাপ্লায়ার বেছে লাইন যোগ, ড্রাফটে সেভ, তারপর অনুমোদন।'], 'path' => '/admin/purchase-orders/create'],
            ['title' => ['en' => 'Post GRN', 'bn' => 'GRN পোস্ট'], 'body' => ['en' => 'Receive from PO. Approved QC lines add stock. Partial qty is OK.', 'bn' => 'PO থেকে গ্রহণ। অনুমোদিত QC লাইন স্টক বাড়ায়। আংশিক পরিমাণ চলে।'], 'path' => '/admin/goods-receipts'],
        ],
        'examples' => [['title' => ['en' => 'Partial receipt', 'bn' => 'আংশিক গ্রহণ'], 'body' => ['en' => 'PO 10,000 bottles; first GRN 6,000; second GRN 4,000. PO becomes fully received.', 'bn' => 'PO ১০,০০০ বোতল; প্রথম GRN ৬,০০০; দ্বিতীয় GRN ৪,০০০। PO সম্পূর্ণ গ্রহণ হয়।']]],
        'tips' => [['en' => 'Service lines (labour) confirm without stock quantity.', 'bn' => 'সার্ভিস লাইন (শ্রম) স্টক ছাড়াই নিশ্চিত হয়।']],
    ],
    [
        'slug' => 'warehouses',
        'order' => 4,
        'icon' => 'warehouses',
        'title' => ['en' => 'Warehouses and locations', 'bn' => 'গুদাম ও লোকেশন'],
        'summary' => ['en' => 'Define where stock lives. Vehicles, carriers, and route cost live in Logistics.', 'bn' => 'স্টক কোথায় থাকে তা নির্ধারণ করুন। যান, ক্যারিয়ার ও রুট খরচ Logistics-এ।'],
        'flowchart' => [
            'en' => "flowchart TD\n    A[\"Warehouse\"] --> B[\"Locations / bins\"]\n    B --> C[\"Stock in / out\"]",
            'bn' => "flowchart TD\n    A[\"গুদাম\"] --> B[\"লোকেশন / বিন\"]\n    B --> C[\"স্টক ইন / আউট\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create warehouse', 'bn' => 'গুদাম তৈরি'], 'body' => ['en' => 'Factory for production; depot for sales area. Open Warehouse dashboard for a snapshot.', 'bn' => 'উৎপাদনের জন্য Factory; বিক্রয় এলাকার জন্য depot। সারাংশের জন্য Warehouse dashboard খুলুন।'], 'path' => '/admin/warehouses/create'],
            ['title' => ['en' => 'Warehouse locations', 'bn' => 'গুদাম লোকেশন'], 'body' => ['en' => 'Bins and aisles inside a warehouse for pick accuracy.', 'bn' => 'পিক সঠিকতার জন্য গুদামের ভিতর বিন ও অ্যাসল।'], 'path' => '/admin/warehouse-locations'],
        ],
        'examples' => [],
        'tips' => [
            ['en' => 'Production usually uses a Factory-type warehouse.', 'bn' => 'উৎপাদন সাধারণত Factory টাইপ গুদাম ব্যবহার করে।'],
            ['en' => 'Fleet, carriers, and logistics bills are in **Logistics**, not Warehouses.', 'bn' => 'ফ্লিট, ক্যারিয়ার ও লজিস্টিক্স বিল **Logistics**-এ — Warehouses-এ নয়।'],
        ],
    ],
    [
        'slug' => 'logistics',
        'order' => 4.5,
        'icon' => 'inventory',
        'title' => ['en' => 'Logistics & fleet', 'bn' => 'লজিস্টিক্স ও ফ্লিট'],
        'summary' => [
            'en' => 'Own trucks vs hired carriers: vehicle loads, fleet spend, logistics bills, and route profitability — separate from Delivery POD.',
            'bn' => 'নিজস্ব ট্রাক বনাম ভাড়া ক্যারিয়ার: যান লোড, ফ্লিট খরচ, লজিস্টিক্স বিল ও রুট লাভজনকতা — Delivery POD থেকে আলাদা।',
        ],
        'flowchart' => [
            'en' => <<<'MERMAID'
flowchart LR
    A["Own fleet"] --> B["Vehicle load"]
    B --> C["Fleet expense"]
    D["Hired carrier"] --> E["Rate card"]
    E --> F["Logistics bill"]
    C --> G["Route cost vs sales"]
    F --> G
MERMAID,
            'bn' => <<<'MERMAID'
flowchart LR
    A["নিজস্ব ফ্লিট"] --> B["যান লোড"]
    B --> C["ফ্লিট খরচ"]
    D["ভাড়া ক্যারিয়ার"] --> E["রেট কার্ড"]
    E --> F["লজিস্টিক্স বিল"]
    C --> G["রুট খরচ বনাম বিক্রয়"]
    F --> G
MERMAID,
        ],
        'steps' => [
            ['title' => ['en' => 'Open logistics dashboard', 'bn' => 'লজিস্টিক্স ড্যাশবোর্ড'], 'body' => ['en' => 'Master data → Logistics → Logistics dashboard. See month-to-date fleet spend, unpaid carrier bills, and active vehicles.', 'bn' => 'Master data → Logistics → Logistics dashboard। মাসের ফ্লিট খরচ, বকেয়া ক্যারিয়ার বিল ও সক্রিয় যান দেখুন।'], 'path' => '/admin/logistics'],
            ['title' => ['en' => 'Register vehicles', 'bn' => 'যান নিবন্ধন'], 'body' => ['en' => 'Vehicle registry: plate, capacity, active flag. Used on vehicle loads and fleet expenses.', 'bn' => 'Vehicle registry: নম্বর প্লেট, ক্যাপাসিটি, Active। Vehicle load ও fleet expense-এ ব্যবহার।'], 'path' => '/admin/vehicles'],
            ['title' => ['en' => 'Plan a vehicle load', 'bn' => 'যান লোড পরিকল্পনা'], 'body' => ['en' => 'Assign confirmed deliveries to a truck trip. Capacity and route help dispatch — this is ops, not yet a GL bill.', 'bn' => 'নিশ্চিত ডেলিভারি একটি ট্রাক ট্রিপে দিন। ক্যাপাসিটি ও রুট ডিসপ্যাচে সাহায্য করে — এখনো GL বিল নয়।'], 'path' => '/admin/vehicle-load'],
            ['title' => ['en' => 'Record fleet expenses', 'bn' => 'ফ্লিট খরচ'], 'body' => ['en' => 'Fuel, maintenance, rent on own trucks. These are operating costs — not the same as a carrier invoice.', 'bn' => 'নিজস্ব ট্রাকে জ্বালানি, রক্ষণাবেক্ষণ, ভাড়া। এটি অপারেটিং খরচ — ক্যারিয়ার ইনভয়েস নয়।'], 'path' => '/admin/fleet-expenses'],
            ['title' => ['en' => 'Add transport carriers', 'bn' => 'ট্রান্সপোর্ট ক্যারিয়ার'], 'body' => ['en' => 'Hired truck/courier vendors live under Logistics — not raw-material Suppliers. Then add a rate card if you quote by route or kg.', 'bn' => 'ভাড়া ট্রাক/কুরিয়ার Logistics-এ — কাঁচামাল Suppliers-এ নয়। রুট বা কেজি অনুযায়ী রেট কার্ড যোগ করুন।'], 'path' => '/admin/logistics/carriers'],
            ['title' => ['en' => 'Post a logistics bill', 'bn' => 'লজিস্টিক্স বিল'], 'body' => ['en' => 'Carrier invoice for hired transport. Record lines (freight, loading, demurrage), VAT, and payments. Outstanding shows on the dashboard.', 'bn' => 'ভাড়া পরিবহনের ক্যারিয়ার ইনভয়েস। লাইন (ফ্রেইট, লোডিং), VAT ও পেমেন্ট। বকেয়া ড্যাশবোর্ডে দেখায়।'], 'path' => '/admin/logistics-bills'],
            ['title' => ['en' => 'Review route cost vs sales', 'bn' => 'রুট খরচ বনাম বিক্রয়'], 'body' => ['en' => 'Reports → Logistics reports → Route cost vs sales. Compare fleet + carrier spend to sales by route.', 'bn' => 'Reports → Logistics reports → Route cost vs sales। রুট অনুযায়ী ফ্লিট + ক্যারিয়ার খরচ বিক্রয়ের সাথে তুলনা।'], 'path' => '/admin/reports/route-costs'],
        ],
        'examples' => [
            [
                'title' => ['en' => 'Own truck vs hired carrier', 'bn' => 'নিজস্ব ট্রাক বনাম ভাড়া ক্যারিয়ার'],
                'body' => [
                    'en' => 'Dhaka city: own van — record fuel as a fleet expense. Chittagong trip: hired XYZ Logistics — create a logistics bill against the carrier, not a purchase bill on a raw-material supplier.',
                    'bn' => 'ঢাকা শহর: নিজস্ব ভ্যান — জ্বালানি fleet expense। চট্টগ্রাম ট্রিপ: XYZ Logistics ভাড়া — logistics bill, কাঁচামাল সাপ্লায়ারের purchase bill নয়।',
                ],
            ],
        ],
        'tips' => [
            ['en' => 'Delivery POD proves goods arrived. Logistics records **how much the trip cost**.', 'bn' => 'Delivery POD পণ্য পৌঁছানোর প্রমাণ। Logistics **ট্রিপ কত খরচ** রেকর্ড করে।'],
            ['en' => 'Do not post the same freight twice (fleet expense + logistics bill) for one trip.', 'bn' => 'এক ট্রিপে একই ফ্রেইট দুবার পোস্ট করবেন না (fleet expense + logistics bill)।'],
        ],
    ],
    [
        'slug' => 'manufacturing',
        'order' => 5,
        'icon' => 'manufacturing',
        'title' => ['en' => 'Manufacturing', 'bn' => 'উৎপাদন'],
        'summary' => ['en' => 'BOM recipe → batch → production run → QC → stock confirm.', 'bn' => 'BOM রেসিপি → ব্যাচ → উৎপাদন রান → QC → স্টক নিশ্চিত।'],
        'flowchart' => [
            'en' => "flowchart TD\n    A[\"BOM\"] --> B[\"Batch\"]\n    B --> C[\"Production run\"]\n    C --> D[\"QC\"]\n    D --> E[\"Confirm stock\"]",
            'bn' => "flowchart TD\n    A[\"BOM\"] --> B[\"ব্যাচ\"]\n    B --> C[\"উৎপাদন রান\"]\n    C --> D[\"QC\"]\n    D --> E[\"স্টক নিশ্চিত\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create BOM', 'bn' => 'BOM তৈরি'], 'body' => ['en' => 'Finished product + material lines with qty per 1 unit.', 'bn' => 'তৈরি পণ্য + প্রতি ১ ইউনিটে কাঁচামালের পরিমাণ।'], 'path' => '/admin/boms/create'],
            ['title' => ['en' => 'Production run', 'bn' => 'উৎপাদন রান'], 'body' => ['en' => 'Select product, batch, qty, line, shift.', 'bn' => 'পণ্য, ব্যাচ, পরিমাণ, লাইন, শিফট বেছে নিন।'], 'path' => '/admin/production/create'],
            ['title' => ['en' => 'QC then stock', 'bn' => 'QC তারপর স্টক'], 'body' => ['en' => 'QC approves → Pending receipts → Confirm stock.', 'bn' => 'QC অনুমোদন → Pending receipts → Confirm stock।'], 'path' => '/admin/production/pending-receipts'],
        ],
        'examples' => [['title' => ['en' => '1 carton BOM', 'bn' => '১ কার্টন BOM'], 'body' => ['en' => '12 bottles + 12 caps + 1 carton + 6L water per SAF-500ML-CTN.', 'bn' => 'প্রতি SAF-500ML-CTN-এ ১২ বোতল + ১২ ক্যাপ + ১ কার্টন + ৬L পানি।']]],
        'tips' => [['en' => 'Without active BOM, materials are not consumed on confirm.', 'bn' => 'সক্রিয় BOM না থাকলে নিশ্চিতকরণে কাঁচামাল কাটে না।']],
    ],
    [
        'slug' => 'inventory',
        'order' => 6,
        'icon' => 'inventory',
        'title' => ['en' => 'Inventory', 'bn' => 'ইনভেন্টরি'],
        'summary' => ['en' => 'See stock, transfer between warehouses, audit counts.', 'bn' => 'স্টক দেখুন, গুদাম থেকে গুদামে স্থানান্তর, গণনা যাচাই।'],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Stock in\"] --> B[\"Warehouse\"]\n    B --> C[\"Transfer or sell\"]",
            'bn' => "flowchart LR\n    A[\"স্টক ইন\"] --> B[\"গুদাম\"]\n    B --> C[\"স্থানান্তর বা বিক্রি\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Check stock', 'bn' => 'স্টক দেখুন'], 'body' => ['en' => 'Inventory dashboard and material stock views.', 'bn' => 'ইনভেন্টরি ড্যাশবোর্ড ও ম্যাটেরিয়াল স্টক।'], 'path' => '/admin/inventory'],
            ['title' => ['en' => 'Transfer', 'bn' => 'স্থানান্তর'], 'body' => ['en' => 'Move qty from one warehouse to another.', 'bn' => 'এক গুদাম থেকে অন্যটিতে পরিমাণ সরান।'], 'path' => '/admin/stock/transfers'],
        ],
        'examples' => [],
        'tips' => [['en' => 'Low stock report helps plan POs.', 'bn' => 'লো স্টক রিপোর্ট PO পরিকল্পনায় সাহায্য করে।']],
    ],
    [
        'slug' => 'sales',
        'order' => 7,
        'icon' => 'sales',
        'title' => ['en' => 'Sales orders', 'bn' => 'বিক্রয় অর্ডার'],
        'summary' => ['en' => 'Agents order finished products; system reserves stock.', 'bn' => 'এজেন্ট তৈরি পণ্য অর্ডার দেয়; সিস্টেম স্টক রিজার্ভ করে।'],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Draft order\"] --> B[\"Confirm\"] --> C[\"Pick and deliver\"]",
            'bn' => "flowchart LR\n    A[\"ড্রাফট\"] --> B[\"নিশ্চিত\"] --> C[\"পিক ও ডেলিভার\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create order', 'bn' => 'অর্ডার তৈরি'], 'body' => ['en' => 'Agent, products, qty, delivery date.', 'bn' => 'এজেন্ট, পণ্য, পরিমাণ, ডেলিভারি তারিখ।'], 'path' => '/admin/orders/create'],
            ['title' => ['en' => 'Confirm', 'bn' => 'নিশ্চিত'], 'body' => ['en' => 'Moves order forward and reserves stock.', 'bn' => 'অর্ডার এগিয়ে যায় ও স্টক রিজার্ভ হয়।'], 'path' => '/admin/orders'],
        ],
        'examples' => [['title' => ['en' => 'Sample order', 'bn' => 'স্যাম্পল অর্ডার'], 'body' => ['en' => 'Use order type Sample for free promotional goods — not billed.', 'bn' => 'পromotional ফ্রি পণ্যের জন্য Sample টাইপ — বিল হয় না।']]],
        'tips' => [['en' => 'Check FG stock before promising delivery date.', 'bn' => 'ডেলিভারি তারিখ দেওয়ার আগে FG স্টক দেখুন।']],
    ],
    [
        'slug' => 'delivery',
        'order' => 8,
        'icon' => 'inventory',
        'title' => ['en' => 'Delivery and POD', 'bn' => 'ডেলিভারি ও POD'],
        'summary' => ['en' => 'Dispatch goods and record proof of delivery.', 'bn' => 'পণ্য পাঠান ও ডেলিভারির প্রমাণ রেকর্ড করুন।'],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Pick list\"] --> B[\"Delivery\"] --> C[\"POD\"] --> D[\"Delivered\"]",
            'bn' => "flowchart LR\n    A[\"পিক লিস্ট\"] --> B[\"ডেলিভারি\"] --> C[\"POD\"] --> D[\"ডেলিভার্ড\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Create delivery', 'bn' => 'ডেলিভারি তৈরি'], 'body' => ['en' => 'Link order, route, vehicle.', 'bn' => 'অর্ডার, রুট, যান যুক্ত করুন।'], 'path' => '/admin/deliveries'],
            ['title' => ['en' => 'POD', 'bn' => 'POD'], 'body' => ['en' => 'Record delivered qty, short, damage, receiver name.', 'bn' => 'ডেলিভার্ড পরিমাণ, কমতি, ক্ষতি, গ্রহীতার নাম লিখুন।'], 'path' => '/admin/deliveries/pod'],
        ],
        'examples' => [],
        'tips' => [['en' => 'Invoice usually needs status Delivered.', 'bn' => 'ইনভয়েস সাধারণত Delivered স্ট্যাটাস চায়।']],
    ],
    [
        'slug' => 'accounting',
        'order' => 9,
        'icon' => 'accounting',
        'title' => ['en' => 'Accounting & tax ledger', 'bn' => 'হিসাব ও কর লেজার'],
        'summary' => [
            'en' => 'Money in (agent invoices, receipts), money out (supplier bills, expenses, payroll), and automatic GL journals — with VAT, withholding, and COGS for tax advisors.',
            'bn' => 'টাকা ইন (এজেন্ট ইনভয়েস, রসিদ), টাকা আউট (সাপ্লায়ার বিল, খরচ, পে-রোল), স্বয়ংক্রিয় GL জার্নাল — VAT, উৎসে কর ও COGS সহ।',
        ],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Delivered order\"] --> B[\"Sales invoice\"]\n    B --> C[\"GL journal\"]\n    C --> D[\"Receipt\"]\n    D --> E[\"Bank\"]",
            'bn' => "flowchart LR\n    A[\"ডেলিভার্ড অর্ডার\"] --> B[\"বিক্রয় ইনভয়েস\"]\n    B --> C[\"GL জার্নাল\"]\n    C --> D[\"রসিদ\"]\n    D --> E[\"ব্যাংক\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Issue customer invoice', 'bn' => 'গ্রাহক ইনভয়েস ইস্যু'], 'body' => ['en' => 'Accounting → Customer invoices. Create from a delivered sales order. System posts Dr AR, Cr Sales Revenue, Cr VAT Payable. COGS journal fires if inventory GL is on.', 'bn' => 'Accounting → Customer invoices। ডেলিভার্ড অর্ডার থেকে তৈরি। সিস্টেম Dr AR, Cr Sales Revenue, Cr VAT Payable পোস্ট করে।'], 'path' => '/admin/finance'],
            ['title' => ['en' => 'Post customer receipt', 'bn' => 'গ্রাহক রসিদ পোস্ট'], 'body' => ['en' => 'Open invoice → add receipt (amount, method, date). Posts Dr Bank, Cr AR. Cannot exceed outstanding.', 'bn' => 'ইনভয়েস খুলে রসিদ যোগ করুন। Dr Bank, Cr AR। বকেয়ার বেশি নয়।'], 'path' => '/admin/finance'],
            ['title' => ['en' => 'Record supplier bill', 'bn' => 'সাপ্লায়ার বিল রেকর্ড'], 'body' => ['en' => 'Link to PO/GRN. Posts Dr Purchases/Inventory, Dr Input VAT, Cr Accounts Payable.', 'bn' => 'PO/GRN যুক্ত করুন। Dr Purchases/Inventory, Dr Input VAT, Cr AP।'], 'path' => '/admin/bills'],
            ['title' => ['en' => 'Pay supplier', 'bn' => 'সাপ্লায়ার পরিশোধ'], 'body' => ['en' => 'Post payment on bill or use batch payment. Dr AP, Cr Bank.', 'bn' => 'বিলে পেমেন্ট বা ব্যাচ পেমেন্ট। Dr AP, Cr Bank।'], 'path' => '/admin/bills'],
            ['title' => ['en' => 'Record expenses', 'bn' => 'খরচ রেকর্ড'], 'body' => ['en' => 'Pick expense category (maps to expense ledger), amount, and payment: bank, cash, or accrued payable. Posts Dr expense, Cr bank/payable.', 'bn' => 'Expense category (expense ledger), পরিমাণ, payment: bank/cash/payable। Dr expense, Cr bank/payable।'], 'path' => '/admin/expenses'],
            ['title' => ['en' => 'Map expense categories', 'bn' => 'Expense category ম্যাপ'], 'body' => ['en' => 'Accounting → Expense category mapping. Link Utilities, Rent, Marketing, etc. to chart of accounts leaves.', 'bn' => 'Expense category mapping। Utilities, Rent, Marketing COA leaf-এর সাথে যুক্ত করুন।'], 'path' => '/admin/expense-categories'],
            ['title' => ['en' => 'Post payroll', 'bn' => 'পে-রোল পোস্ট'], 'body' => ['en' => 'Salary distributions — not duplicate salary in Expenses. Dr Salaries & wages, Cr bank/cash/salary payable.', 'bn' => 'Salary distributions — Expenses-এ বেতন দ্বিগুণ করবেন না। Dr Salaries & wages, Cr bank/payable।'], 'path' => '/admin/salary-distributions'],
            ['title' => ['en' => 'Review journals & periods', 'bn' => 'জার্নাল ও পিরিয়ড দেখুন'], 'body' => ['en' => 'Journal entries for audit trail. Close accounting periods before month-end lock.', 'bn' => 'নিরীক্ষার জন্য জার্নাল এন্ট্রি। মাস শেষে পিরিয়ড বন্ধ করুন।'], 'path' => '/admin/journals'],
            ['title' => ['en' => 'Run tax reports', 'bn' => 'কর রিপোর্ট চালান'], 'body' => ['en' => 'VAT report (output − input), AR/AP aging, trial balance, P&L. Export Tally XML for external CA.', 'bn' => 'VAT রিপোর্ট, AR/AP aging, ট্রায়াল ব্যালেন্স, P&L। বাহ্যিক CA-র জন্য Tally XML।'], 'path' => '/admin/reports/vat'],
        ],
        'examples' => [
            [
                'title' => ['en' => 'Invoice with 15% VAT and 3% withholding', 'bn' => '১৫% VAT ও ৩% উৎসে কর সহ ইনভয়েস'],
                'body' => [
                    'en' => 'Agent order delivered: 100 cartons × ৳500 net = ৳50,000 net. VAT 15% = ৳7,500. Invoice posts AR ৳57,500. If agent withholding 3% on net = ৳1,500, cash expectation is ৳56,000; withholding sits in Withholding Tax Receivable until claimed.',
                    'bn' => '১০০ কার্টন × ৳৫০০ নেট = ৳৫০,০০০। VAT ১৫% = ৳৭,৫০০। AR ৳৫৭,৫০০। উৎসে কর ৩% = ৳১,৫০০ হলে নগদ প্রত্যাশা ৳৫৬,০০০।',
                ],
            ],
            [
                'title' => ['en' => 'Month-end VAT check', 'bn' => 'মাস শেষ VAT যাচাই'],
                'body' => [
                    'en' => 'Open VAT report for the month. Output VAT (sales invoices) minus Input VAT (supplier bills) = net payable to NBR for the period. Cross-check VAT Payable (2000) and Input VAT (1150) on trial balance.',
                    'bn' => 'মাসের VAT রিপোর্ট খুলুন। আউটপুট VAT − ইনপুট VAT = নেট প্রদেয়। ট্রায়াল ব্যালেন্সে 2000 ও 1150 যাচাই করুন।',
                ],
            ],
        ],
        'tips' => [
            ['en' => 'Open **How profit & loss is calculated** for a one-unit produce-to-sell example with numbers.', 'bn' => '**লাভ-ক্ষতি কীভাবে হয়** বিষয়ে এক ইউনিট উৎপাদন-থেকে-বিক্রয় উদাহরণ দেখুন।'],
            ['en' => 'Open **Transaction → ledger map** for a client-ready cheat sheet of every posting.', 'bn' => '**Transaction → ledger map** কোর্সে প্রতিটি পোস্টিংয়ের cheat sheet দেখুন।'],
            ['en' => 'Switch to the **Tax & ledger reference** tab for full journal entries, chart of accounts, and advisor FAQs.', 'bn' => '**কর ও লেজার রেফারেন্স** ট্যাবে সম্পূর্ণ জার্নাল, চার্ট অফ অ্যাকাউন্টস ও FAQ দেখুন।'],
            ['en' => 'Never invoice before delivery — revenue and VAT should follow POD.', 'bn' => 'ডেলিভারির আগে ইনভয়েস নয় — আয় ও VAT POD-এর পর।'],
            ['en' => 'Close the accounting period after trial balance matches AR/AP aging.', 'bn' => 'ট্রায়াল ব্যালেন্স AR/AP aging-এর সাথে মিললে পিরিয়ড বন্ধ করুন।'],
        ],
    ],
    [
        'slug' => 'profit-loss',
        'order' => 9.5,
        'icon' => 'accounting',
        'title' => [
            'en' => 'How profit & loss is calculated',
            'bn' => 'লাভ-ক্ষতি কীভাবে হয়',
        ],
        'summary' => [
            'en' => 'Follow one unit from raw materials → production → sale → invoice journals → gross profit and net profit on the P&L report. With BDT worked example.',
            'bn' => 'এক ইউনিট কাঁচামাল → উৎপাদন → বিক্রয় → ইনভয়েস জার্নাল → মোট ও নিট লাভ পর্যন্ত। BDT উদাহরণসহ।',
        ],
        'flowchart' => [
            'en' => <<<'MERMAID'
flowchart TD
    A["Buy materials GRN"] --> B["Produce 1 unit BOM"]
    B --> C["FG in stock"]
    C --> D["Deliver to agent"]
    D --> E["Sales invoice"]
    E --> F["Net sales 80"]
    E --> G["COGS 42"]
    F --> H["Gross profit 38"]
    G --> H
    H --> I["Minus commission and costs"]
    I --> J["Net profit on P&L"]
MERMAID,
            'bn' => <<<'MERMAID'
flowchart TD
    A["কাঁচামাল GRN"] --> B["BOM দিয়ে ১ ইউনিট"]
    B --> C["FG স্টকে"]
    C --> D["এজেন্টকে ডেলিভারি"]
    D --> E["বিক্রয় ইনভয়েস"]
    E --> F["নেট বিক্রয় ৮০"]
    E --> G["COGS ৪২"]
    F --> H["মোট লাভ ৩৮"]
    G --> H
    H --> I["কমিশন ও খরচ বিয়োগ"]
    I --> J["P&L নিট লাভ"]
MERMAID,
        ],
        'steps' => [
            ['title' => ['en' => 'Know your unit cost', 'bn' => 'ইউনিট খরচ জানুন'], 'body' => ['en' => 'Run production for 1 SKU. BOM consumes raw materials; finished goods land in stock at **material_unit_cost** (e.g. ৳42 per carton). Balance sheet only — no profit yet.', 'bn' => '১ SKU উৎপাদন করুন। BOM কাঁচামাল কাটে; তৈরি পণ্য **material_unit_cost**-এ স্টকে (যেমন ৳৪২/কার্টন)। ব্যালেন্স শিট — এখনো লাভ নয়।'], 'path' => '/admin/production'],
            ['title' => ['en' => 'Deliver the order', 'bn' => 'অর্ডার ডেলিভার করুন'], 'body' => ['en' => 'Confirm sales order → picking → POD. Stock leaves warehouse. Still no revenue on P&L until you invoice.', 'bn' => 'বিক্রয় অর্ডার নিশ্চিত → পিকিং → POD। স্টক বের হয়। ইনভয়েস না হলে P&L-এ আয় নয়।'], 'path' => '/admin/orders'],
            ['title' => ['en' => 'Issue the invoice', 'bn' => 'ইনভয়েস ইস্যু'], 'body' => ['en' => 'Create invoice from delivered qty. Posts **Sales Revenue** (net) + **VAT Payable** + **COGS** + clears **FG inventory**. This is when gross profit appears.', 'bn' => 'ডেলিভার্ড পরিমাণে ইনভয়েস। **Sales Revenue** (নেট) + **VAT Payable** + **COGS** + **FG** ক্লিয়ার। এখন মোট লাভ দেখা যায়।'], 'path' => '/admin/finance'],
            ['title' => ['en' => 'Read the P&L report', 'bn' => 'P&L রিপোর্ট দেখুন'], 'body' => ['en' => 'Reports → Profit & loss. Net sales − COGS = gross profit. Subtract commission, expenses, payroll = net profit.', 'bn' => 'রিপোর্ট → লাভ ও ক্ষতি। নেট বিক্রয় − COGS = মোট লাভ। কমিশন, খরচ, বেতন বিয়োগ = নিট লাভ।'], 'path' => '/admin/reports/pl'],
        ],
        'examples' => [
            [
                'title' => ['en' => '1 carton SAF-1L-BTL — full numbers', 'bn' => '১ কার্টন SAF-1L-BTL — সম্পূর্ণ হিসাব'],
                'body' => [
                    'en' => 'Cost to make: ৳42. Sell net: ৳80. VAT 15%: ৳12. Invoice AR: ৳92. COGS: ৳42. **Gross profit: ৳38.** Commission 5%: ৳4. **Contribution: ৳34** before rent, payroll, and other monthly costs.',
                    'bn' => 'তৈরি খরচ: ৳৪২। নেট বিক্রয়: ৳৮০। VAT ১৫%: ৳১২। AR: ৳৯২। COGS: ৳৪২। **মোট লাভ: ৳৩৮।** কমিশন ৫%: ৳৪। **অবদান: ৳৩৪** (ভাড়া/বেতনের আগে)।',
                ],
            ],
        ],
        'tips' => [
            ['en' => 'Use the **Ledger entries & P&L math** tab for journals, formulas, and the step-by-step checklist.', 'bn' => '**লেজার এন্ট্রি ও P&L হিসাব** ট্যাবে জার্নাল, সূত্র ও চেকলিস্ট দেখুন।'],
            ['en' => 'VAT (৳12) is owed to NBR — it is not part of your ৳80 revenue or ৳38 gross profit.', 'bn' => 'VAT (৳১২) NBR-এর — এটি ৳৮০ আয় বা ৳৩৮ মোট লাভের অংশ নয়।'],
        ],
    ],
    [
        'slug' => 'ledger-mapping',
        'order' => 9.75,
        'icon' => 'accounting',
        'title' => [
            'en' => 'Transaction → ledger map',
            'bn' => 'লেনদেন → লেজার মানচিত্র',
        ],
        'summary' => [
            'en' => 'Simple cheat sheet: every business action (invoice, bill, expense, payroll, GRN, commission) and which ledger it debits and credits. Use in client demos and CA handover.',
            'bn' => 'সহজ cheat sheet: প্রতিটি ব্যবসায়িক কাজ (ইনভয়েস, বিল, খরচ, পে-রোল, GRN, কমিশন) কোন ledger-এ Dr/Cr হয়। ক্লায়েন্ট ডেমো ও CA হ্যান্ডওভারে ব্যবহার করুন।',
        ],
        'flowchart' => [
            'en' => "flowchart TD\n    A[\"Business document saved\"] --> B{\"Posts to GL?\"}\n    B -->|Yes| C[\"Balanced journal entry\"]\n    B -->|No| D[\"Operational only\"]\n    C --> E[\"Chart of accounts Balances\"]",
            'bn' => "flowchart TD\n    A[\"ডকুমেন্ট সেভ\"] --> B{\"GL পোস্ট?\"}\n    B -->|হ্যাঁ| C[\"ভারসাম্যপূর্ণ জার্নাল\"]\n    B -->|না| D[\"অপারেশনাল মাত্র\"]\n    C --> E[\"COA Balances\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Explain the three layers', 'bn' => 'তিন স্তর ব্যাখ্যা'], 'body' => ['en' => 'Sub-ledgers (invoices, bills) → General ledger (journals) → Reports (P&L, trial balance, Tally export).', 'bn' => 'সাব-লেজার (ইনভয়েস, বিল) → GL (জার্নাল) → রিপোর্ট (P&L, trial balance, Tally)।'], 'path' => '/admin/journals'],
            ['title' => ['en' => 'Open the master map', 'bn' => 'মাস্টার মানচিত্র'], 'body' => ['en' => 'Use the **Full transaction map** tab in this course — or Chart of accounts Balances to verify totals.', 'bn' => 'এই কোর্সের **Full transaction map** ট্যাব — বা COA Balances-এ মোট যাচাই।'], 'path' => '/admin/accounts'],
            ['title' => ['en' => 'Configure expense routing', 'bn' => 'Expense routing'], 'body' => ['en' => 'Expense category mapping links Utilities, Rent, Marketing to COA leaves. User picks bank/cash/payable on each expense.', 'bn' => 'Expense category mapping Utilities, Rent, Marketing COA-তে। প্রতি expense-এ bank/cash/payable।'], 'path' => '/admin/expense-categories'],
            ['title' => ['en' => 'Trace one invoice to GL', 'bn' => 'ইনভয়েস GL-এ ট্রেস'], 'body' => ['en' => 'Find invoice → search journals by date/description → drill General ledger on Trade debtors.', 'bn' => 'ইনভয়েস → জার্নাল খুঁজুন → Trade debtors GL-এ ড্রিল।'], 'path' => '/admin/reports/general-ledger'],
        ],
        'examples' => [
            [
                'title' => ['en' => '30-second client pitch', 'bn' => '৩০ সেকেন্ড ক্লায়েন্ট পিচ'],
                'body' => [
                    'en' => '“When you save invoice, bill, or expense, the system creates a balanced journal entry to your Tally-style chart. You see it in Balances, trial balance, and Tally export.”',
                    'bn' => '“ইনভয়েস, বিল বা expense সেভ করলে Tally-style chart-এ balanced journal তৈরি হয়। Balances, trial balance ও Tally export-এ দেখেন।”',
                ],
            ],
        ],
        'tips' => [
            ['en' => 'Orders and POs do not post — invoice and GRN/bill do.', 'bn' => 'অর্ডার ও PO পোস্ট করে না — ইনভয়েস ও GRN/বিল করে।'],
            ['en' => 'Use Salary distributions for payroll, not Expenses + payroll both.', 'bn' => 'পে-রোলের জন্য Salary distributions — Expenses-এ দ্বিগুণ নয়।'],
        ],
    ],
    [
        'slug' => 'hr-payroll',
        'order' => 10.5,
        'icon' => 'employees',
        'title' => ['en' => 'HR & payroll', 'bn' => 'HR ও পে-রোল'],
        'summary' => [
            'en' => 'Employees, contracts, leaves, and allowances are HR records. Only salary distributions post to the general ledger.',
            'bn' => 'কর্মী, চুক্তি, ছুটি, ভাতা HR রেকর্ড। শুধু salary distributions GL-এ পোস্ট হয়।',
        ],
        'flowchart' => [
            'en' => "flowchart LR\n    A[\"Employee master\"] --> B[\"Contract / leave\"]\n    B --> C[\"Salary distribution\"]\n    C --> D[\"GL: Salaries & wages\"]",
            'bn' => "flowchart LR\n    A[\"Employee master\"] --> B[\"Contract / leave\"]\n    B --> C[\"Salary distribution\"]\n    C --> D[\"GL: Salaries & wages\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Add employees', 'bn' => 'কর্মী যোগ'], 'body' => ['en' => 'Control → HR → Employees. Name, department, join date. No GL impact.', 'bn' => 'Control → HR → Employees। নাম, department, join date। GL প্রভাব নেই।'], 'path' => '/admin/employees'],
            ['title' => ['en' => 'Manage contracts & leaves', 'bn' => 'চুক্তি ও ছুটি'], 'body' => ['en' => 'Contracts and leave requests track HR compliance. Operational only.', 'bn' => 'চুক্তি ও ছুটি HR compliance। অপারেশনাল মাত্র।'], 'path' => '/admin/contracts'],
            ['title' => ['en' => 'Post salary distribution', 'bn' => 'বেতন বিতরণ'], 'body' => ['en' => 'Accounting → Salary distributions. Base + bonus + allowances. Dr Salaries & wages, Cr bank/cash/salary payable.', 'bn' => 'Salary distributions। Base + bonus + allowances। Dr Salaries & wages, Cr bank/payable।'], 'path' => '/admin/salary-distributions'],
            ['title' => ['en' => 'Review payroll report', 'bn' => 'পে-রোল রিপোর্ট'], 'body' => ['en' => 'Reports → Payroll summary compares distributions to GL when journals are posted.', 'bn' => 'Payroll summary — distributions ও GL তুলনা।'], 'path' => '/admin/reports/payroll'],
        ],
        'examples' => [
            ['title' => ['en' => 'Monthly payroll', 'bn' => 'মাসিক পে-রোল'], 'body' => ['en' => '10 employees × avg ৳25,000 = ৳250,000. One distribution per employee or batch entry. Journal: Dr Salaries & wages 250,000 | Cr Bank 250,000.', 'bn' => '১০ কর্মী × ৳২৫,০০০ = ৳২,৫০,০০০। Dr Salaries & wages | Cr Bank।']],
        ],
        'tips' => [
            ['en' => 'Do not duplicate payroll in Expenses — P&L would double-count.', 'bn' => 'Expenses-এ পে-রোল দ্বিগুণ করবেন না — P&L ভুল হবে।'],
            ['en' => 'Department on employee can tag payroll journal lines for cost centre reporting.', 'bn' => 'Employee department payroll line-এ cost centre tag হতে পারে।'],
        ],
    ],
    [
        'slug' => 'reports',
        'order' => 11,
        'icon' => 'reports',
        'title' => ['en' => 'Reports', 'bn' => 'রিপোর্ট'],
        'summary' => ['en' => 'P&L, stock, aging, logistics, fleet, and export center.', 'bn' => 'P&L, স্টক, aging, লজিস্টিক্স, ফ্লিট ও এক্সপোর্ট সেন্টার।'],
        'flowchart' => [
            'en' => "flowchart TD\n    A[\"Daily posting\"] --> B[\"Reports dashboard\"]\n    B --> C[\"P and L / VAT / stock\"]\n    B --> D[\"Logistics and fleet\"]\n    B --> E[\"Export center\"]",
            'bn' => "flowchart TD\n    A[\"দৈনিক পোস্টিং\"] --> B[\"রিপোর্ট ড্যাশবোর্ড\"]\n    B --> C[\"P&L / VAT / স্টক\"]\n    B --> D[\"লজিস্টিক্স ও ফ্লিট\"]\n    B --> E[\"এক্সপোর্ট সেন্টার\"]",
        ],
        'steps' => [
            ['title' => ['en' => 'Open reports', 'bn' => 'রিপোর্ট খুলুন'], 'body' => ['en' => 'Reports dashboard lists all standard reports.', 'bn' => 'রিপোর্ট ড্যাশবোর্ডে সব স্ট্যান্ডার্ড রিপোর্ট।'], 'path' => '/admin/reports-dashboard'],
        ],
        'examples' => [],
        'tips' => [['en' => 'Run month-end pack: inventory valuation + AR aging + P&L + logistics/fleet. Use Export center for auditor CSV/PDF.', 'bn' => 'মাস শেষে: inventory valuation + AR aging + P&L + লজিস্টিক্স/ফ্লিট। অডিটর CSV/PDF-এর জন্য Export center।']],
    ],
];
