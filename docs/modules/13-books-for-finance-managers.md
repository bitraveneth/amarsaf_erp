# Books for finance managers and CAs

> **Who this is for:** Chartered accountant, accounting manager, month-end owner  
> **What you achieve:** Understand how Saf ERP keeps the books, which screen is source of truth, and how to close a month.

This is a **reference**, not a training course. Staff who raise invoices day to day should use [Accounting](09-accounting.md). The full Dr/Cr list is in [Transaction → ledger map](11-ledger-mapping.md).

---

## What kind of system this is

Saf ERP is an **operational ERP with a journal-based general ledger**.

When someone saves an invoice, receipt, bill, GRN, expense, or salary distribution, the system posts a **balanced journal** (total debit = total credit). You then read those journals on trial balance, profit and loss, and general ledger.

| This is suitable for | This is not a replacement for |
|---|---|
| Management accounts | A full statutory audit pack on its own |
| VAT working papers | NBR filing software |
| AR / AP control | A second set of books in Tally unless you export |

For statutory work, export **Tally XML** or PDF/CSV from reports, then your practice software.

---

## Three layers

Think of the books in three layers. Do not mix them when you reconcile.

| Layer | What it is | Where you look |
|---|---|---|
| **1. Documents** | Invoices, receipts, purchase bills, GRNs, expenses, payroll | Accounting menus |
| **2. General ledger** | Posted journals against the chart of accounts | Journal entries, trial balance, GL |
| **3. Reports** | P&L, balance sheet, aging, VAT | Reports & analytics |

**Rule:** Layer 2 is the ledger. Layer 1 is the sub-ledger. If they disagree, a document exists that was not posted as a journal (or a journal was posted without a document).

---

## Source of truth

This is the question every CA asks first.

| Question | Trust this screen | Do not treat as the ledger |
|---|---|---|
| Did the books balance? | **Trial balance** and **Journal entries** | Dashboard tiles |
| What is profit? | **Income statement** (posted journals) | Accounting dashboard “profit” (that is invoices − expenses − payroll, a **management estimate**) |
| Who owes us? | **AR aging** list = invoice outstanding | Must still be **reconciled** to Trade debtors on the GL |
| Who we owe | **AP aging** list = bill outstanding | Reconcile to Trade creditors on the GL |
| Stock value | **Inventory valuation** shows both operational qty × cost **and** inventory GL | Either number alone |

**If AR aging total ≠ Trade debtors GL:** invoices exist that never created a sales journal, or journals were posted without invoices. On a **live** company, new invoices post automatically. On the **demo** database, many sample invoices were loaded without matching sales journals — do not audit the demo as if it were a live set of books.

---

## How a transaction hits the books

Four examples in BDT. Full table: [Transaction → ledger map](11-ledger-mapping.md).

### 1. Agent sale, net ৳100,000 + 15% VAT

Invoice issued from a **delivered** order.

| | Account | Dr | Cr |
|---|---|---|---|
| | Trade debtors | 115,000 | |
| | Product sales | | 100,000 |
| | VAT payable | | 15,000 |

If the agent has withholding, a second journal reduces Trade debtors and raises WHT receivable.

**Outstanding** = (Net + VAT − Withholding) − receipts − credit notes − advances applied.

### 2. Receipt ৳50,000 into bank

| | Account | Dr | Cr |
|---|---|---|---|
| | Bank | 50,000 | |
| | Trade debtors | | 50,000 |

Posted receipts cannot be deleted. Reverse with an adjusting entry if needed.

### 3. Materials received, then supplier bill

**GRN approved (stock, with unit cost):** Dr inventory (raw materials / packing / FG as mapped) | Cr GRNI accrual.

**Bill saved for those stock lines:** Dr GRNI (+ input VAT) | Cr Trade creditors. The bill **clears GRNI**; it does not hit Purchases again.

**Bill for non-stock / services:** Dr Purchases + Input VAT | Cr Trade creditors.

Purchase **orders** do not post. Nothing hits the P&L until GRN/bill (stock) or bill (expense).

### 4. Monthly payroll (salary distribution)

| | Account | Dr | Cr |
|---|---|---|---|
| | Salaries & wages | (gross) | |
| | Bank / cash / salary payable | | (same) |

Do **not** also book the same month as an Expense. That doubles the P&L.

---

## Chart of accounts

**Menu:** Accounting → **Chart of accounts** (`/admin/accounts`)

Groups (assets, income, manufacturing, opex) roll up on P&L and the balance sheet. **Only leaf accounts** receive postings.

You may rename a leaf with care. Do not delete an account that already has journals. Expense categories must stay mapped (**Accounting → Expense category mapping**).

**Inventory / COGS note:** When an invoice is issued, cost of goods sold is posted to the **raw material consumption** ledger (moving-average cost out of finished goods). The name on the P&L is a consumption line, not a separate “COGS” heading. Gross profit is still revenue minus the manufacturing section.

---

## How to read the statements

### Trial balance

Posted journals only, for the dates you pick. Opening + period = closing. Debit and credit columns are normal balances (assets debit, liabilities credit).

### Income statement

- **Net revenue** = sales (and other income) minus returns  
- **Gross profit** = net revenue − manufacturing costs  
- **Operating profit** = gross profit − admin (6100) − selling (6200)  
- **Net profit** = gross profit − all operating expenses, **including finance costs (6300)**

### Balance sheet

Equity on this report is **capital and retained earnings already on the COA**. **Current-year profit or loss is not auto-closed into retained earnings.**

Until you post a year-end close:

**Assets − (Liabilities + Equity) = year-to-date net profit / (loss) from the P&L.**

That difference is expected. It is not a broken trial balance. After you journal net income to retained earnings, the balance sheet equation will read Assets = Liabilities + Equity.

### Inventory valuation

Moving average. GRN and production update qty and cost. Invoice posts COGS and reduces finished goods.

The report shows **operational value** (qty × average cost) next to **GL inventory**. A large variance means stock movements happened without inventory journals, or journals were seeded without valuation rows.

---

## Month-end checklist

Do this in order. Screens are under **Accounting** and **Reports**.

1. **Accounting periods** — keep the month open while you post; close it when the pack is final.  
2. **Unposted items** — income statement may list draft logistics bills, expenses not in the ledger, payroll not linked to a journal. Post or void them.  
3. **Bank reconciliation** — match statement lines to receipts, payments, and expenses.  
4. **AR** — aging total vs Trade debtors GL. Investigate the variance, do not ignore it.  
5. **AP** — aging total vs Trade creditors GL.  
6. **Inventory** — valuation operational vs GL.  
7. **VAT** — VAT report vs VAT payable and Input VAT.  
8. **Print / export** — income statement, trial balance, balance sheet, aging.

---

## What you may change vs must not

| You may | You must not |
|---|---|
| Map expense categories to ledgers | Delete a posted receipt |
| Open / close accounting periods | Book payroll both as Expense and as Salary distribution |
| Post a **balanced** manual journal | Invoice an order before delivery (VAT and revenue too early) |
| Rename unused leaf accounts | Change product tax class after many invoices without a plan |

---

## Demo database vs live company

| | Demo | Live / fresh install |
|---|---|---|
| Purpose | Show screens to a buyer | Real books |
| Invoices vs GL | Many invoices **without** sales journals | New invoices post to the ledger |
| What to do | Do not audit it as statutory books | Work only through the screens |

A fresh company uses **Production seeder**: chart of accounts, roles, no fake invoices. From then on, if the team only uses the ERP screens, documents and journals stay aligned.

---

## Related guides

- [Accounting](09-accounting.md) — daily invoices, bills, expenses  
- [Transaction → ledger map](11-ledger-mapping.md) — full Dr/Cr cheat sheet  
- [Reports](10-reports.md) — report list and screens  
- [HR & payroll](12-hr-payroll.md) — what posts from payroll  

**In the app:** Learning Hub is staff training (lessons and quizzes). This Client manual chapter is the finance-manager reference.
