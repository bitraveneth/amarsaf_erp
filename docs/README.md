# Saf ERP — Client documentation system

This folder holds **client-facing manuals** in plain English. Hand these to customers and train staff from them.

---

## Two layers (important)

| Layer | File(s) | Purpose |
|---|---|---|
| **Whole system** | `00-how-the-system-works.md` | How every module connects — read first |
| **Each module** | `modules/01` … `modules/10` | Step-by-step for one area of the business |

Plus **`client-user-guide.md`** = index, roles, checklists, links to all guides.

---

## Module guides (complete set)

| # | File | ERP menu area |
|---|---|---|
| 00 | `00-how-the-system-works.md` | **Entire system** |
| 01 | `modules/01-products-and-materials.md` | Control → Products / Materials |
| 02 | `modules/02-agents-and-pricing.md` | Control → Agents |
| 03 | `modules/03-procurement.md` | Control → Suppliers, PO, GRN |
| 04 | `modules/04-warehouses-and-routes.md` | Control → Warehouses |
| 05 | `modules/05-manufacturing.md` | Manufacturing |
| 06 | `modules/06-inventory.md` | Inventory |
| 07 | `modules/07-sales.md` | Sales |
| 08 | `modules/08-delivery-and-pod.md` | Inventory → Deliveries, picking |
| 09 | `modules/09-accounting.md` | Accounting |
| 10 | `modules/10-reports.md` | Reports & analytics |

---

## In-app viewing

**System settings → Client manual** loads every file listed in `manual-manifest.php` in order.

---

## Adding or updating a guide

1. Copy `_module-template.md` for new modules.
2. Use simple English, menu paths, and mermaid flowcharts.
3. Register the file in `manual-manifest.php`.
4. Add a row to the index table in `client-user-guide.md`.

---

## Other docs (internal / implementers)

| File | Audience |
|---|---|
| `system-cycle.md` | Implementers — extended cycle notes |
| `configuration-guide.md` | Technical setup |
| `local-setup-guide.md` | Developers |

---

## Writing rules

- Write for a **warehouse supervisor**, not a programmer.
- Every process with 4+ steps gets a **mermaid flowchart**.
- Include **who** does each step and **what changes** in the system after save.
