# Saf ERP — Client Presentation & System Reference

**Product:** Saf ERP (A-ERP)  
**Stack:** Laravel 10, MySQL, Tailwind/Vite admin UI  
**Industry fit:** Manufacturing + distribution (FMCG / bottling / agent-based sales)  
**Tax context:** Bangladesh VAT, withholding tax, HSN-style tax classes  

Use this document for client demos, delivery handover, and internal training.

---

## 1. Executive summary (what to tell the client)

Saf ERP is an integrated operations platform covering:

- **Procurement** — purchase orders, goods receipt (GRN), supplier bills, AP
- **Manufacturing** — BOM, batches, production runs, QC, material consumption
- **Inventory** — multi-warehouse stock, transfers, write-offs, audits, FEFO/FIFO traceability
- **Sales & distribution** — agent orders, pricing, credit limits, commission rules
- **Logistics** — routes, vehicles, deliveries, proof of delivery (POD), returns
- **Finance** — invoicing, VAT, withholding, receipts, credit notes, expenses, payroll
- **Reporting** — P&L, balance sheet, cash flow, VAT, agent performance, production summary

One business cycle connects factory and market:

**Purchase → GRN → Production → FG stock → Agent order → Delivery/POD → Invoice → Collection → Commission → Management reports**

---

## 2. Accounting model (important for finance stakeholders)

### 2.1 Model type

Saf ERP uses a **practical hybrid accounting model**, not full IFRS/GAAP ERP ledger software like SAP or Oracle.

| Layer | What it is | Standard? |
|---|---|---|
| Chart of Accounts | Structured accounts table (`asset`, `liability`, `equity`, `income`, `expense`) | Partial — reference master only |
| Operational ledger | `ledger_entries` table with debit/credit by account **name** | Inspired by double-entry, simplified |
| Sub-ledgers | Invoices, receipts, purchase bills, expenses, payroll | Operational sub-ledger |
| Inventory costing | BOM + production material cost snapshots | Manufacturing ERP style |
| P&L / Balance sheet | Aggregated reports from ledger + expenses + payroll + estimated COGS | Management reporting, not statutory audit ledger |

**Bottom line for the client:**

- It is **not** a certified full double-entry general ledger with journal vouchers, trial balance drill-down, and period close controls.
- It **is** a strong **operational ERP with embedded finance posting** suitable for day-to-day business control, VAT reporting, receivables/payables tracking, and management P&L.
- For statutory audit, many clients export invoice/AP data to their external accountant or ERP.

### 2.2 How postings work

When key business events happen, the system creates **ledger entries** automatically.

#### Sales invoice (from delivered order)

| Account | Debit | Credit |
|---|---:|---:|
| Accounts Receivable | Net + VAT | |
| Sales Revenue | | Net |
| VAT Payable | | VAT amount |
| Withholding Tax Receivable | Withholding (if agent has rate) | |
| Accounts Receivable | | Withholding |

#### Customer receipt

| Account | Debit | Credit |
|---|---:|---:|
| Bank | Amount received | |
| Accounts Receivable | | Amount received |

#### Credit note (returns/adjustments)

| Account | Debit | Credit |
|---|---:|---:|
| Sales Returns | Net credit | |
| VAT Payable | VAT reversal | |
| Accounts Receivable | | Total credit |

#### Supplier purchase bill

| Account | Debit | Credit |
|---|---:|---:|
| Purchases | Net | |
| Input VAT | VAT | |
| Accounts Payable | | Net + VAT |

#### Supplier payment

| Account | Debit | Credit |
|---|---:|---:|
| Accounts Payable | Amount | |
| Bank | | Amount |

#### Other supported postings

- Agent advance application
- Customer gifts → Selling & Distribution Expense / Bank
- Commission settlement → Commission Expense
- Expenses module → expense accounts via manual/operational entries

### 2.3 Default chart of accounts (seeded)

| Code | Account | Type |
|---|---|---|
| 1000 | Bank | Asset |
| 1100 | Accounts Receivable | Asset |
| 2000 | VAT Payable | Liability |
| 2100 | Accounts Payable | Liability |
| 3000 | Owner's Equity | Equity |
| 4000 | Sales Revenue | Income |
| 4100 | Other Income | Income |
| 5000 | Cost of Goods Sold | Expense |
| 5100 | Selling & Distribution Expense | Expense |
| 5200 | Marketing Expense | Expense |
| 5300 | Payroll Expense | Expense |
| 5400 | Utilities Expense | Expense |

Additional account names appear in ledger postings (e.g. Purchases, Input VAT, Sales Returns, Commission Expense, Withholding Tax Receivable) even if not all are pre-seeded in the chart table.

### 2.4 VAT and withholding

- **Output VAT:** calculated from product tax class on invoice lines → credited to **VAT Payable**
- **Input VAT:** from supplier bills → debited to **Input VAT**
- **VAT report:** output VAT minus input VAT for the period (`/admin/reports/vat`)
- **Withholding:** agent-specific rate applied on invoice; reduces cash collection expectation

**Outstanding receivable formula:**

`(Net + VAT - Withholding) - Receipts - Advance applications - Credit notes`

---

## 3. Profit & Loss — how the system calculates it

**Screen:** `/admin/reports/pl`

### 3.1 P&L formula

```
Net Sales        = Sales Revenue (credit) - Sales Returns (debit)
COGS (estimated) = Sum of (invoice qty × avg production material_unit_cost per product)
Gross Profit     = Net Sales - COGS
Operating costs  = Commission Expense + Expenses module + Customer gifts + Campaigns + Payroll
Net Profit       = Gross Profit - Operating costs
```

### 3.2 Important limitations (state these honestly in demo)

1. **COGS is estimated** from production run material cost averages, not from perpetual inventory GL posting.
2. **Not every inventory movement** posts to a COGS ledger account automatically.
3. **Payroll** comes from salary distribution records, not full HR/payroll statutory module.
4. P&L is best treated as **management estimate** for decision-making, not audited financial statements.

### 3.3 Related financial reports

| Report | Path | Purpose |
|---|---|---|
| Profit & Loss | `/admin/reports/pl` | Revenue, COGS estimate, expenses, net profit |
| Balance Sheet | `/admin/reports/bs` | Assets vs liabilities snapshot; equity = assets - liabilities |
| Cash Flow | `/admin/reports/cashflow` | Bank debits vs credits in period |
| VAT Report | `/admin/reports/vat` | Output vs input VAT |
| Agent Performance | `/admin/reports/agent-performance` | Sales, receipts, outstanding by agent |
| Production Summary | `/admin/finance/production-summary` | Production cost vs sales estimate |
| Payroll Summary | `/admin/reports/payroll-summary` | Salary distributions by employee |

---

## 4. End-to-end system workflow

### 4.1 One-line cycle

```
Supplier PO → GRN → Supplier Bill → BOM Issue → Production → FG Batch →
Agent Order → Reserve Stock → Pick/Pack → Dispatch → POD →
Invoice/VAT → Receipt → Bank Reco → Commission → Reports
```

### 4.2 Factory side (Cycle A)

| Step | Module | Key output |
|---|---|---|
| Master setup | Control | Products, materials, BOM, warehouses, agents |
| Purchase | Procurement | Approved PO |
| Receive | GRN | RM/PM stock increased (approved QC lines only) |
| Bill | AP | Supplier liability posted |
| Produce | Manufacturing | FG batch with expiry |
| Transfer | Inventory | Stock at depot/route level |

### 4.3 Market side (Cycle B)

| Step | Module | Key output |
|---|---|---|
| Order | Sales | Confirmed order, stock reserved |
| Fulfillment | Inventory | Pick list, dispatch |
| Deliver | Logistics | POD, delivered qty truth |
| Invoice | Finance | AR + revenue + VAT posted |
| Collect | Finance | Bank receipt, AR reduced |
| Commission | Sales/Finance | Agent commission settled |

### 4.4 Status gates (business rules to demo)

| Action | Required status / rule |
|---|---|
| Create invoice | Order must be **delivered** |
| GRN stock increase | QC line must be **approved** |
| Reserve stock | Available qty must exist |
| Post receipt | Amount ≤ outstanding |
| Credit note | Cannot exceed remaining invoice value |

---

## 5. Module map for presentation slides

| # | Module | Client value | Demo screen |
|---|---|---|---|
| 1 | Control & masters | Single source of truth | `/admin/products`, `/admin/agents` |
| 2 | Tax & compliance | VAT/withholding on transactions | `/admin/tax-classes`, `/admin/reports/vat` |
| 3 | Procurement | Controlled buying & AP | `/admin/purchase-orders`, `/admin/goods-receipts`, `/admin/bills` |
| 4 | Manufacturing | BOM-driven production & batch trace | `/admin/boms`, `/admin/production`, `/admin/batches` |
| 5 | Inventory | Real-time stock & audit trail | `/admin/inventory`, `/admin/stock/transfers` |
| 6 | Sales | Agent channel order management | `/admin/orders`, `/admin/agents` |
| 7 | Delivery | Route/POD accountability | `/admin/deliveries`, `/admin/vehicles` |
| 8 | Finance | Invoice-to-cash | `/admin/finance` |
| 9 | HR/Payroll (lite) | Employee, allowances, salary distribution | `/admin/employees` |
| 10 | Reports | Management visibility | `/admin/reports/pl`, `/admin/accounting-dashboard` |

---

## 6. Role-based demo script (45–60 minute client session)

### Part 1 — Vision (5 min)

Explain integrated factory + distribution model using section 1 and the one-line cycle.

### Part 2 — Master data (5 min)

Show product, BOM, agent pricing, warehouse structure.

### Part 3 — Buy and make (10 min)

Live or seeded:

1. Open approved PO
2. Post GRN with QC approved
3. Open production run → QC → confirm stock
4. Show FG inventory and batch/expiry

### Part 4 — Sell and deliver (10 min)

1. Create agent order
2. Show stock reservation
3. Create delivery, assign route/vehicle
4. Capture POD → mark delivered

### Part 5 — Finance (10 min)

1. Generate invoice from delivered order
2. Show VAT and withholding
3. Post receipt
4. Show agent outstanding reduced

### Part 6 — Management reports (10 min)

1. **P&L** — revenue, COGS estimate, net profit
2. **Balance sheet** — AR, bank, AP, VAT
3. **VAT report** — output vs input
4. **Agent performance** — sales vs collection

### Part 7 — Controls & security (5 min)

- Role permissions
- Audit trail on stock movements
- Notification alerts for pending actions

### Q&A anchors

- "Is this full accounting software?" → Section 2.1
- "How is profit calculated?" → Section 3
- "Can it handle returns?" → Customer returns + credit notes
- "Bangladesh VAT?" → Tax classes + VAT report

---

## 7. Month-end checklist (for delivery handover)

| Team | Close tasks |
|---|---|
| Purchase | Review open POs, clear GRN backlog |
| Warehouse | Complete write-offs, review audit variances |
| Production | Confirm all pending production runs |
| Sales/Delivery | Close dispatch backlog, finalize POD |
| Accounts | Reconcile invoices, receipts, credit notes, AP |
| Management | Review P&L, stock summary, receivable aging |

---

## 8. Document pack for client delivery

Include these files from the repository:

| Document | Audience |
|---|---|
| `docs/client-user-guide.md` | End users (operations manual) |
| `docs/system-cycle.md` | Process owners / consultants |
| `docs/configuration-guide.md` | IT / server admin |
| `docs/local-setup-guide.md` | Internal demo/UAT setup |
| `docs/client-presentation-guide.md` | Sales / project delivery (this file) |

In-app help: `/admin/help`

---

## 9. Known scope boundaries (set expectations)

Be transparent with delivery clients:

1. Simplified GL — not replacement for full accounting audit systems
2. COGS on P&L is estimated from production costing
3. Mobile agent app may be separate (API routes exist under `/api`)
4. Statutory payroll/tax filing formats may need external export
5. Multi-company / multi-currency not assumed in current design

---

## 10. Quick reference — demo credentials

After `php artisan migrate --seed`:

- **URL:** `http://your-local-url/admin`
- **Super admin:** `super@saferpv.local` / `password`
- **Accounts demo:** `accounts@saferpv.local` / `password`

Change passwords before any production deployment.

---

**Version:** 1.0  
**Prepared for:** Client presentation & delivery handover  
**Source repo:** https://github.com/bitraveneth/Saf_Erp
