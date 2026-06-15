# Design consistency audit

Baseline captured on branch `v2.1-design` (June 2026). Re-run after each migration wave.

## Summary

| Metric | Count |
|--------|------:|
| Admin views with anti-patterns (`text-2xl`, `text-3xl`, `text-title-sm`) | 132 files |
| Total anti-pattern occurrences | 356 |
| Views using `x-admin.page-header` or `x-report.page` | 60 files |

**Anti-patterns:** raw Tailwind heading sizes instead of `erp-h1`, `erp-dash-h1`, `erp-metric-value`, etc.

## Migration waves (priority order)

### Wave 1 — Finance reports (mostly done)

Most reports use `x-report.page`. Remaining inline styles: `finance/show`, `finance/index` (KPI cards), `finance/reconciliation`, `finance/accounts`.

### Wave 2 — Core operations

| File | Hits | Status |
|------|-----:|--------|
| `warehouses/index` | 5 | Header migrated |
| `production/index` | 5 | Header migrated |
| `bills/index` | 5 | Pending |
| `inventory/materials` | 6 | Pending |
| `stock/movements` | 6 | Pending |
| `returns/customer/index` | 6 | Pending |
| `deliveries/*` | 4–5 each | Pending |

### Wave 3 — HR / employees

| File | Hits | Status |
|------|-----:|--------|
| `employees/index` | 6 → ~0 | Migrated (header + KPI + table shell) |
| `employees/leaves/index` | 7 → ~5 | Header migrated |
| `employees/leaves/all` | 7 | Pending |
| `employees/badges/index` | 7 | Pending |
| `employees/contracts/index` | 7 | Pending |
| `employees/equipment/index` | 7 | Pending |
| `employees/allowances/index` | 7 | Pending |
| `employees/locations/*` | 6–7 | Pending |
| `finance/salary_distributions/index` | 7 | Pending |

### Wave 4 — Legacy / low priority

- `help.blade.php` (8)
- Ecommerce demo components under `resources/views/components/ecommerce/`

## Top offenders (remaining)

```
finance/show.blade.php          8
help.blade.php                  8
employees/allowances/index      7
employees/equipment/index       7
employees/leaves/all            7
employees/locations/all         7
finance/salary_distributions    7
employees/badges/index          7
employees/contracts/index       7
```

## Re-run audit

```powershell
rg -c "text-2xl|text-3xl|text-title-sm" resources/views/admin --glob "*.blade.php" | Sort-Object { [int]($_ -split ':')[1] } -Descending
rg -l "x-admin\.page-header|x-report\.page" resources/views/admin --glob "*.blade.php" | Measure-Object
```

## Rollback

```bash
git checkout v2-pre-design-2026-06-15
# or stay on v2.1-design and revert individual commits
```

Reference: [erp-design-guide.md](erp-design-guide.md)
