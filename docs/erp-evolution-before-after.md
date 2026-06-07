# Saf ERP — Before & After (Phases 1–7)

This document summarizes what changed across the seven implementation phases: what the system could do **before**, what it can do **now**, and what you should consider doing next.

**Demo login:** `super@saferpv.local` / `password`  
**Base URL:** `http://127.0.0.1:8000` (or your Laragon host)

---

## Executive summary

| Area | Before | Now |
|------|--------|-----|
| **Accounting** | Operational sub-ledger (`ledger_entries`) with auto-posting on invoices/receipts; no formal GL close | Full **journal-based GL** with periods, trial balance, general ledger, manual journals, reversals |
| **Inventory costing** | Estimated COGS from production snapshots in P&L | **Perpetual inventory GL** on GRN, production, sales, write-offs; real COGS in P&L when posted |
| **Receivables / payables** | Outstanding tracked on invoices/bills only | **AR/AP aging** reports with bucket analysis and GL reconciliation hints |
| **Banking** | Receipts recorded individually | **Bank reconciliation** with CSV import; supplier **batch payments** with batch reference |
| **Manufacturing planning** | BOM + production only | **MRP-lite**, **low-stock alerts**, **production variance**, **batch traceability** |
| **Field sales** | Commission rules in admin; basic agent API | **Collection-based commission API**, **POD PDF**, route optimize with **vehicle capacity** warnings |
| **Compliance / exports** | VAT report on screen | **VAT CSV export**, **invoice JSON**, **e-invoice JSON**, **Tally XML** from posted journals |
| **Platform** | Menu + module screens only | **Global search**, **command palette (Ctrl+K)**, **webhooks**, **finance summary API** |

---

## Phase 1 — Journal-based general ledger

### Before
- Finance relied on simplified `ledger_entries` created when invoices, receipts, bills, etc. were posted.
- Chart of accounts existed as a reference master but there were no journal vouchers, no period close, and no trial balance / GL drill-down.
- Manual adjusting entries were not supported in a structured way.

### Now
- **Journal entries** with balanced debit/credit lines, draft → post → reverse workflow.
- **Accounting periods** with open/closed control — posting blocked into closed periods.
- **Trial balance** and **general ledger** reports from posted journals.
- Expanded chart of accounts seeder aligned with manufacturing + distribution.
- Legacy `ledger_entries` still exist for backward compatibility; new inventory and structured flows prefer journals.

### Key screens
| Screen | URL |
|--------|-----|
| Journal entries | `/admin/journals` |
| Accounting periods | `/admin/accounting-periods` |
| Trial balance | `/admin/reports/trial-balance` |
| General ledger | `/admin/reports/general-ledger` |
| Chart of accounts | `/admin/accounts` |

---

## Phase 2 — Perpetual inventory → GL

### Before
- Stock quantities tracked in warehouses; production material cost stored on runs.
- P&L **COGS was estimated** from production `material_unit_cost` averages when no GL COGS existed.
- No inventory valuation report tied to GL inventory accounts.

### Now
- **`InventoryAccountingService`** posts GL journals when:
  - Goods receipt (GRN) is approved
  - Production run stock is confirmed
  - Customer invoice is issued (COGS + inventory relief)
  - Inventory write-off / adjustment occurs
- **`InventoryCostingService`** resolves unit costs (standard cost, GRN cost, BOM snapshot).
- P&L prefers **real GL COGS** when journal postings exist; falls back to estimate only if GL is empty.
- **Inventory valuation** report shows stock value by product/warehouse.

### Key screens
| Screen | URL |
|--------|-----|
| Inventory valuation | `/admin/reports/inventory-valuation` |
| P&L (GL COGS) | `/admin/reports/pl` |

---

## Phase 3 — AR/AP aging, bank reconciliation, batch pay

### Before
- Outstanding amounts visible per invoice/bill in finance screens.
- No aging buckets, no bank statement matching workflow, supplier payments one bill at a time.

### Now
- **AR aging** — outstanding receivables by agent, bucketed (current, 1–30, 31–60, 61–90, 90+).
- **AP aging** — outstanding payables by supplier with same buckets.
- **Bank reconciliation** — import bank CSV, match inflows (receipts) and outflows (bill payments).
- **Supplier payment batch** — pay multiple bills in one action with a shared `batch_reference`.

### Key screens
| Screen | URL |
|--------|-----|
| AR aging | `/admin/reports/ar-aging` |
| AP aging | `/admin/reports/ap-aging` |
| Bank reconciliation | `/admin/finance/reconciliation` |
| Purchase bills (batch pay) | `/admin/bills` |

---

## Phase 4 — MRP-lite, low stock, variance, batch trace

### Before
- Manufacturing: BOMs, production runs, batches existed but no planning suggestions.
- No reorder-level field, no low-stock dashboard, no standard-vs-actual production cost report.
- Batch records existed; trace across GRN → delivery → invoice was manual.

### Now
- **`reorder_level`** on products (edit form + DB column).
- **MRP suggestions** — products below reorder level (minus open PO qty) + BOM component shortages.
- **Low stock report** — products where available qty < reorder level.
- **Low-stock notifications** in ERP notification sync when count > 0.
- **Production variance** — standard (BOM) unit cost vs actual material cost per confirmed run.
- **Batch traceability** — lookup by batch code; detail shows production runs, GRN lines, deliveries, related invoices.

### Key screens
| Screen | URL |
|--------|-----|
| MRP suggestions | `/admin/mrp` |
| Low stock | `/admin/inventory/low-stock` |
| Production variance | `/admin/reports/production-variance` |
| Batch trace lookup | `/admin/reports/batch-trace` |
| Batch trace detail | `/admin/reports/batch-trace/{batch_id}` |

---

## Phase 5 — Agent commissions, POD, route capacity

### Before
- Commission rules and admin commission report existed.
- Agent mobile API had orders, invoices, statement — no dedicated commissions endpoint.
- POD captured as uploads; no printable POD PDF from admin.
- Route optimization did not consider vehicle crate capacity.

### Now
- **`GET /api/agent/commissions?month=YYYY-MM`** returns:
  - **accrual** — commission on delivered/invoiced sales in month
  - **collection** — commission on **paid** invoices only (cash-collection basis)
- **POD PDF** — `/admin/deliveries/{delivery}/pod-pdf`
- **Route optimize** — flags deliveries when assigned vehicle **capacity (crates)** would be exceeded (`exception_notes` on delivery).

### Key endpoints
| Endpoint | Purpose |
|----------|---------|
| `GET /api/agent/commissions` | Agent commission summary (accrual + collection) |
| `GET /admin/deliveries/{id}/pod-pdf` | Printable proof of delivery |

---

## Phase 6 — VAT export, invoice JSON, Tally

### Before
- VAT report on screen only; no structured export for filing or external accountant.
- No machine-readable invoice export; no bridge to Tally or similar GL tools.

### Now
- **VAT CSV export** — `/admin/reports/vat/export?month=YYYY-MM` (button on VAT report page).
- **Invoice JSON** — `/admin/finance/{invoice}/json`
- **E-invoice JSON (v1)** — `/admin/finance/{invoice}/e-invoice` (buyer uses agent `special_code` / `location_code` as reference)
- **Tally XML** — `/admin/exports/tally` — exports **posted journal entries** for a date range.

### Key screens
| Screen | URL |
|--------|-----|
| VAT report + export buttons | `/admin/reports/vat` |
| Tally export | `/admin/exports/tally` |

---

## Phase 7 — Search, webhooks, finance API

### Before
- Navigation by sidebar menu only; no cross-entity search.
- No outbound event hooks for integrations.
- No lightweight finance KPI API for dashboards or external tools.

### Now
- **Global search page** — `/admin/search?q=...` (products, orders, invoices, agents, batches).
- **Command palette (Ctrl+K)** — suggests entities via `/admin/search/suggest` (requires built frontend assets).
- **Webhook endpoints** — `/admin/webhooks` — register URLs; **`journal.posted`** event dispatched when a journal is posted.
- **Finance summary API** — `GET /api/finance/summary` (admin / super_admin) — AR sub-ledger total, GL AR balance, open invoice count, stock units.

### Key screens / endpoints
| Item | URL |
|------|-----|
| Search | `/admin/search` |
| Webhooks | `/admin/webhooks` |
| Finance summary API | `GET /api/finance/summary` |

---

## Database & technical additions (summary)

| Addition | Purpose |
|----------|---------|
| `journal_entries`, `journal_entry_lines`, `accounting_periods` | Formal GL (Phase 1) |
| `inventory_gl_posts` | Idempotent inventory → GL posting tracker (Phase 2) |
| `bill_payments.batch_reference` | Supplier batch payment grouping (Phase 3) |
| `products.reorder_level` | MRP + low stock (Phase 4) |
| `webhook_endpoints` | Outbound integrations (Phase 7) |

**Main new services:** `AccountingService`, `InventoryAccountingService`, `InventoryCostingService`, `AgingReportService`, `MrpService`, `ProductionVarianceService`, `BatchTraceabilityService`, `TallyExportService`, `GlobalSearchService`, `WebhookDispatcher`.

---

## Deployment checklist (after pulling latest code)

Run these on **each environment** (local Laragon, staging, production):

```bash
php artisan migrate --force
php artisan db:seed --class=MenuStructureSeeder --force
npm run build
php artisan config:clear
php artisan route:clear
```

If you use two copies of the project (`Projects\Saf_Erp` and `laragon\www\Saf_Erp`), keep them in sync and migrate both.

---

## Suggested next steps

### Immediate (this week)

1. **Set reorder levels** on key raw materials and finished goods so MRP and low-stock reports show meaningful data.
2. **Walk through one full cycle** with the new reports:
   - GRN → confirm production → deliver → invoice → receipt → check trial balance + inventory valuation + AR aging.
3. **Test exports** with real data:
   - Download VAT CSV for last month.
   - Import Tally XML into a test Tally company (verify account name mapping).
   - Open e-invoice JSON and confirm buyer reference fields meet your compliance needs.
4. **Re-seed or verify menu** if sidebar links are missing (`MenuStructureSeeder` or check Menu manager).
5. **Rebuild assets** if Ctrl+K search does nothing (`npm run build`).

### Short term (before go-live / client handover)

6. **Update `docs/client-presentation-guide.md`** — Section 2.1 still describes the old “no trial balance / no journal vouchers” model. It should reflect Phases 1–7.
7. **Add agent tax / BIN field** if e-invoice compliance requires a formal tax identifier (currently uses `special_code` / `location_code` as buyer reference).
8. **Configure webhooks** for any external system (BI tool, WhatsApp bot, middleware) and document which events you rely on (`journal.posted` today; more events can be added later).
9. **Train finance users** on:
   - Manual journals and period close
   - When to use trial balance vs operational finance screens
   - Bank reconciliation CSV format expectations
10. **Test agent app** against `GET /api/agent/commissions` — confirm collection vs accrual matches your commission policy.

### Medium term (quality & scale)

11. **Automated tests** for aging, inventory GL posting, MRP, and commission API — reduces regression risk as you keep building.
12. **Reconcile legacy vs new GL** — understand which flows still write `ledger_entries` only vs full journals; aim for one source of truth over time.
13. **Webhook delivery log** — today failures are logged only; a retry queue / admin log UI would help production integrations.
14. **Expand search** to suppliers, purchase bills, production runs if users ask for it.
15. **Production deployment** — env-specific `.env`, queue worker if webhooks/async notifications grow, scheduled `SyncErpNotifications` on cron.

### Optional enhancements (not built yet)

| Idea | Why |
|------|-----|
| Auto-generate PO from MRP suggestions | Closes the loop from planning to procurement |
| More webhook events (`invoice.issued`, `order.delivered`) | Richer integrations |
| FEFO/expiry alerts on batches | Compliance for food/beverage |
| Multi-currency / multi-company | Only if business expands beyond current scope |
| Statutory audit export pack | Single ZIP: TB, GL, VAT CSV, AR/AP aging |

---

## Quick reference — all new URLs (Phases 1–7)

```
/admin/journals
/admin/accounting-periods
/admin/reports/trial-balance
/admin/reports/general-ledger
/admin/reports/inventory-valuation
/admin/reports/ar-aging
/admin/reports/ap-aging
/admin/finance/reconciliation
/admin/mrp
/admin/inventory/low-stock
/admin/reports/production-variance
/admin/reports/batch-trace
/admin/reports/vat/export
/admin/exports/tally
/admin/finance/{invoice}/json
/admin/finance/{invoice}/e-invoice
/admin/deliveries/{delivery}/pod-pdf
/admin/search
/admin/webhooks

GET /api/agent/commissions?month=YYYY-MM
GET /api/finance/summary
```

---

## Related docs

| Document | Use for |
|----------|---------|
| `docs/local-setup-guide.md` | Installing and running locally |
| `docs/client-presentation-guide.md` | Client demo script (needs accounting section update) |
| `docs/client-user-guide.md` | End-user module help |
| `docs/system-cycle.md` | Business process flow |

---

*Last updated: June 2026 — covers Phases 1 through 7.*
