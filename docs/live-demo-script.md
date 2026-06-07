# Saf ERP — Live Demo Script & Client Presentation

Use this on demo day. Estimated time: **45–60 minutes**.

**Before you start**
- Laragon → **Start All**
- Server running: `http://127.0.0.1:8000`
- Login: `super@saferpv.local` / `password`
- Use **Chrome/Edge** (not Cursor browser)
- Always use **127.0.0.1** (not `localhost`)

---

## Part 0 — Opening slide talk (5 min)

### Slide 1: Title
**Saf ERP — Integrated Manufacturing & Distribution Platform**

- One system: factory + warehouse + sales + finance
- Built for agent-based FMCG / bottling businesses
- Bangladesh VAT & withholding ready

### Slide 2: The problem we solve
| Without ERP | With Saf ERP |
|---|---|
| Excel + WhatsApp orders | Single order truth |
| Stock guesswork | Real-time inventory + batch trace |
| Manual invoices | Invoice from delivered quantity |
| Late commission disputes | Agent performance + settlement |
| No management view | P&L, VAT, receivables dashboards |

### Slide 3: One business cycle
```
Purchase → GRN → Production → FG Stock → Agent Order →
Delivery/POD → Invoice/VAT → Receipt → Commission → Reports
```

**Say this:** “Every module connects. Sales cannot invoice without delivery. Production consumes BOM. Finance posts automatically.”

---

## Part 1 — Dashboard & control (5 min)

### Step 1.1 — Admin home
**URL:** `/admin`

**Show:**
- Notification bell (pending actions)
- Quick module access

**Say:** “Management starts here — alerts tell each role what needs action today.”

### Step 1.2 — Master data (products)
**URL:** `/admin/products`

**Point to seeded SKUs:**

| SKU | Product | Price |
|---|---|---:|
| SAF-500ML-CTN | Mineral Water 500ml – Carton (12) | BDT 550 |
| SAF-500ML | Mineral Water 500ml – Bottle | BDT 45 |
| SAF-1L-CTN | Mineral Water 1L – Carton | BDT 900 |
| SAF-20L-JAR | Mineral Water 20L – Jar | BDT 250 |

**Say:** “Products carry tax class, packaging, barcode — used in production, sales, and VAT.”

### Step 1.3 — Agents
**URL:** `/admin/agents`

**Point to:**
- Dhaka North Dealer 01
- Dhaka South Dealer 01
- Chattogram Dealer 01

**Say:** “Agents have pricing, credit, commission rules — every order links back here.”

### Step 1.4 — Tax
**URL:** `/admin/tax-classes`

**Say:** “Standard VAT 15% flows to invoice lines and VAT report automatically.”

---

## Part 2 — Manufacturing (8 min)

### Step 2.1 — BOM
**URL:** `/admin/boms`

**Show:** BOM for `SAF-500ML-CTN` (PET, caps, labels, cartons, water)

**Say:** “Production knows exactly what raw materials each carton consumes.”

### Step 2.2 — Production runs
**URL:** `/admin/production`

**Show seeded runs** (status: completed / confirmed)

**Say:** “Run → QC → confirm stock. FG enters warehouse with batch/expiry trace.”

### Step 2.3 — Batches
**URL:** `/admin/batches`

**Say:** “FEFO/FIFO — deliveries pick oldest approved batch first.”

### Step 2.4 — Manufacturing dashboard
**URL:** `/admin/manufacturing-dashboard`

**Say:** “Production manager sees pending QC, output, material usage.”

---

## Part 3 — Inventory (5 min)

### Step 3.1 — Inventory overview
**URL:** `/admin/inventory`

**Show:** Stock by warehouse, available vs reserved

**Say:** “When order confirms, stock reserves — no double-selling.”

### Step 3.2 — Stock movements
**URL:** `/admin/stock/movements` (or inventory menu)

**Say:** “Full audit trail: GRN in, production out, delivery out, transfers, write-offs.”

---

## Part 4 — Sales & delivery (10 min)

### Step 4.1 — Sales orders
**URL:** `/admin/orders`

**Show seeded orders** (all `confirmed`):

| Order | Agent | Total (approx) |
|---|---|---:|
| #1 | Dhaka North Dealer 01 | BDT 660,000 |
| #3 | Dhaka North Dealer 01 | BDT 825,000 |
| #6 | Dhaka South Dealer 01 | BDT 440,000 |

**Say:** “Order = agent + SKU qty + price + commission. Status tracks pick → pack → dispatch → deliver.”

### Step 4.2 — Deliveries
**URL:** `/admin/deliveries`

**Show:** Delivery #1 linked to Order #1 (status: scheduled)

**Say:** “Route, vehicle, POD capture delivered qty. Invoice only uses delivered truth.”

### Step 4.3 — Live mini-flow (optional — 5 min)
If you want to show live action:

1. Open Order #1 → progress to delivery if needed
2. Open Delivery #1 → update POD → mark **Delivered**
3. System consumes reserved stock

**Warning:** Only do this if you practiced once before the client meeting.

---

## Part 5 — Finance (12 min) ★ Most important for clients

### Step 5.1 — Invoices
**URL:** `/admin/finance`

**Show seeded invoices:**

| Invoice | Net | VAT (15%) | Gross |
|---|---:|---:|---:|
| INV-000001 | 660,000 | 99,000 | 759,000 |
| INV-000002 | 495,000 | 74,250 | 569,250 |

**Say:** “Invoice created from delivered order. VAT from product tax class. Withholding applied per agent rate.”

**Formula to say aloud:**
```
Line total = qty × unit price
VAT = net × 15%
Outstanding = (Net + VAT − Withholding) − Receipts − Credits
```

### Step 5.2 — Post a receipt (live demo)
1. Open **INV-000001**
2. Add receipt (e.g. BDT 500,000, method: Bank)
3. Show outstanding reduced

**Say:** “Receipt posts to Bank (debit) and Accounts Receivable (credit) automatically.”

### Step 5.3 — Supplier side (brief)
**URL:** `/admin/bills` and `/admin/purchase-orders`

**Say:** “Purchase bill creates AP. Payment reduces supplier liability. Input VAT feeds VAT report.”

### Step 5.4 — Accounting dashboard
**URL:** `/admin/accounting-dashboard`

**Say:** “Finance team sees receivables, payables, recent postings.”

---

## Part 6 — Reports & P&L (10 min) ★ Management slide

### Step 6.1 — Profit & Loss
**URL:** `/admin/reports/pl`

**Explain the formula:**
```
Net Sales     = Sales Revenue − Returns
COGS          = Estimated from production material cost
Gross Profit  = Net Sales − COGS
Net Profit    = Gross Profit − Commissions − Expenses − Payroll
```

**Be honest:** “COGS is management estimate from production costing — not a full statutory audit ledger.”

### Step 6.2 — Balance Sheet
**URL:** `/admin/reports/bs`

**Say:** “Snapshot: Bank, AR, AP, VAT Payable, equity.”

### Step 6.3 — VAT Report
**URL:** `/admin/reports/vat`

**Say:** “Output VAT (sales) minus Input VAT (purchases) = VAT payable for the period.”

### Step 6.4 — Agent Performance
**URL:** `/admin/reports/agent-performance`

**Say:** “Who sold, who paid, who still owes — by agent.”

### Step 6.5 — Reports dashboard
**URL:** `/admin/reports-dashboard`

**Say:** “Executive view — sales trend, stock, finance gaps.”

---

## Part 7 — Security & roles (3 min)

### Step 7.1 — Role demo (optional second login)
Log out → log in as `accounts@saferpv.local` / `password`

**Show:** Accounts officer sees finance menus, not all admin settings.

**URL:** `/admin/permissions` (as super admin)

**Say:** “Each role sees only what they need — warehouse, production, sales, accounts.”

---

## Part 8 — Close & Q&A (5 min)

### Slide: What Saf ERP is / is not

| ✅ Is | ❌ Is not |
|---|---|
| Operational ERP + embedded finance | Full SAP/Tally replacement |
| Invoice-to-cash + VAT reporting | Manual journal voucher GL |
| Management P&L & dashboards | Statutory audit sign-off system |
| Agent commission & delivery POD | Standalone accounting only |

### Slide: Implementation path
1. Master data setup (products, agents, warehouses)
2. Opening stock + BOM
3. User training by role
4. Parallel run (1 month)
5. Go-live

### Slide: Support & docs
- In-app help: `/admin/help`
- User manual: `docs/client-user-guide.md`
- Process cycle: `docs/system-cycle.md`

---

## Demo login cheat sheet

| Role | Email | Password |
|---|---|---|
| Super Admin (full demo) | super@saferpv.local | password |
| Accounts Officer | accounts@saferpv.local | password |
| Warehouse | warehouse@saferpv.local | password |
| Production | production@saferpv.local | password |
| Purchase | purchase@saferpv.local | password |

---

## Common client questions — quick answers

**Q: Is this full accounting software?**  
A: Operational ERP with auto ledger postings. Management reports yes; statutory audit may still use external accountant.

**Q: How is profit calculated?**  
A: P&L screen — revenue from ledger, COGS estimated from production material cost.

**Q: Can it handle returns?**  
A: Yes — delivery exceptions + credit notes adjust AR and VAT.

**Q: Bangladesh VAT?**  
A: Tax classes on products, VAT report with output vs input VAT.

**Q: Mobile for agents?**  
A: API routes exist (`/api`) — mobile app can connect separately.

---

## 30-minute short demo (if time is limited)

1. `/admin` — dashboard (2 min)
2. `/admin/products` + `/admin/agents` — masters (3 min)
3. `/admin/production` + `/admin/inventory` — make & stock (5 min)
4. `/admin/orders` + `/admin/deliveries` — sell & deliver (5 min)
5. `/admin/finance` — invoice + receipt (7 min)
6. `/admin/reports/pl` + `/admin/reports/vat` — management (8 min)

---

## Day-before checklist

- [ ] Laragon Start All
- [ ] `php artisan serve` running
- [ ] Test login at `http://127.0.0.1:8000/admin`
- [ ] Open P&L and Finance screens once (confirm data loads)
- [ ] Prepare second browser tab with reports ready
- [ ] Close unnecessary apps (stable internet not required — local server)

---

**Version:** 1.0 · Demo data from `migrate --seed`
