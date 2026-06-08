# How the whole Saf ERP system works

> **Who this is for:** Owners, managers, trainers, and anyone new to the system  
> **Read this first** — then open the module guide for the area you work in every day.

---

## One sentence summary

Saf ERP connects **buying materials → making products → storing stock → selling to agents → delivering → invoicing and collecting money** in one place, with every step tracked.

---

## The big picture

```mermaid
flowchart TB
    subgraph setup [Phase 0 — Setup once]
        MD[Master data<br/>Products, agents, suppliers, warehouses]
    end

    subgraph factory [Phase 1 — Factory side]
        PO[Purchase order] --> GRN[Goods receipt GRN]
        GRN --> RM[Raw material stock]
        RM --> BOM[BOM recipe]
        BOM --> PROD[Production run]
        PROD --> QC[QC approval]
        QC --> FG[Finished goods stock]
    end

    subgraph market [Phase 2 — Market side]
        SO[Sales order] --> PICK[Pick and pack]
        PICK --> DEL[Delivery and POD]
        DEL --> INV[Customer invoice]
        INV --> PAY[Receipt / collection]
    end

    subgraph back [Phase 3 — Back office]
        EXP[Expenses and payroll]
        RPT[Reports P and L, stock, aging]
    end

    setup --> factory
    setup --> market
    FG --> SO
    factory --> back
    market --> back
```

---

## Two entry points — same system

Most businesses run two cycles that meet in the **warehouse**:

| Entry point | Trigger | Path through the system |
|---|---|---|
| **Make stock first** | Low raw material or production plan | Procurement → Production → FG stock → Sales |
| **Sell first** | Agent places order | Sales order → check stock → maybe trigger production/procurement |

```mermaid
flowchart LR
    A[Low RM stock] --> B[Buy via PO]
    B --> C[Receive GRN]
    C --> D[Produce]
    D --> E[FG ready]
    E --> F[Agent order]
    F --> G[Deliver]
    G --> H[Invoice]

    X[Agent order] --> Y{Stock OK?}
    Y -->|Yes| G
    Y -->|No| B
```

---

## Module map — what each area does

| # | Module | What it handles | Main output |
|---|---|---|---|
| 01 | Products & materials | Catalog of what you buy and sell | SKUs ready for BOM and sales |
| 02 | Agents & pricing | Who you sell to, commissions, price lists | Agent can place orders |
| 03 | Procurement | PO, GRN, supplier bills | Materials in stock, payables tracked |
| 04 | Warehouses & routes | Where stock lives, vehicles, delivery zones | Stock locations and dispatch plan |
| 05 | Manufacturing | BOM, batches, production, QC | Finished goods with batch traceability |
| 06 | Inventory | Stock levels, transfers, adjustments, MRP | Accurate quantities everywhere |
| 07 | Sales | Agent orders, targets, returns, gifts | Confirmed orders ready to ship |
| 08 | Delivery & POD | Dispatch, proof of delivery, exceptions | Goods delivered, stock consumed |
| 09 | Accounting | Invoices, receipts, expenses, payroll | Ledger and receivables updated |
| 10 | Reports | P&L, stock valuation, aging, production | Management decisions |

Each module has its **own guide** in `docs/modules/` with step-by-step instructions and flowcharts.

---

## Standard document flow (paper trail)

Every important action creates a **document** you can trace:

```mermaid
flowchart TD
    PO[PO-20260607-001] --> GRN[GRN-0042]
    GRN --> STK1[Material stock +]
    STK1 --> PR[PO production order]
    PR --> BAT[Batch lot code]
    BAT --> FG[FG stock +]
    FG --> SO[Sales order]
    SO --> DEL[Delivery note]
    DEL --> INV[Invoice]
    INV --> RCT[Receipt]
```

If a customer asks *“where did this carton come from?”* you can go **Invoice → Delivery → Sales order → Batch → Production run → GRN → PO**.

---

## Who does what (typical team)

```mermaid
flowchart LR
    subgraph purchase [Purchase team]
        P1[Create PO]
        P2[Follow GRN]
    end
    subgraph wh [Warehouse]
        W1[Receive GRN]
        W2[Confirm production stock]
        W3[Transfer and pick]
    end
    subgraph prod [Production]
        PR1[BOM and runs]
        PR2[QC]
    end
    subgraph sales [Sales]
        S1[Agent orders]
    end
    subgraph del [Delivery]
        D1[Dispatch POD]
    end
    subgraph acc [Accounts]
        A1[Invoice and receipt]
    end

    P1 --> W1
    W1 --> PR1
    PR2 --> W2
    W2 --> S1
    S1 --> W3
    W3 --> D1
    D1 --> A1
```

| Role | Daily focus | Module guides to read |
|---|---|---|
| Admin | Master data, users, permissions | 01, 02, 04, Control sections |
| Purchase executive | PO and supplier follow-up | 03 |
| Production officer | Runs and batches | 05 |
| QC officer | Approve or reject batches | 05 |
| Warehouse officer | GRN, stock confirm, transfers, picking | 03, 05, 06, 08 |
| Sales officer | Agent orders | 07 |
| Delivery coordinator | Routes, POD, exceptions | 08 |
| Accounts officer | Invoices, bills, expenses | 09 |

---

## Setup order (first time only)

Do these **before** daily operations:

```mermaid
flowchart TD
    A[1. Materials] --> B[2. Products]
    B --> C[3. Tax and packaging]
    C --> D[4. Suppliers]
    D --> E[5. Agents and pricing]
    E --> F[6. Warehouses and routes]
    F --> G[7. BOMs]
    G --> H[8. Users and roles]
    H --> I[Ready for live operations]
```

Skipping a step causes errors later (e.g. sales order without product, production without BOM).

---

## Daily rhythm (all teams)

| Time | Action | Where |
|---|---|---|
| Start of day | Check dashboard alerts and pending counts | Home dashboard |
| Morning | Clear GRN backlog, production QC, pending stock confirms | Procurement, Manufacturing |
| Midday | Process new sales orders, check stock | Sales, Inventory |
| Afternoon | Dispatch and update POD | Delivery |
| End of day | Post receipts, review open invoices | Accounting |
| Week end | Stock audit sample, open PO review | Inventory, Procurement |
| Month end | P&L, aging, payroll | Reports, Accounting |

---

## How stock moves (simple rules)

| Event | Material stock | Finished goods stock |
|---|---|---|
| GRN posted (approved QC) | **Increases** | — |
| Production stock confirmed | **Decreases** (BOM consumption) | **Increases** |
| Sales order confirmed | Reserved (not yet out) | Reserved |
| Delivery completed | — | **Decreases** |
| Stock transfer | Moves between warehouses | Moves between warehouses |
| Write-off / audit adjustment | Up or down per count | Up or down per count |

---

## Money flow (simple rules)

| Event | Accounts impact |
|---|---|
| Supplier bill posted | You owe supplier (payable) |
| Customer invoice from delivery | Agent owes you (receivable) |
| Receipt from agent | Receivable goes down, bank up |
| Expense posted | Expense up, bank or payable |
| Production completed | Cost of goods captured for P&L |

---

## When something goes wrong

| Problem | Usually means | Check module |
|---|---|---|
| Cannot confirm sales order | Not enough FG stock | 06 Inventory, 05 Manufacturing |
| Cannot confirm production stock | QC not done or no RM stock | 05 Manufacturing, 03 Procurement |
| GRN did not add stock | QC line not approved | 03 Procurement |
| No invoice for order | Delivery not marked delivered | 08 Delivery |
| Wrong VAT on invoice | Product tax class wrong | 01 Products |

See **Common problems** in `client-user-guide.md` for a longer list.

---

## Next steps

1. Read the **module guide** for your role (see table in `client-user-guide.md`).
2. Walk through **one full cycle** on a test order: PO → GRN → production → sales → delivery → invoice.
3. Use **Reports dashboard** weekly to verify numbers match reality.

---

## Related files

| Document | Purpose |
|---|---|
| `client-user-guide.md` | Index, roles, status codes, checklists |
| `modules/01` … `modules/10` | Detailed per-module manuals |
| `system-cycle.md` | Extended cycle notes for implementers |
