# Transaction → ledger map

> **Who this is for:** Owners, accounts officers, client demos, CAs  
> **What you achieve:** Answer “where does this money go in the books?”

---

## One sentence

When you save an invoice, bill, expense, or payroll record, Saf ERP creates a **balanced journal entry** to your chart of accounts.

---

## Master table

| Business action | When it posts | Debit | Credit |
|---|---|---|---|
| Sales invoice | Issued from delivered order | Trade debtors | Product sales + VAT payable |
| Withholding on invoice | Agent has WHT % | WHT receivable | Trade debtors |
| Customer receipt | Payment on invoice | Bank | Trade debtors |
| Credit note | Return / adjustment | Sales returns (+ VAT) | Trade debtors |
| Agent advance given | Advance saved | Bank | Agent advances |
| Advance applied | On invoice | Agent advances | Trade debtors |
| Purchase bill (non-stock) | Bill saved | Purchases + Input VAT | Trade creditors |
| Purchase bill (stock) | Bill saved | GRNI + Input VAT | Trade creditors |
| Supplier payment | Bill paid | Trade creditors | Bank |
| GRN approved | QC OK, inventory GL on | Raw materials / FG | GRNI accrual |
| Production complete | Stock confirmed | WIP → FG | Raw materials |
| COGS on invoice | Invoice issued | RM consumption | Finished goods |
| Expense (paid) | Expense saved | Category expense | Bank |
| Expense (unpaid) | Payment = payable | Category expense | Utility / rent / salary payable |
| Payroll | Salary distribution saved | Salaries & wages | Bank / cash / salary payable |
| Commission accrual | Settlement accrued | Commission expense | Commission payable |
| Commission payment | Settlement paid | Commission payable | Bank |

---

## What does NOT post

- Purchase orders (until GRN)
- Sales orders (until invoice)
- Deliveries in transit
- HR records (contracts, leaves) — only **salary distributions** post payroll
- Stock transfers (no P&L unless write-off)

---

## Expense categories

Configure at **Accounting → Expense category mapping** (`/admin/expense-categories`).

| Category | Expense ledger | If unpaid (payable) |
|---|---|---|
| General | Selling & distribution | Trade creditors |
| Marketing | Marketing expense | Trade creditors |
| Utilities | Utilities expense | Utility bill payable |
| Salary | Salaries & wages | Salary payable |
| Rent | Office rent | Rent payable |
| Travel & delivery | Delivery expense | Trade creditors |

---

## Client examples

**DESCO ৳15,000 paid by bank:** Category Utilities, payment Bank → Dr Utilities expense 15,000 | Cr Bank 15,000

**Agent sale ৳100,000 net + 15% VAT:** Dr Trade debtors 115,000 | Cr Product sales 100,000 | Cr VAT payable 15,000

---

## In the app

**Learning Hub → Transaction → ledger map** — full bilingual course with quizzes.

Related: [Accounting](09-accounting.md) · [Reports](10-reports.md) · [Books for finance managers](13-books-for-finance-managers.md)
