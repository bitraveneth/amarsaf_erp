# ERP v2 UAT Matrix

Sign off each module before production launch. Mark: **Pass** / **Fail** / **N/A**.

## Sales

| Test | Steps | Pass |
|------|-------|------|
| Create order | New order → add lines → save | |
| Invoice | Deliver order → create invoice | |
| Receipt | Record partial payment | |
| Credit note | Issue credit against invoice | |
| Sales register report | Open `/admin/reports/sales-register`, filter period | |
| Outstanding invoices | Open `/admin/reports/outstanding-invoices` | |

## Inventory

| Test | Steps | Pass |
|------|-------|------|
| Stock transfer | Transfer between locations | |
| Goods receipt | GRN from PO | |
| Low stock report | `/admin/reports/low-stock` | |
| Inventory valuation | `/admin/reports/inventory-valuation` | |

## Production

| Test | Steps | Pass |
|------|-------|------|
| Production run | Create → QC → confirm stock | |
| Production summary | `/admin/reports/production-summary` | |
| Batch trace | Lookup batch lot | |

## Finance / Accounting

| Test | Steps | Pass |
|------|-------|------|
| Expense | Create and post expense | |
| Purchase bill | Create bill → payment | |
| Journal entry | Manual journal post | |
| Income statement | `/admin/reports/income-statement` | |
| Balance sheet | `/admin/reports/balance-sheet` | |
| Trial balance | `/admin/reports/trial-balance` | |
| Bank reconciliation | Tool + report | |

## HR / Payroll

| Test | Steps | Pass |
|------|-------|------|
| Employee contract | Active contract on employee | |
| Payroll summary | `/admin/reports/payroll-summary` | |

## Permissions

| Test | Steps | Pass |
|------|-------|------|
| Warehouse role | Cannot access accounting | |
| Reports role | Can view reports, not settings | |

## Exports

| Test | Steps | Pass |
|------|-------|------|
| CSV export | Income statement CSV downloads | |
| Print | Report print layout readable | |

## Notes

Record failures with URL, screenshot, and steps to reproduce.

## Design checklist (visual QA)

Reference: [erp-design-guide.md](erp-design-guide.md) and [design-consistency-audit.md](design-consistency-audit.md).

| Check | Pass |
|-------|------|
| Page title uses `x-admin.page-header` or `erp-h1` (not `text-2xl`/`text-3xl`) | |
| KPI numbers use `erp-metric-value` / `x-admin.stat-card` | |
| Tables use `erp-table` with `erp-table-num` for amounts | |
| Header shows breadcrumbs on desktop; search centered on xl+ | |
| Language toggle in user menu (not global header bar) | |
| Bengali locale renders Noto Sans Bengali without layout breaks | |
| No new hex colours outside brand tokens | |

