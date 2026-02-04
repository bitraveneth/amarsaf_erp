@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>হেল্প &amp; সিস্টেম গাইড</h1>
                <p>এই পেইজটা আসলে আপনার <strong>SAF ERP ম্যানুয়াল</strong> – উপরে মেনু অনুযায়ী হেল্প, নিচে পুরো সিস্টেমের ফ্লো।</p>
            </div>
        </header>

        {{-- মেনু ভিত্তিক দ্রুত নেভিগেশন --}}
        <nav class="help-toc">
            <p class="metric-label">🔎 মেনু অনুযায়ী হেল্প</p>
            <ul>
                <li><a href="#help-control">1. Control (Masters &amp; Settings)</a></li>
                <li><a href="#help-manufacturing">2. Manufacturing</a></li>
                <li><a href="#help-inventory">3. Inventory (Core operations)</a></li>
                <li><a href="#help-sales">4. Sales</a></li>
                <li><a href="#help-accounting">5. Accounting</a></li>
                <li><a href="#help-employees">6. Employees / HR</a></li>
                <li><a href="#help-crm">7. CRM &amp; Marketing</a></li>
            </ul>
        </nav>

        <p class="panel-note" style="margin-top:1.5rem;">
            <strong>এক নজরে পুরো ফ্লো (কীভাবে সিস্টেমটা চলে?):</strong>
            <br>১️⃣ আগে সেট‑আপ করুন – Products, Packaging types, Tax &amp; VAT classes, Warehouses, Agents, Employees।
            <br>২️⃣ প্রতিটি ফিনিশড পণ্যের জন্য BOM (Bill of Materials) বানান – ১ cartoon বানাতে কত bottle, cap, label, carton লাগবে।
            <br>৩️⃣ Production orders থেকে নির্দিষ্ট Batch / Lot ধরে production চালান, QC approve করলে Factory stock ↑ হয়।
            <br>৪️⃣ Transfers দিয়ে Factory → Central depot / Warehouse এ stock পাঠান; batch/lot ঠিক থাকে বলে FIFO/FEFO follow করা যায়।
            <br>৫️⃣ Agents থেকে Sales orders নিন; order <em>Confirmed</em> হলে প্রয়োজনীয় qty reserved হয়।
            <br>৬️⃣ Picking lists, Packing slips ও Deliveries ব্যবহার করে গাড়ি/route অনুযায়ী মাল বের করুন; Delivery status শেষ পর্যন্ত <em>Delivered</em> করুন।
            <br>৭️⃣ Delivered order থেকে Invoice তৈরি হলে Accounts Receivable (AR) ↑ এবং Sales Revenue ↑ হয়।
            <br>৮️⃣ গ্রাহক টাকা দিলে Receipt এ নথিভুক্ত করুন – Bank ↑, AR ↓; Bank reconciliation থেকে statement অনুযায়ী মিলিয়ে নিন।
            <br>৯️⃣ Employees, Contracts, Allowances, Expenses ইত্যাদি আপডেট করলে Payroll ও Profit &amp; Loss রিপোর্ট সঠিক থাকে।
            <br>🔟 শেষে Accounting রিপোর্ট – Profit &amp; Loss, VAT report, Balance sheet, Cashflow, Agent performance ইত্যাদি দেখে
            ব্যবসার সিদ্ধান্ত নিন।
        </p>

        {{-- নিচে মেনু ভিত্তিক ডিটেইল গাইড (expand/collapse সহ) --}}

        <section id="help-control" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>1. Control (Masters &amp; Settings)</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Control help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <p class="panel-note">
                এই ব্লকটা মূলত সাইডবারের <strong>Control</strong> গ্রুপ – Products, Agents, Warehouses, Employees
                এবং System settings অংশের জন্য।
            </p>
            <ul class="help-steps">
                <li>
                    <strong>Tax &amp; VAT classes</strong> (Control → Products → Tax &amp; VAT classes) – এখানে Bangladesh এর VAT rate গুলো দিন
                    (যেমন ১৫%), প্রয়োজনে HSN / local কোড লিখুন। পরে প্রোডাক্টে এই ক্লাস সিলেক্ট করলে ইনভয়েসে VAT অটো ক্যালকুলেট হবে।
                </li>
                <li>
                    <strong>Packaging types</strong> (Control → Products → Packaging types) – Bottle 500ml, Bottle 1L, Crate 12x1L ইত্যাদি
                    ইউনিট সেট করুন। এখানেই basic unit/description থাকায় Picking ও Packing slip‑এ “Pack” কলামে সুন্দরভাবে দেখায়।
                </li>
                <li>
                    <strong>Products</strong> (Control → Products → Products) – প্রতিটি SKU এর জন্য:
                    <ul>
                        <li>SKU, নাম, Size, Volume (ml)</li>
                        <li>Packaging type, Tax class, Base price</li>
                        <li>Mineral source, pH, TDS, Certifications</li>
                        <li>Barcode / QR code (যদি ইমেজ দিয়ে আপলোড করেন)</li>
                    </ul>
                    এগুলো ঠিকভাবে থাকলে পরের সব রিপোর্ট (Sales, Production, Inventory, Finance) consistent হয়।
                </li>
                <li>
                    <strong>Warehouses &amp; Locations</strong> (Control → Warehouses) –
                    প্রথমে Central depot / Factory ইত্যাদি warehouse তৈরি করুন, পরে প্রয়োজন হলে
                    <em>Location code</em> দিয়ে R1-S2-B3 টাইপ code ব্যবহার করুন যাতে Picking list‑এ কোন rack থেকে মাল তুলতে হবে,
                    সেটা দেখা যায়।
                </li>
            </ul>
            </div>
        </section>

        {{-- 2. Manufacturing --}}
        <section id="help-manufacturing" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>2. Manufacturing – BOM, Production orders &amp; Batches</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Manufacturing help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <ul class="help-steps">
                <li>
                    <strong>BOMs (Bill of Materials)</strong> (Manufacturing → BOMs) – একটা ফিনিশড পণ্য বানাতে
                    প্রতি ইউনিটে কতগুলো component লাগে সেটা এখানে সেট করবেন।
                    যেমন: ১ cartoon 1L water = ১২ bottle + ১২ cap + ১২ label + ১ carton box।
                    পরে Production order করলে system এই BOM ধরে raw‑material consumption হিসাব করতে পারে।
                </li>
                <li>
                    <strong>Batches &amp; lots</strong> (Manufacturing → Batches &amp; lots) – প্রতিটি production lot এর
                    batch code (যেমন 1L‑250105‑A), Production date, Expiry date, QC status, Notes রেকর্ড করুন।
                    এখানকার batch/lot নম্বরেই পরের সব stock, delivery ও invoice track হয়।
                </li>
                <li>
                    <strong>Production orders</strong> (Manufacturing → Production orders) – production run এর সময়:
                    <ol>
                        <li>Finished product, Batch / Lot, Warehouse, Line, Shift নির্বাচন করুন</li>
                        <li>Quantity এবং QC status দিন (প্রথমে সাধারণত <em>pending</em>)</li>
                        <li>QC approved + warehouse সেট থাকলে system অটো <em>Stock entries</em> তৈরি করে, status = available</li>
                    </ol>
                    ফলে sales ও inventory একই batch data ব্যবহার করে – FEFO/FIFO handling সহজ হয়।
                </li>
            </ul>
            </div>
        </section>

        {{-- 3. Inventory --}}
        <section id="help-inventory" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>3. Inventory – Transfers, Deliveries, Packing</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Inventory help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <ul class="help-steps">
                <li>
                    <strong>Inventory dashboard</strong> (Inventory → Inventory dashboard) – warehouse + product + batch অনুযায়ী
                    available / reserved stockের সারাংশ; কোন batch শীঘ্রই expire হবে সেটাও এখান থেকে ধরা যায়।
                </li>
                <li>
                    <strong>Transfers</strong> (Inventory → Transfers) – এক warehouse থেকে অন্য warehouse এ stock পাঠাতে ব্যবহার করুন;
                    system দুই দিকেই movement log রাখে; পরে audit trail হিসেবে ব্যবহার করতে পারবেন।
                </li>
                <li>
                    <strong>Deliveries &amp; POD</strong> – Sales order থেকে delivery তৈরি করুন:
                    Route, Vehicle, Driver, Status (Scheduled → In transit → Delivered)।
                    এখানে POD photo আপলোড করলে future reference থাকে।
                </li>
                <li>
                    <strong>Packing slips</strong> – Inventory → Packing slips থেকে নির্দিষ্ট date / vehicle অনুযায়ী
                    সব delivery line দেখে one click এ packing slip প্রিন্ট করতে পারবেন।
                    এটাই warehouse / loader টিমের কাজের মূল কাগজ।
                </li>
                <li>
                    <strong>Fleet, Fleet schedule &amp; Vehicle load</strong> – গাড়ি, capacity, driver, daily schedule এবং
                    estimated crate load (গাড়ি কতটা full) – সব এখানে দেখা যায়, যাতে under‑utilized বা overloaded কোন vehicle না থাকে।
                </li>
                <li>
                    <strong>Inventory adjustments</strong> – Expired / Wasted / Supplier return / Other কারণ দেখিয়ে
                    write‑off করলে <em>Stock movements</em> টেবিলে কারণসহ রেকর্ড হয়; ফলে পরে বোঝা যায়
                    কতটা ক্ষতি expiry, কতটা damage, কতটা return থেকে এসেছে।
                </li>
            </ul>
            </div>
        </section>

        {{-- 4. Sales --}}
        <section id="help-sales" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>4. Sales – Agents, Orders, Deliveries</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Sales help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <ul class="help-steps">
                <li>
                    <strong>Agents</strong> (Control → Agents → Agents) –
                    ডিলার / এজেন্ট / হোলসেলার প্রোফাইল এখানে থাকবে:
                    নাম, Area, Zone, Location code, Special code, Mobile, Email, Credit limit, Bank details, KYC documents ইত্যাদি।
                </li>
                <li>
                    <strong>Price lists</strong> – Control → Products → Price lists – এখানে base price ও কতজন এজেন্টের override আছে সেটা দেখবেন।
                    এছাড়া এজেন্ট প্রোফাইল থেকে “Pricing &amp; commissions” খুলে প্রতি SKU এর special price সেট করলে,
                    অর্ডার এন্ট্রি করার সময় সিস্টেম স্বয়ংক্রিয়ভাবে সেই rate নিয়ে আসে (না থাকলে Products এর base price নেয়)।
                </li>
                <li>
                    <strong>Commission rules</strong> – একই স্ক্রিনে কমিশন rule (যেমন সব regular order এ ২%) সেট করলে,
                    Order save হওয়ার সময় প্রতিটি লাইনে commission amount অটো ক্যালকুলেট হয় এবং
                    <em>Agent commission settlements</em> রিপোর্টে মাসিক summary দেখা যায়।
                </li>
                <li>
                    <strong>Sales orders</strong> (Sales → Sales orders) – নতুন অর্ডার নেবার ফ্লো:
                    <ol>
                        <li>Agent নির্বাচন করুন (Customers / Agents থেকে)</li>
                        <li>Order type (Regular / Bulk / Sample / Return) ও Delivery date দিন</li>
                        <li>পছন্দের SKU যোগ করুন – Qty, Unit price (অটো আসবে, চাইলে override)</li>
                        <li>Save করলে order status = <em>Confirmed</em>; একই সময়ে stock থেকে সেই qty <em>reserved</em> হয়ে যায়</li>
                    </ol>
                </li>
                <li>
                    <strong>Customer gifts &amp; Marketing campaigns</strong> – কোন এজেন্টকে কী ধরনের fridge/banner/gift দিলেন
                    এবং কোন campaign code এর সাথে link – এগুলো পরের রিপোর্টে “Customer acquisition cost” ও marketing ROI বোঝার কাজে লাগবে।
                </li>
            </ul>
            </div>
        </section>

        <section id="help-employees" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>6. Employees – HR, Contracts, Allowances, Locations</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Employees help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <ul class="help-steps">
                <li>
                    <strong>Employees</strong> (Employees → Employees) – Name, Department, Job position, Work zone,
                    work email/phone/mobile, Tag, Photo, CV – সব এক জায়গায় থাকবে।
                </li>
                <li>
                    <strong>Contracts</strong> – Employee প্রতি active contract রাখুন:
                    Reference, Start/End date, Working schedule, Salary, TA/DA, Bonus, Status।
                    Payroll summary এই তথ্য ধরে মাসিক base salary হিসাব করে।
                </li>
                <li>
                    <strong>Allowances</strong> – TA/DA/BONUS ক্লেইম গুলো employee নিজে mobile app থেকে
                    বা backoffice থেকে add করতে পারে; Amount, Reference, Attachment (slip) সহ থাকে।
                </li>
                <li>
                    <strong>Leaves</strong> – Leave apply / approve; Leave summary API দিয়ে mobile app‑এও
                    “this year total vs used vs remaining” দেখানো হয়।
                </li>
                <li>
                    <strong>Equipment</strong> – ট্যাব, ফোন, device ইস্যু date, identifier (IMEI), status (assigned / returned / lost)
                    সব log হয়; পরে lost device / replacement হিসাব সহজ হয়।
                </li>
                <li>
                    <strong>Locations</strong> – Field sales এর GPS / manual location log:
                    Logged at, Lat/Lng, Label, Source (manual/imported/gps), Notes – এগুলো থেকে কোন day‑তে
                    কে কোথায় ছিল সেটা record থাকে।
                </li>
                <li>
                    <strong>Badges</strong> – “Star Performer”, “On‑time Collection” টাইপ recognition badge employee‑কে assign করতে পারবেন;
                    এটি pure HR recognition, হিসাবের উপর প্রভাব ফেলে না কিন্তু culture‑এ positive impact দেয়।
                </li>
            </ul>
            </div>
        </section>

        <section id="help-accounting" class="help-section collapsed" data-collapsible>
            <div class="help-section-header">
                <h2>5. Accounting – Invoices, Receipts, Expenses, Reports</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle Accounting help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <p class="panel-note">
                এখানে বাংলায় সহজ করে <strong>AR (Accounts Receivable)</strong>, Profit &amp; Loss, Balance sheet–এর relation ব্যাখ্যা করা হল।
            </p>
            <ul class="help-steps">
                <li>
                    <strong>Customer invoices</strong> (Accounting → Customer invoices) –
                    শুধুমাত্র <em>Delivered</em> order থেকে invoice বানান।
                    System এখানে Net total + VAT = Gross amount হিসাব করে।
                    Invoice তৈরি হলে হিসাবটা হয়:
                    <em>Accounts Receivable (AR) ↑, Sales Revenue ↑, VAT Payable ↑</em>।
                </li>
                <li>
                    <strong>Receipts</strong> – Invoice ভিউয়ের নিচে যে “Receipts” অংশ দেখেন সেটা মোটামুটি
                    “গ্রাহকের টাকা আসল কি না” log করার জায়গা।
                    উদাহরণ: Invoice = 69,000; গ্রাহক Bank transfer এ 66,240 দিলেন →
                    <em>Bank ↑ 66,240, Accounts Receivable ↓ 66,240</em>।
                    বাকি টাকা থাকলে invoice status “Issued”–ই থাকে, পুরোটা পেলে “Paid” হয়।
                </li>
                <li>
                    <strong>Credit notes</strong> – কোন কারণে গ্রাহককে discount / return দিতে হলে
                    Invoice থেকে credit note issue করুন।
                    হিসাব: <em>Sales Returns ↑, AR ↓</em> (অথবা future invoice adjust) – Profit &amp; Loss এ
                    Net sales = Sales Revenue – Sales Returns হিসেবে দেখা যায়।
                </li>
                <li>
                    <strong>Expenses</strong> – Utilities, Marketing, Salary, Travel ইত্যাদি সব খরচ এখানে category সহ রেকর্ড করুন।
                    Profit &amp; Loss রিপোর্টে “Expenses” অংশে এগুলো যোগ হয়ে “Profit = Net sales – Expenses” দেখায়।
                </li>
                <li>
                    <strong>Chart of accounts</strong> – Bank, Accounts Receivable, VAT Payable, Sales Revenue, Sales Returns,
                    Commission Expense, বিভিন্ন Expense account – এগুলো আগে থেকে create করলে রিপোর্টগুলো অর্থবহ হয়।
                </li>
                <li>
                    <strong>Bank reconciliation</strong> – Bank statement এর তারিখ অনুযায়ী কোন কোন receipt সত্যিকারের
                    bank‑এ জমা হয়েছে সেটা tick করে “Mark selected as reconciled” করলে পরে
                    audit / CA এর জন্য পরিষ্কার trail থাকে।
                </li>
                <li>
                    <strong>Profit &amp; Loss</strong> – সময় অনুযায়ী Sales, Sales returns, Commission, Expenses নিয়ে
                    Net profit দেখায়। এখানে + / – sign দিয়ে বুঝবেন:
                    Sales = +ve, Returns / Expenses = –ve।
                </li>
                <li>
                    <strong>Balance sheet</strong> – As of একটি তারিখে snapshot:
                    <em>Assets (Bank + Accounts Receivable + অন্য সম্পদ)
                        = Liabilities (VAT payable ইত্যাদি) + Equity</em>।
                    সহজ বাংলায় – আজ পর্যন্ত ব্যবসায় আপনি মোট কত invest করেছেন এবং এখন cash + receivable মিলিয়ে
                    কত সম্পদ আছে সেটা এই রিপোর্টে দেখা যায়।
                </li>
                <li>
                    <strong>Cashflow, Payroll, Agent performance, Production analysis</strong> – এগুলো summery রিপোর্ট:
                    টাকা আসা‑যাওয়া, HR খরচ, এজেন্ট level performance, production vs sales ইত্যাদি
                    সব এক জায়গায় বোঝার জন্য।
                </li>
            </ul>
            </div>
        </section>

        <section id="help-crm" class="help-section collapsed" data-collapsible style="margin-bottom:1rem;">
            <div class="help-section-header">
                <h2>7. CRM &amp; Marketing – Customer gifts, Campaigns</h2>
                <button type="button"
                        class="sidebar-toggle"
                        aria-label="Toggle CRM &amp; Marketing help"
                        data-collapse-toggle
                        data-open-icon="−"
                        data-closed-icon="+">
                    <span class="sidebar-toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="help-section-body">
            <ul class="help-steps">
                <li>
                    <strong>Customer gifts</strong> (Sales → Customer gifts) – কোন এজেন্টকে কোন তারিখে কী ধরনের gift, কত টাকার,
                    কোন campaign code এর against এ দেওয়া হলো – সব রেকর্ড করুন।
                </li>
                <li>
                    <strong>Marketing campaigns</strong> (Sales → Marketing campaigns) – Facebook / Instagram / Google ads,
                    Field promo – এগুলোর reach, impressions, cost, status, attachment (report / creative) log করলে
                    পরের দিন “প্রতি টাকায় কত sales এসেছে” সেটা হিসাব করা সহজ হয়।
                </li>
            </ul>
            </div>
        </section>
    </section>
</div>
@endsection
