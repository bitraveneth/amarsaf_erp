# Saf ERP v2.0.0 — Release notes

**Repository:** [bitraveneth/Saf_Erp](https://github.com/bitraveneth/Saf_Erp)  
**Branch:** [`v2`](https://github.com/bitraveneth/Saf_Erp/tree/v2)  
**Previous stable release:** [v1.0.0](https://github.com/bitraveneth/Saf_Erp/releases/tag/v1.0.0)  
**Release date:** 8 June 2026

---

## Summary

v2 is a major functional and UX upgrade on top of the v1.0.0 baseline. v1 delivered a working ERP core (sales, inventory, manufacturing, accounting basics, invoices, and calculation fixes from the v1.0.1 hotfix). v2 adds **phase-2 finance exports**, a **redesigned login**, **EN | BD language support**, **Learning Hub**, **operational inbox strips**, **procurement/GRN workflow improvements**, **warehouse dashboard**, **expanded client documentation**, and broad UI polish across admin screens.

---

## What v1.0.0 included (baseline)

Reference: Git tag `v1.0.0` → merge of hotfix `v1.0.1` into `main` (April 2026).

| Area | v1.0.0 state |
|---|---|
| **Core ERP** | Products, agents, suppliers, POs, GRNs, inventory, manufacturing, sales orders, deliveries, invoices |
| **Accounting** | Basic finance screens, ledger-oriented workflows |
| **Stability** | System calculation fixes, invoice design fixes, manual system checks (v1.0.1 hotfix) |
| **UI** | TailAdmin-based admin shell, sidebar navigation |
| **Auth** | Standard Laravel login at `/login` |
| **Exports** | Early export center / Tally foundations |
| **Docs** | `client-user-guide.md` and internal setup notes |

---

## What is new in v2.0.0

### 1. Authentication & branding

| Before (v1) | Now (v2) |
|---|---|
| Generic login card on `/login` | **Centered brand login card** on `/` (root URL) |
| Logo sometimes forced onto black background | **Transparent SAF wordmark** — no black wrapper |
| No guest locale switch | **EN \| BD** toggle on login + `SetLocale` middleware |
| Light theme only on auth | **Light / dark** theme toggle on login |
| — | New `layouts/auth.blade.php`; forgot/reset password aligned |

### 2. Localization (Bangla)

| Before (v1) | Now (v2) |
|---|---|
| English-only UI | `lang/en/app.php` and `lang/bn/` string files |
| — | `LocaleController` + public `POST /locale` for guests |
| — | Login copy, header notice labels, and learning content support BN |

### 3. Export Center — Phase 1 & Phase 2

| Before (v1) | Now (v2) |
|---|---|
| Basic module export registry | **Financial report exports:** P&L, trial balance, GL, AR/AP aging, VAT |
| — | **Phase 2:** balance sheet, cash flow, outstanding invoices/bills, sales register |
| — | **Month-end ZIP pack** (`MonthEndExportPackService`, `MonthEndExportController`) |
| — | Export buttons on finance report pages |
| — | Richer export preview panel (highlights, row styles, numeric columns) |
| — | `FinancialReportExportService`, `TabularExport::csvContent()` for ZIP builds |

> **Server requirement:** PHP `zip` extension must be enabled for month-end ZIP downloads. Without it, the app returns HTTP 503 with a clear message.

### 4. Accounting & finance

| Before (v1) | Now (v2) |
|---|---|
| Finance controller basics | Expanded `FinanceController` — more reports and export hooks |
| Limited period handling | Fiscal period / accounting period services and models |
| — | `AccountingService`, aging, inventory accounting, bank import services |
| — | Journal entries, inventory valuation, webhook endpoints |
| — | Accounting dashboard and bank reconciliation improvements |

### 5. Procurement, GRN & purchase orders

| Before (v1) | Now (v2) |
|---|---|
| PO create/edit/show | **Pricing fields** on PO lines (migration `2026_06_08_120000`) |
| GRN basic flow | **GRN approval workflow** (migration `2026_06_08_140000`) |
| — | `purchase_orders/receive.blade.php`, procurement guide component |
| — | Procurement inbox strip on dashboard / layout |
| — | Improved goods receipt create/index/show screens |

### 6. Sales, orders & fulfillment

| Before (v1) | Now (v2) |
|---|---|
| Order list/show | **Fulfillment quantities** on order items (migration `2026_06_07_120000`) |
| Picking lists | Picking overview polish, order partials, workflow toolbar |
| — | Fulfillment inbox strip |
| — | Delivery progress dashboard components |
| — | `Delivery/` and `Sales/` service layers |

### 7. Dashboard & top bar

| Before (v1) | Now (v2) |
|---|---|
| Static dashboard KPIs | Refreshed snapshot KPIs, ops alerts, delivery progress |
| No broadcast notice | **Header notice strip** — configurable in Settings → Brand |
| — | Notice types: info / warning / urgent; optional link; BN labels |
| — | `WarehouseDashboardController` + warehouse dashboard view |

### 8. Learning Hub

| Before (v1) | Now (v2) |
|---|---|
| Help page only | Full **Learning Hub** (`LearningHubController`, `resources/learning/`) |
| — | Context bar on relevant screens, deep accounting lessons |
| — | `LearningAssistantService` integration with Saf AI assistant |
| — | Dedicated CSS/JS bundle (`learning-hub.css`, `learning-hub.js`) |

### 9. ERP Assistant (Saf AI)

| Before (v1) | Now (v2) |
|---|---|
| Basic assistant | Intent router, response composer, insights service |
| — | FAQ catalog, data catalog, dismiss controls, floating agent polish |

### 10. Documents & PDFs

| Before (v1) | Now (v2) |
|---|---|
| PDF layouts | `PdfDocumentBuilder`, improved company header/body/styles |
| Black logo box on PDFs | Transparent logo treatment on documents and order print |

### 11. Client documentation system

| Before (v1) | Now (v2) |
|---|---|
| Single user guide | **`docs/00-how-the-system-works.md`** — whole-system overview |
| — | **10 module guides** under `docs/modules/` (products → reports) |
| — | `manual-manifest.php` drives in-app Client manual viewer |
| — | `docs/README.md`, `_module-template.md` for future modules |

### 12. Inventory & manufacturing

| Before (v1) | Now (v2) |
|---|---|
| Stock screens | `Inventory/` services, material screen improvements |
| — | Manufacturing bulk data seeder for demos |
| — | MRP, batch traceability, production variance services |

### 13. Settings & system

| Before (v1) | Now (v2) |
|---|---|
| Brand settings | Header notice fields with live Alpine preview |
| — | `SystemSettings::headerNotice()` shared to all views |
| — | Employee allowances/contracts/equipment show pages |

### 14. Tests & tooling

| Before (v1) | Now (v2) |
|---|---|
| Calculation tests | Expanded `SystemCalculationTest` |
| — | `vite.config.js` updated for learning-hub entry |

---

## Upgrade path (v1 → v2)

```bash
git fetch origin
git checkout v2
composer install
npm install && npm run build
php artisan migrate
php artisan db:seed   # optional: demo / menu seeders if needed
```

1. Enable PHP **zip** extension in `php.ini` if using month-end export pack.
2. Replace `public/images/brand/saf-logo.png` with your transparent wordmark if needed.
3. Configure **Settings → Brand → Top bar notice** for urgent messages.
4. Root URL `/` is now the login page; `/login` redirects to `/`.

---

## Known limitations

| Item | Notes |
|---|---|
| Month-end ZIP | Requires `ext-zip`; documented 503 fallback |
| PHPUnit | Not bundled in vendor on all environments — run tests where `phpunit` is installed |
| GitHub release | Tag `v2.0.0` on branch `v2` after deploy verification |

---

## File index (high-signal new paths)

```
app/Http/Controllers/Admin/LearningHubController.php
app/Http/Controllers/Admin/LocaleController.php
app/Http/Controllers/Admin/MonthEndExportController.php
app/Http/Controllers/Admin/WarehouseDashboardController.php
app/Http/Middleware/SetLocale.php
app/Services/Accounting/FinancialReportExportService.php
app/Services/Accounting/MonthEndExportPackService.php
docs/00-how-the-system-works.md
docs/modules/
docs/RELEASE-v2.md
lang/bn/
resources/views/layouts/auth.blade.php
resources/views/components/layout/header-notice.blade.php
resources/views/components/locale-toggle.blade.php
public/images/brand/auth-panel-hero.svg
```

---

## Credits

Built on the Saf ERP v1.0.0 foundation. v2 development span: April–June 2026.
