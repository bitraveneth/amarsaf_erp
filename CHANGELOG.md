# Changelog

All notable Saf ERP changes are recorded here.

Format based on [Keep a Changelog](https://keepachangelog.com/).  
Version tags follow [Semantic Versioning](https://semver.org/).

---

## [2.3.0] - 2026-10-25

Sales invoice sheet. v2.2.0 did not include this layout.

### Changed

- Screen, print, and PDF invoices use the SAF sheet: logo, invoice-to block, line columns, totals, payment block, signature, and footer.
- Discount is withholding plus credit notes. Paid advance is receipts plus advances. Total due is the outstanding amount.
- Dompdf still uses the v2.2.0 security settings: files stay inside the application directory, and remote loading and PHP execution stay off.

## [2.2.0] - 2026-10-25

Security update for the October 2026 PHP bugs and the Composer packages those bugs sat next to.

### Security

- Deploy stops when PHP is missing the fixes for CVE-2026-109618 and CVE-2026-109619 (PHP2Shell). On Ubuntu it installs USN-8948-1 and restarts php-fpm and Apache. `php -v` can still print 8.3.6 or 8.1.2 after the Ubuntu package update.
- Guzzle 7.16.0, Symfony 6.4.46, CommonMark 2.10.4, PHPUnit 10.5.66, and Dompdf 3.1.6.
- PDF rendering stays inside the application directory. Remote file loading and PHP execution inside PDFs stay off.
- The API `token` guard rejects non-string credentials (CVE-2026-114509). Sign-in uses the session and Sanctum.
- Production session cookies are marked Secure when `APP_ENV=production`.

Laravel 10 still has advisories that are only fixed in Laravel 12 and 13: the email-rule header bug, signed-URL path confusion, and a debug-page issue that applies when `APP_DEBUG` is true.

## [2.0.0] - 2026-06-08

Major release on branch [`v2`](https://github.com/bitraveneth/Saf_Erp/tree/v2).  
Full before/after notes vs [v1.0.0](https://github.com/bitraveneth/Saf_Erp/releases/tag/v1.0.0): see [`docs/RELEASE-v2.md`](docs/RELEASE-v2.md).

### Added

- Centered brand login page at `/` with light/dark theme toggle (`layouts/auth.blade.php`).
- EN | BD locale toggle for guests and authenticated users (`LocaleController`, `SetLocale` middleware, `lang/bn/`).
- Export Center Phase 2: balance sheet, cash flow, outstanding invoices/bills, sales register exports.
- Month-end ZIP export pack with optional Tally XML (`MonthEndExportPackService`).
- Export preview polish and export buttons on finance report pages.
- Configurable top-bar notice strip (Settings → Brand) with info/warning/urgent types.
- Learning Hub with in-app lessons, context bar, and assistant integration.
- Client documentation system: whole-system guide + 10 module manuals under `docs/modules/`.
- Warehouse dashboard, procurement inbox, and fulfillment inbox UI strips.
- GRN approval workflow and PO line pricing fields (migrations).
- Order item fulfillment quantity tracking (migration).
- ERP Assistant improvements: intent router, insights, FAQ/data catalogs.
- Accounting services expansion: fiscal periods, journal entries, inventory accounting, aging, bank import.
- PDF document builder and improved company document templates.
- Transparent brand logo treatment across UI, PDFs, and order views.
- `auth-panel-hero.svg` brand illustration asset.

### Changed

- Root route `/` serves login; `/login` redirects to `/`.
- Dashboard KPIs, ops alerts, delivery progress, and order workflow UI refreshed.
- Purchase order, goods receipt, and inventory screens reworked.
- `FinanceController`, `ModuleExportRegistry`, and `TabularExport` extended for new exports.
- Sidebar menu structure and permissions seeders updated.
- Forgot-password and reset-password views aligned to new auth layout.

### Fixed

- Outstanding invoice export paid-total calculation (no `paid_total` on Invoice model).
- Month-end pack URL routing in `specialExportsForUser`.
- Black background wrapper removed from transparent SAF logo.
- System calculation regression coverage expanded.

### Known issues

- Month-end ZIP requires PHP `zip` extension; returns HTTP 503 with message when missing.

---

## [1.1.0] - 2026-04-07

### Inventory

- Added consistent `stock_movements` logging for goods receipts, production confirmation, stock reservations, deliveries, customer returns, and transfers.
- Fixed customer returns so they reuse a compatible batch-linked available stock entry when possible instead of creating a stray unbatched balance row.
- Improved stock movement and batch timeline UI so newer movement types render with readable labels and correct inbound/outbound summaries.

### Operations

- Added or exposed a clearer Stock Movements entry in the inventory menu fallback structure.
- Improved transfer notes to show warehouse and location names instead of raw IDs.
- Added GRN reversal handling that preserves movement history instead of silently deleting stock history.

### Verification

- Added regression coverage for GRN posting, production posting, reservation and delivery movement creation, customer return batch reuse, and transfer movement logging.

---

## [1.0.0] - 2026-04-08

First tagged stable release ([GitHub release](https://github.com/bitraveneth/Saf_Erp/releases/tag/v1.0.0)).

- Core Saf ERP modules: master data, procurement, manufacturing, inventory, sales, delivery, invoicing.
- TailAdmin-based admin UI and role-based navigation.
- Invoice design and system calculation fixes merged from hotfix `v1.0.1`.
- Basic export center and Tally export foundations.
- `client-user-guide.md` for end-user onboarding.
