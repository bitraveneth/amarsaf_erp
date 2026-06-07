# Saf ERP — Manual UAT Sign-Off Checklist

**Environment:** Local (Laragon + MySQL)  
**Base URL:** `http://127.0.0.1:8000/admin`  
**Test date:** 7 June 2026  
**Tester:** Automated + manual verification  
**Build:** Laravel 10, seeded demo database  

---

## Executive summary

| Category | Result |
|----------|--------|
| Static checks (auth, routes, data, reports) | **14 PASS / 0 FAIL / 2 WARN** |
| Live order-to-cash workflow | **6 PASS / 0 FAIL** |
| HTTP UI smoke (20 admin pages) | **20 PASS / 0 FAIL** |
| **Overall UAT status** | **PASS — suitable for client demo** |

**Caveats (document for client):**
- Demo seed creates 5 invoices on **confirmed** orders (bypasses live rule). Live workflow requires **delivered** before invoice.
- 4 seeded receipts have **no AR/Bank ledger entries** (created directly in seeder). Live receipts posted via Finance UI **do** create ledger entries (verified on INV-000006).
- Accounting is **operational hybrid**, not full statutory GL (see `docs/client-presentation-guide.md`).

---

## 1. Access & login

| ID | Test | Steps | Expected | Result |
|----|------|-------|----------|--------|
| UAT-01 | Super admin login | Open `/login`, use `super@saferpv.local` / `password` | Dashboard loads, no 419 error | **PASS** |
| UAT-01b | Session stability | Use `127.0.0.1` only (not localhost mix) | Login persists | **PASS** (prior session fix confirmed) |
| UAT-10 | Accounts role | Login as `accounts@saferpv.local` / `password` | Accounting permissions active | **PASS** |

---

## 2. Master data & navigation

| ID | Test | Expected | Result |
|----|------|----------|--------|
| UAT-03 | Products, agents, warehouses | SKU `SAF-500ML-CTN`, dealers, warehouses present | **PASS** |
| UAT-02 | Key routes registered | Dashboard, P&L, agents, settlements, returns | **PASS** |
| UAT-UI | Admin pages HTTP 200 | 20 core modules (see section 6) | **PASS** |

---

## 3. Sales order → invoice → receipt (live flow)

**Executed on Order #6** (Dhaka South Dealer 01, BDT 440,000 net)

| Step | Action | Expected | Result |
|------|--------|----------|--------|
| 1 | Order status: confirmed → picked | Status updates | **PASS** |
| 2 | picked → packed | Status updates | **PASS** |
| 3 | packed → dispatched | Status updates | **PASS** |
| 4 | dispatched → delivered | Status = delivered | **PASS** |
| 5 | Auto-invoice on delivery | Invoice `INV-000006` created (net 440,000 + VAT 66,000) | **PASS** |
| 6 | Post receipt BDT 50,000 via Finance | Outstanding reduces; Bank DR + AR CR ledger entries | **PASS** |

**Post-test verification:**
- Invoice `INV-000006` outstanding: **BDT 456,000** (506,000 − 50,000)
- Live receipt created AR credit ledger entry (fixes prior WARN for seeded-only receipts)

---

## 4. Reports & finance

| ID | Test | Expected | Result |
|----|------|----------|--------|
| UAT-09 | P&L report | Renders without error | **PASS** |
| UAT-09 | Balance sheet (`/admin/reports/bs`) | Renders without error | **PASS** |
| UAT-09 | VAT report | Renders without error | **PASS** |
| UAT-09 | Agent performance (`/admin/reports/agents`) | Renders without error | **PASS** |
| UAT-08a | Ledger totals | AR and Sales Revenue > 0 | **PASS** |

---

## 5. Known warnings (not blockers for demo)

| ID | Finding | Impact | Recommendation |
|----|---------|--------|----------------|
| UAT-05 | 5 seeded invoices on non-delivered orders | Demo data only; confuses if shown as “live rule” | Explain seed vs live workflow in demo |
| UAT-08b | 4 seeded receipts without AR ledger | Historical seed gap | Use live receipt flow (Order #6) in demo |
| — | PHPUnit dev deps not installed | Automated test suite not run | Optional: `composer install` then `php artisan test` |

---

## 6. UI smoke — pages verified (HTTP 200)

All tested as super admin on 7 June 2026:

- `/admin` — Dashboard  
- `/admin/orders`, `/admin/orders/6` — Sales orders  
- `/admin/finance`, `/admin/finance/6` — Finance & invoice detail  
- `/admin/reports/pl`, `/admin/reports/bs`, `/admin/reports/vat`, `/admin/reports/agents`, `/admin/reports/payroll`  
- `/admin/settlements` — Commission settlements  
- `/admin/returns/customer` — Customer returns  
- `/admin/deliveries` — Deliveries  
- `/admin/inventory`, `/admin/production`  
- `/admin/purchase-orders`, `/admin/bills`  
- `/admin/agents`, `/admin/warehouses`, `/admin/employees`  

**Correct URLs (common doc typos):**

| Wrong | Correct |
|-------|---------|
| `/admin/reports/agent-performance` | `/admin/reports/agents` |
| `/admin/reports/balance-sheet` | `/admin/reports/bs` |
| `/admin/purchase-bills` | `/admin/bills` |
| `/admin/payroll` | `/admin/reports/payroll` |

---

## 7. Client demo script (recommended live path)

For a **live** end-to-end demo (not seeded shortcuts):

1. Pick an order without invoice (e.g. Order #7+ if still confirmed).
2. Progress status: **picked → packed → dispatched → delivered**.
3. Show auto-created invoice on Finance screen.
4. Post partial receipt → show outstanding and ledger.
5. Open P&L and VAT reports.
6. Switch to `accounts@saferpv.local` to show role-scoped accounting dashboard.

---

## 8. Sign-off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| QA / Tester | | | |
| Product owner | | | |
| Client representative | | | |

**UAT conclusion:** System passes manual UAT for **client presentation and operational demo**. Not certified as full statutory accounting ERP.

---

## Appendix — Re-run UAT scripts

From project root (Laragon PHP):

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe _uat_static.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe _uat_live.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe _uat_http.php
```

**Note:** `_uat_live.php` modifies data (progresses one order and posts a receipt). Use a fresh order for repeat runs.
