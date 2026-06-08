# Saf ERP — Client manual (index)

> Plain-English guides for business users.  
> **Start with:** [How the whole system works](00-how-the-system-works.md) — then open your module guide below.

---

## Two layers of documentation

| Layer | Document | When to read |
|---|---|---|
| **Whole system** | **How the whole system works** | First day — see how all modules connect |
| **Your module** | One guide per area below | Daily work in your department |

In the app: **System settings → Client manual** shows all guides in one scrollable page.

---

## Module guides

| # | Guide | Who usually reads it |
|---|---|---|
| **00** | [How the whole system works](00-how-the-system-works.md) | Everyone — owners, trainers, new staff |
| **01** | [Products and materials](modules/01-products-and-materials.md) | Admin, purchase setup |
| **02** | [Agents and pricing](modules/02-agents-and-pricing.md) | Sales manager, admin |
| **03** | [Procurement — PO, GRN, bills](modules/03-procurement.md) | Purchase, warehouse receive |
| **04** | [Warehouses and routes](modules/04-warehouses-and-routes.md) | Warehouse manager, admin |
| **05** | [Manufacturing — BOM, production, QC](modules/05-manufacturing.md) | Production, QC, warehouse |
| **06** | [Inventory operations](modules/06-inventory.md) | Warehouse, inventory controller |
| **07** | [Sales orders and commissions](modules/07-sales.md) | Sales team |
| **08** | [Delivery and POD](modules/08-delivery-and-pod.md) | Delivery coordinator |
| **09** | [Accounting](modules/09-accounting.md) | Accounts, finance |
| **10** | [Reports and analytics](modules/10-reports.md) | Management, accounts |

---

## What Saf ERP does (summary)

| Area | Handles | Outcome |
|---|---|---|
| Procurement | PO, GRN, supplier bill | Materials in stock |
| Production | BOM, batch, run, QC | Finished goods in stock |
| Sales | Agent orders | Orders ready to ship |
| Delivery | Dispatch, POD | Goods delivered |
| Finance | Invoice, receipt, expense | Money tracked |
| Inventory | Transfers, audit | Stock accurate |

---

## User roles — daily focus

| Role | Main screens | Module guides |
|---|---|---|
| Purchase executive | Purchase orders, GRN | 03, 01 |
| Production officer | Production, batches, BOM | 05, 01 |
| QC officer | Production QC review | 05 |
| Warehouse officer | GRN, pending receipts, transfers, picking | 03, 05, 06, 08 |
| Sales officer | Sales orders | 07, 02 |
| Delivery coordinator | Deliveries, POD | 08, 04 |
| Accounts officer | Invoices, bills, expenses | 09, 10 |
| Admin | All master data, users | 01, 02, 04 + settings |

---

## Daily routine (all teams)

| Step | Task | Screen |
|---|---|---|
| 1 | Check dashboard alerts | `/admin` |
| 2 | GRN and procurement inbox | `/admin/purchase-orders`, `/admin/goods-receipts` |
| 3 | Production QC and pending receipts | `/admin/production` |
| 4 | New sales orders | `/admin/orders` |
| 5 | Dispatch and POD | `/admin/deliveries` |
| 6 | Invoices and receipts | `/admin/finance` |

---

## Status quick reference

### Sales order

Draft → Confirmed → Picked → Packed → Dispatched → Delivered

### Purchase order

Draft → Approved → Partial received → Received

### Production

Run created → QC pending → QC approved → Stock confirmed → Completed

---

## Common problems

| Problem | Likely cause | Module to check |
|---|---|---|
| Cannot confirm sales order | Low FG stock | 06, 05 |
| GRN did not add stock | QC line not approved | 03 |
| No invoice | Delivery not delivered | 08 |
| Production cannot confirm | QC pending or no RM stock | 05, 03 |
| Menu missing | Permission | Admin → users/roles |

---

## Month-end checklist

| Team | Check |
|---|---|
| Purchase | Open POs and pending GRN cleared |
| Warehouse | Audits and write-offs reviewed |
| Production | All pending stock confirms done |
| Sales | Dispatch backlog closed |
| Accounts | Invoices, receipts, outstanding reviewed |
| Management | P&L, stock valuation, aging (module 10) |

---

## For editors (maintaining these docs)

- Template: `docs/_module-template.md`
- Add new files to: `docs/manual-manifest.php`
- Standards: `docs/README.md`

---

Version: `v6.0`  
Last updated: `2026-06-07`

---

*The detailed module guides follow this index in the Client manual screen.*
