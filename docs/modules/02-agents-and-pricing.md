# Agents and pricing

> **Who this is for:** Sales manager, Admin  
> **What you achieve:** Agents can place orders at the right price, and commissions are calculated correctly.

---

## Before you start

- [ ] Finished **products** exist (module 01)
- [ ] **Tax classes** set up if invoices need VAT

---

## Main flow

```mermaid
flowchart LR
    A[Create agent] --> B[Set zone and credit]
    B --> C[Commission rules]
    C --> D[Price list optional]
    D --> E[Agent places sales order]
```

---

## Step-by-step

### Create an agent

**Menu:** Control → Agents → **Agents** → Create  
**Screen:** `/admin/agents/create`

| Field | Required | Meaning |
|---|---|---|
| Name | Yes | Agent or dealer name |
| Zone / area | Recommended | Used on orders and reports |
| Phone | Recommended | Contact |
| Credit limit | Optional | Blocks orders if exceeded |
| Withholding rate | Optional | Deducted on invoices |
| Active | Yes | Inactive agents cannot order |

### Commission rules

**Menu:** Control → Agents → **Commission rules**  
**Screen:** `/admin/commission-rules`

Define how agents earn commission (e.g. % of order total). Used in commission reports and settlements.

### Price lists

**Menu:** Control → Products → **Price lists**  
**Screen:** `/admin/products-price-list`

Override **base price** per agent and product. When an agent is selected on a sales order, the system fills unit prices from their price list.

---

## Common mistakes

- Forgetting to activate the agent — they will not appear on orders.
- No price list and wrong base price on product — order totals will be wrong.
- Commission rule not linked — commission report shows zero.

---

## Related guides

- [Sales orders](07-sales.md)
- [Accounting — invoices](09-accounting.md)
