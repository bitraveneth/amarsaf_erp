<?php

/**
 * Troubleshooting cases for the Learning Hub.
 * Each case tells the user when it can happen and why it happened.
 */
return [
    'audience' => [
        'en' => 'Anyone who prints documents or checks profit and loss',
        'bn' => 'যারা ডকুমেন্ট ছাপেন বা লাভ-ক্ষতি দেখেন',
    ],
    'glossary' => [
        [
            'term' => ['en' => 'Challan copy', 'bn' => 'চালান কপি'],
            'def' => [
                'en' => 'One labeled page of a delivery challan. Saf ERP prints three: customer, office, and transport.',
                'bn' => 'ডেলিভারি চালানের একটি লেবেল করা পৃষ্ঠা। Saf ERP তিনটি ছাপে: গ্রাহক, অফিস ও পরিবহন।',
            ],
        ],
        [
            'term' => ['en' => 'Posted journal', 'bn' => 'পোস্ট করা জার্নাল'],
            'def' => [
                'en' => 'A balanced voucher the profit and loss statement is allowed to read. A saved expense without this voucher stays off the statement.',
                'bn' => 'ভারসাম্যপূর্ণ ভাউচার যা লাভ-ক্ষতি পড়তে পারে। এই ভাউচার ছাড়া সেভ করা খরচ বিবৃতিতে আসে না।',
            ],
        ],
    ],
    'troubles' => [
        [
            'id' => 'expense-not-on-pl',
            'title' => [
                'en' => 'Expense is saved, but profit and loss does not show it',
                'bn' => 'খরচ সেভ আছে, কিন্তু লাভ-ক্ষতিতে নেই',
            ],
            'when' => [
                'en' => 'The banner at the top of the ERP says some expenses are not in the P&L ledger. The same alert can sit in the bell. The income statement can also show a count of expenses that never reached the ledger.',
                'bn' => 'ERP-র উপরে ব্যানার বলে কিছু খরচ P&L লেজারে নেই। একই সতর্কতা ঘণ্টাতেও থাকতে পারে। আয় বিবৃতিতেও এমন খরচের সংখ্যা দেখাতে পারে যা লেজারে পৌঁছায়নি।',
            ],
            'why' => [
                'en' => "Profit and loss reads **posted journals** only. An expense can exist in the expense list without a posted voucher.\n\nThat happens when the row was loaded as demo data, or saved before the form started posting automatically. It also happens when someone **reversed** the voucher, when a **draft** voucher was never posted, when the expense date sits in a **closed month**, or when the expense account or the bank/cash account is **missing**.\n\nA new expense now posts in the same save. If posting fails, that save does not leave a half-saved expense behind.",
                'bn' => "লাভ-ক্ষতি শুধু **পোস্ট করা জার্নাল** পড়ে। খরচের তালিকায় খরচ থাকতে পারে, কিন্তু পোস্ট করা ভাউচার নাও থাকতে পারে।\n\nএমন হয় যখন সারিটি ডেমো ডেটা, অথবা ফর্ম স্বয়ংক্রিয় পোস্ট শুরুর আগে সেভ হয়েছে। এমনও হয় যখন কেউ ভাউচার **রিভার্স** করে, **ড্রাফট** কখনো পোস্ট হয়নি, খরচের তারিখ **বন্ধ মাসে**, অথবা খরচের অ্যাকাউন্ট বা ব্যাংক/নগদ অ্যাকাউন্ট **নেই**।\n\nনতুন খরচ এখন সেই সেভেই পোস্ট হয়। পোস্ট ব্যর্থ হলে অর্ধেক সেভ করা খরচ থেকে যায় না।",
            ],
            'fix' => [
                'en' => 'Open **Expenses** from the ledger notice, then use **Sync missing expenses**. If the month is closed, reopen that month first. If an account is missing, create it, then sync again.',
                'bn' => 'ব্যানার পড়ুন। সেখানে কারণ লেখা থাকে। মাস খোলা থাকলে এবং দুটি অ্যাকাউন্ট থাকলে **Sync missing expenses** ব্যবহার করুন। মাস বন্ধ থাকলে আগে সেই মাস খুলুন। অ্যাকাউন্ট না থাকলে তৈরি করে আবার সিঙ্ক করুন।',
            ],
            'path' => '/admin/reports/pl',
        ],
        [
            'id' => 'sync-did-not-clear',
            'title' => [
                'en' => 'Sync did not clear the expense banner',
                'bn' => 'সিঙ্ক করলেও খরচের ব্যানার যায়নি',
            ],
            'when' => [
                'en' => 'You pressed Sync missing expenses and the banner is still there, or the message says nothing was posted, or only some rows posted.',
                'bn' => 'আপনি Sync missing expenses চেপেছেন, তবু ব্যানার আছে, অথবা বার্তা বলে কিছুই পোস্ট হয়নি, অথবা কিছু সারিই পোস্ট হয়েছে।',
            ],
            'why' => [
                'en' => 'Sync writes a voucher only for an expense that has **no posted journal**, whose date is in an **open month**, and whose expense account and payment account both exist. A closed month is skipped. A missing account stops that row and the message includes the reason. A reversed voucher can be posted again only while the month is open. A draft is still not a posted voucher, so profit and loss keeps ignoring it until it is posted.',
                'bn' => 'সিঙ্ক তখনই ভাউচার লেখে যখন খরচের **পোস্ট করা জার্নাল নেই**, তারিখ **খোলা মাসে**, এবং খরচের অ্যাকাউন্ট ও পেমেন্ট অ্যাকাউন্ট দুটোই আছে। বন্ধ মাস বাদ যায়। অ্যাকাউন্ট না থাকলে সেই সারি থেমে যায় এবং বার্তায় কারণ থাকে। রিভার্স করা ভাউচার আবার পোস্ট হয় শুধু মাস খোলা থাকলে। ড্রাফট এখনো পোস্ট করা ভাউচার নয়, তাই পোস্ট না হওয়া পর্যন্ত লাভ-ক্ষতি সেটি ধরে না।',
            ],
            'fix' => [
                'en' => 'Read the message after sync. Reopen the closed month, or add the missing account, then sync again.',
                'bn' => 'সিঙ্কের পরের বার্তা পড়ুন। বন্ধ মাস খুলুন, অথবা বাদ পড়া অ্যাকাউন্ট যোগ করে আবার সিঙ্ক করুন।',
            ],
            'path' => '/admin/expenses',
        ],
        [
            'id' => 'challan-three-copies',
            'title' => [
                'en' => 'Delivery challan prints three pages',
                'bn' => 'ডেলিভারি চালান তিন পৃষ্ঠা ছাপে',
            ],
            'when' => [
                'en' => 'You print or download a delivery challan (chalan copy) and the file has three full pages.',
                'bn' => 'আপনি ডেলিভারি চালান (চালান কপি) প্রিন্ট বা ডাউনলোড করলে ফাইলে তিনটি পূর্ণ পৃষ্ঠা থাকে।',
            ],
            'why' => [
                'en' => 'A delivery challan is issued as three copies of the same goods list. **Customer copy** stays with the buyer, **Office copy** stays with accounts, and **Transport copy** stays with the driver. The PDF repeats the full sheet once for each copy and labels the page. It is a goods note, not a tax invoice, so it has no VAT total.',
                'bn' => 'ডেলিভারি চালান একই পণ্যতালিকার তিন কপি। **Customer copy** ক্রেতার কাছে, **Office copy** হিসাবের কাছে, **Transport copy** চালকের কাছে থাকে। PDF প্রতিটি কপির জন্য পুরো শিট আবার ছাপে এবং পৃষ্ঠায় নাম দেয়। এটি পণ্যের নোট, ট্যাক্স ইনভয়েস নয়, তাই VAT মোট নেই।',
            ],
            'fix' => [
                'en' => 'Open the delivery and print the challan. Give the customer copy to the receiver, keep the office copy, and give the transport copy to the driver.',
                'bn' => 'ডেলিভারি খুলে চালান প্রিন্ট করুন। গ্রাহক কপি গ্রহণকারীকে দিন, অফিস কপি রাখুন, পরিবহন কপি চালককে দিন।',
            ],
            'path' => '/admin/deliveries',
        ],
        [
            'id' => 'invoice-vs-challan',
            'title' => [
                'en' => 'Invoice and challan do not show the same numbers',
                'bn' => 'ইনভয়েস ও চালানের সংখ্যা মিলছে না',
            ],
            'when' => [
                'en' => 'The invoice quantity or amount differs from the challan, or you have one document and not the other.',
                'bn' => 'ইনভয়েসের পরিমাণ বা টাকা চালান থেকে আলাদা, অথবা একটি ডকুমেন্ট আছে অন্যটি নেই।',
            ],
            'why' => [
                'en' => 'The challan records what left the warehouse. The invoice is the bill you charge. They are different documents on purpose. Goods can leave before anyone creates the invoice. Short or damaged quantities change what was received, while the invoice still bills the quantity you decide to charge. A sales order by itself is not a tax invoice.',
                'bn' => 'চালান বলে কী গুদাম থেকে বেরিয়েছে। ইনভয়েস হলো যে বিল আপনি কাটেন। ইচ্ছা করেই এরা আলাদা ডকুমেন্ট। ইনভয়েস তৈরির আগেও পণ্য বের হতে পারে। ঘাটতি বা ক্ষতিগ্রস্ত পরিমাণ বদলায় কী পাওয়া গেছে, আর ইনভয়েস সেই পরিমাণই বিল করে যা আপনি চার্জ করতে চান। বিক্রয় অর্ডার নিজে ট্যাক্স ইনভয়েস নয়।',
            ],
            'fix' => [
                'en' => 'Open the delivery for the challan and the customer invoice for the bill. Match the lines. Create the invoice from the order when you are ready to charge.',
                'bn' => 'চালানের জন্য ডেলিভারি এবং বিলের জন্য গ্রাহক ইনভয়েস খুলুন। লাইন মিলান। চার্জ করার সময় অর্ডার থেকে ইনভয়েস তৈরি করুন।',
            ],
            'path' => '/admin/finance',
        ],
        [
            'id' => 'blank-qty-received',
            'title' => [
                'en' => 'Challan “Qty received” is a blank line',
                'bn' => 'চালানে “Qty received” ফাঁকা',
            ],
            'when' => [
                'en' => 'The Qty received column prints underscores instead of a number.',
                'bn' => 'Qty received কলামে সংখ্যার বদলে দাগ ছাপে।',
            ],
            'why' => [
                'en' => 'That line’s delivered quantity is still zero. The blank is intentional, so the receiver can write the count they actually got. After proof of delivery stores a delivered quantity above zero, the next print shows that number. Qty sent still shows what the warehouse dispatched.',
                'bn' => 'সেই লাইনের ডেলিভার্ড পরিমাণ এখনো শূন্য। ফাঁকা ইচ্ছা করে, যাতে গ্রহণকারী আসলে যা পেয়েছেন তা লিখতে পারেন। প্রুফ অফ ডেলিভারিতে শূন্যের বেশি ডেলিভার্ড পরিমাণ সেভ হলে পরের প্রিন্টে সংখ্যা আসে। Qty sent তখনও দেখায় গুদাম কী পাঠিয়েছে।',
            ],
            'fix' => [
                'en' => 'Record the proof of delivery with the received quantity, then print the challan again.',
                'bn' => 'প্রাপ্ত পরিমাণসহ প্রুফ অফ ডেলিভারি লিখে চালান আবার প্রিন্ট করুন।',
            ],
            'path' => '/admin/deliveries/pod',
        ],
        [
            'id' => 'empty-pdf',
            'title' => [
                'en' => 'A PDF opens with a header and no lines',
                'bn' => 'PDF-এ শিরোনাম আছে, লাইন নেই',
            ],
            'when' => [
                'en' => 'An invoice, purchase order, GRN, production order, packing slip, or logistics bill prints the company header and an empty table.',
                'bn' => 'ইনভয়েস, ক্রয় অর্ডার, GRN, উৎপাদন অর্ডার, প্যাকিং স্লিপ বা লজিস্টিক্স বিলে কোম্পানির শিরোনাম আছে, টেবিল ফাঁকা।',
            ],
            'why' => [
                'en' => 'The document was saved without line items, or the lines have no product name. A challan can still show rows from the sales order when the delivery itself has no delivery lines. A GRN, invoice, or logistics bill prints only the lines stored on that document.',
                'bn' => 'ডকুমেন্ট লাইন ছাড়া সেভ হয়েছে, অথবা লাইনে পণ্যের নাম নেই। ডেলিভারিতে ডেলিভারি লাইন না থাকলে চালান বিক্রয় অর্ডারের সারি দেখাতে পারে। GRN, ইনভয়েস বা লজিস্টিক্স বিল শুধু সেই ডকুমেন্টে থাকা লাইন ছাপে।',
            ],
            'fix' => [
                'en' => 'Open the source document and add at least one line with a product or description. Set the product size and unit if the particulars look bare. Then print again.',
                'bn' => 'উৎস ডকুমেন্ট খুলে অন্তত একটি লাইনে পণ্য বা বিবরণ দিন। বিবরণ ফাঁকা লাগলে পণ্যের সাইজ ও একক দিন। তারপর আবার প্রিন্ট করুন।',
            ],
            'path' => '/admin/finance',
        ],
        [
            'id' => 'po-columns',
            'title' => [
                'en' => 'Purchase order PDF hides discount or VAT',
                'bn' => 'ক্রয় অর্ডার PDF-এ ছাড় বা VAT নেই',
            ],
            'when' => [
                'en' => 'The purchase order print shows quantity and rate, but no discount or VAT column.',
                'bn' => 'ক্রয় অর্ডার প্রিন্টে পরিমাণ ও দর আছে, কিন্তু ছাড় বা VAT কলাম নেই।',
            ],
            'why' => [
                'en' => 'Those columns print only when at least one line has a discount percent or a VAT rate above zero. If every line is zero, the sheet leaves the columns out so the page stays short. The PO total still includes any amounts that were calculated on the lines.',
                'bn' => 'কলাম তখনই ছাপে যখন অন্তত একটি লাইনে ছাড়ের শতাংশ বা শূন্যের বেশি VAT হার থাকে। সব লাইন শূন্য হলে পৃষ্ঠা ছোট রাখতে কলাম বাদ যায়। লাইনে হিসাব হওয়া টাকা তবু PO মোটে থাকে।',
            ],
            'fix' => [
                'en' => 'Set a discount percent or VAT rate on a line and save. Print again and the column appears.',
                'bn' => 'একটি লাইনে ছাড়ের শতাংশ বা VAT হার দিয়ে সেভ করুন। আবার প্রিন্ট করলে কলাম আসে।',
            ],
            'path' => '/admin/purchase-orders',
        ],
        [
            'id' => 'report-pdf-copies',
            'title' => [
                'en' => 'Journal or balance sheet does not print three copies',
                'bn' => 'জার্নাল বা ব্যালেন্স শিট তিন কপি ছাপে না',
            ],
            'when' => [
                'en' => 'A report, journal voucher, or table export uses the same header and table style as the invoice, but it is a single page.',
                'bn' => 'রিপোর্ট, জার্নাল ভাউচার বা টেবিল এক্সপোর্ট ইনভয়েসের মতো শিরোনাম ও টেবিল ব্যবহার করে, কিন্তু এক পৃষ্ঠা।',
            ],
            'why' => [
                'en' => 'Customer, office, and transport copies belong to the **delivery challan** only. The customer invoice has its own purple sheet and prints once. Its number looks like SFIN2026101801. A challan number looks like SGC20261018. Purchase orders, GRNs, production orders, packing slips, proof of delivery, logistics bills, journals, and statements share one sheet and print once.',
                'bn' => 'গ্রাহক, অফিস ও পরিবহন কপি শুধু **ডেলিভারি চালানের**। গ্রাহক ইনভয়েসের নিজস্ব বেগুনি শিট আছে এবং সেটি একবার ছাপে। ইনভয়েস নম্বর দেখতে SFIN2026101801-এর মতো। চালান নম্বর দেখতে SGC20261018-এর মতো। ক্রয় অর্ডার, GRN, উৎপাদন অর্ডার, প্যাকিং স্লিপ, প্রুফ অফ ডেলিভারি, লজিস্টিক্স বিল, জার্নাল ও বিবৃতি একই শিট ব্যবহার করে এবং একবার ছাপে।',
            ],
            'fix' => [
                'en' => 'Print the delivery challan when you need the three copies. Use the journal or report PDF when you need the accounting statement.',
                'bn' => 'তিন কপি লাগলে ডেলিভারি চালান প্রিন্ট করুন। হিসাবের বিবৃতি লাগলে জার্নাল বা রিপোর্ট PDF ব্যবহার করুন।',
            ],
            'path' => '/admin/reports/balance-sheet',
        ],
        [
            'id' => 'copy-label-caps',
            'title' => [
                'en' => 'The PDF says CUSTOMER COPY in capitals',
                'bn' => 'PDF-এ CUSTOMER COPY বড় হাতের অক্ষরে',
            ],
            'when' => [
                'en' => 'The copy name on the downloaded PDF is in capitals, while the screen preview looks like “Customer copy”.',
                'bn' => 'ডাউনলোড করা PDF-এ কপির নাম বড় হাতের অক্ষরে, স্ক্রিন প্রিভিউতে “Customer copy”।',
            ],
            'why' => [
                'en' => 'The PDF style uppercases that pill so the copy name stays readable on paper. Customer, Office, and Transport are still three different copies. The words are the same. Only the printed case changes.',
                'bn' => 'PDF স্টাইল সেই পিল বড় হাতের করে যাতে কাগজে কপির নাম পড়া যায়। Customer, Office ও Transport তবু তিনটি আলাদা কপি। শব্দ একই। শুধু ছাপার ছাঁদ বদলায়।',
            ],
            'fix' => [
                'en' => 'No change is required. If a page is missing, you are not printing the delivery challan.',
                'bn' => 'কিছু বদলাতে হয় না। কোনো পৃষ্ঠা না থাকলে আপনি ডেলিভারি চালান প্রিন্ট করছেন না।',
            ],
            'path' => '/admin/deliveries',
        ],
        [
            'id' => 'cannot-open-pdf',
            'title' => [
                'en' => 'Print or PDF says you cannot open it',
                'bn' => 'প্রিন্ট বা PDF বলে আপনি খুলতে পারবেন না',
            ],
            'when' => [
                'en' => 'The PDF link says forbidden, or the document is not found.',
                'bn' => 'PDF লিংক forbidden বলে, অথবা ডকুমেন্ট পাওয়া যায় না।',
            ],
            'why' => [
                'en' => 'Each print checks a permission. Invoices need accounting access. Sales orders and picking lists need sales access. Purchase orders need supplier access. GRNs need inventory access. Production orders need manufacturing access. Challans, packing slips, proof of delivery, and logistics bills need warehouse access. A super admin can open all of them. A link with an id that was never saved also returns not found.',
                'bn' => 'প্রতিটি প্রিন্ট অনুমতি দেখে। ইনভয়েসে হিসাবের অনুমতি লাগে। বিক্রয় অর্ডার ও পিকিং লিস্টে বিক্রয়ের অনুমতি। ক্রয় অর্ডারে সাপ্লায়ার অনুমতি। GRN-এ ইনভেন্টরি। উৎপাদন অর্ডারে ম্যানুফ্যাকচারিং। চালান, প্যাকিং স্লিপ, প্রুফ অফ ডেলিভারি ও লজিস্টিক্স বিলে গুদামের অনুমতি। সুপার অ্যাডমিন সব খুলতে পারেন। যে আইডি সেভ হয়নি এমন লিংকও not found দেয়।',
            ],
            'fix' => [
                'en' => 'Open the document from its list so the link has a real id. If the list itself is hidden, ask an admin to grant that module.',
                'bn' => 'তালিকা থেকে ডকুমেন্ট খুলুন, যাতে লিংকে আসল আইডি থাকে। তালিকাই লুকানো থাকলে অ্যাডমিনকে সেই মডিউলের অনুমতি দিতে বলুন।',
            ],
            'path' => '/admin/finance',
        ],
    ],
];
