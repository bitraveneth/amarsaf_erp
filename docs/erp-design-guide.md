# Saf ERP — Design Guide

Single source of truth for UI typography, spacing, and component patterns.  
**Implementation:** `resources/css/app.css` (search for `ERP Typography`).  
**Font family:** Outfit (`--font-outfit`).

---

## 1. Typography principles

1. Use **semantic classes** (`erp-h1`, `erp-metric-value`, `erp-table-num`) — not arbitrary Tailwind sizes in views.
2. **Weight 700 (bold)** for all headings (H1–H3) and KPI numbers.
3. **Weight 600 (semibold)** for table headers, form labels, and emphasized table numbers.
4. **Weight 400 (normal)** for body copy and default table cells.
5. **Dashboard KPIs** use a larger number scale than **table numbers**.
6. Always use `tabular-nums` on currency, counts, and percentages.

---

## 2. Type scale (reference)

| Token | Class | Size | Weight | Line height | Use |
|-------|-------|------|--------|-------------|-----|
| H1 — module page | `erp-h1` | 20px → 24px (md+) | **700** | 1.25 | Orders, Agents, Products list pages |
| H1 — dashboard | `erp-dash-h1` | 24px → 30px (md+) | **700** | 1.2 | Main dashboard, Sales/Mfg/Accounting dashboards |
| H2 — section | `erp-h2` | 16px | **700** | 1.35 | Panel titles, “Top products”, report sections |
| H3 — card / widget | `erp-h3` | 14px | **700** | 1.4 | Card headers, widget titles, table card titles |
| Eyebrow | `erp-eyebrow` | 11px | 600 | 1.35 | “ACTIVITY”, “SALES PERFORMANCE”, period badges |
| Body | `erp-body` | 14px | 400 | 1.5 | Paragraphs, default table cells, form inputs |
| Body strong | `erp-body-strong` | 14px | 600 | 1.5 | Agent name in table, emphasized row text |
| Caption | `erp-caption` | 12px | 400 | 1.45 | Subtitles, hints, secondary table meta |
| Label | `erp-label` | 12px | 600 | 1.35 | Form field labels (uppercase tracking) |

---

## 3. Numbers & metrics

### Dashboard / KPI tiles (large)

Used on: main dashboard snapshot, manufacturing summary cards, stat cards, sales target side stats.

| Token | Class | Size | Weight | Notes |
|-------|-------|------|--------|-------|
| KPI label | `erp-metric-label` | 11px | 600 | Uppercase, tracking 0.08em, gray-500 |
| KPI value | `erp-metric-value` | 24px | **700** | `tabular-nums`, tight tracking |
| KPI hint | `erp-metric-hint` | 12px | 400 | Below value, gray-500 |

**Do not** use `text-3xl`, `text-title-sm`, or raw `text-2xl` on KPI cards — use `erp-metric-value`.

### Tables & lists (normal)

Used on: all `erp-table`, recent orders, agent lists, finance grids.

| Token | Class | Size | Weight | Notes |
|-------|-------|------|--------|-------|
| Table header | `erp-table-head` | 11px | 600 | Uppercase, tracking 0.06em |
| Table cell | `erp-table-cell` | 14px | 400 | Default text |
| Table number | `erp-table-num` | 14px | 600 | `tabular-nums`; add `is-right` on column |
| Table link/id | `erp-table-link` | 14px | 600 | Order #, invoice # (brand on hover) |

---

## 4. Heading mapping (HTML → class)

| Element | Preferred class | When |
|---------|-----------------|------|
| `<h1>` | `erp-h1` | Standard module index/show header |
| `<h1>` | `erp-dash-h1` | Dashboard home & domain dashboards only |
| `<h2>` | `erp-h2` | Section within a page |
| `<h3>` | `erp-h3` | Card title, nested widget |

Avoid inline `text-2xl font-semibold` on headings. The shell does **not** auto-style bare `<h1>` tags.

---

## 5. Component mapping

| UI area | Title | Labels | Values / numbers |
|---------|-------|--------|------------------|
| Module page header | `erp-h1` + `erp-caption` | — | — |
| Dashboard header | `erp-dash-h1` + `erp-caption` | `erp-eyebrow` for badge | — |
| Snapshot KPI tile | — | `erp-metric-label` | `erp-metric-value` |
| Stat card (`x-admin.stat-card`) | — | `erp-metric-label` | `erp-metric-value` |
| Manufacturing metric card | — | `erp-metric-label` | `erp-metric-value` |
| Performance widget | `erp-h3` | `erp-caption` | chart labels = `erp-caption` |
| Data table | `erp-h3` (card title) | `erp-table-head` | `erp-table-num` / `erp-table-cell` |
| Form | — | `erp-label` | inputs = `erp-body` (14px) |

---

## 6. Export center (recommended pattern)

Per-page export buttons are **not** used. All exports live under the menu:

- **Reports & analytics → Data export → Export center**
- URL: `/admin/export-center`

Users pick **All records** or **Custom dates** with calendar From/To fields. Quick-fill chips (Today, Last 3 months, etc.) populate the calendar. Applies to dated modules; master lists export in full.

Implementation: `ExportDateRange`, `ModuleExportRegistry`, `ExportCenterController`.

---

## 7. Smart KPI numbers

Use `<x-admin.metric-value :value="…" />` on dashboards and stat cards.

Font size scales by **display length** (after removing spaces):

| Length | Class | Size |
|--------|-------|------|
| 1–2 chars | `--2xl` | 36px |
| 3–4 | `--xl` | 32px |
| 5–7 | `--lg` | 24px (default) |
| 8–10 | `--md` | 20px |
| 11–14 | `--sm` | 18px |
| 15+ | `--xs` | 16px |

Short counts like agent totals render large; long currency strings shrink so they stay on one line.

---

## 8. Colors (typography)

| Role | Light | Dark |
|------|-------|------|
| Primary text | gray-900 | white |
| Secondary / caption | gray-500 | gray-400 |
| Muted label | gray-500 | gray-400 |
| Link / accent | brand-600 | brand-400 |
| Success metric | success-600 | success-500 |
| Warning metric | orange-600 | orange-400 |
| Error metric | error-600 | error-500 |

---

## 9. Do / Don’t

**Do**
- Use `erp-metric-value` for dashboard KPI numbers.
- Use `erp-table-num` for amounts and counts in tables.
- Use `erp-h1` on module pages and `erp-dash-h1` on dashboards.
- Keep export in the **Data export center** menu — not on every list page.

**Don’t**
- Mix `text-3xl`, `text-title-sm`, and `text-2xl` for the same KPI pattern.
- Use `font-semibold` (600) for page titles — use **700**.
- Put export buttons on individual list pages.
- Style headings with one-off Tailwind in Blade unless documented here.

---

## 10. CSS token reference

Defined in `app.css` `@theme` block:

```
--erp-size-h1: 1.25rem        (20px)
--erp-size-h1-lg: 1.5rem      (24px)
--erp-size-dash-h1: 1.5rem    (24px)
--erp-size-dash-h1-lg: 1.875rem (30px)
--erp-size-h2: 1rem           (16px)
--erp-size-h3: 0.875rem       (14px)
--erp-size-body: 0.875rem      (14px)
--erp-size-caption: 0.75rem   (12px)
--erp-size-eyebrow: 0.6875rem (11px)
--erp-size-metric: 1.5rem     (24px)
--erp-size-table-head: 0.6875rem (11px)
--erp-weight-heading: 700
--erp-weight-label: 600
--erp-weight-body: 400
```

---

## 11. Blade components

### Module pages — `<x-admin.page-header>`

```blade
<x-admin.page-header title="Agent Master" subtitle="List, search, and manage your agent network.">
    <x-slot:actions>
        {{-- buttons, search, etc. --}}
    </x-slot:actions>
</x-admin.page-header>
```

Renders `erp-h1` + `erp-caption`. Optional `icon="orders"` uses `x-admin.page-icon`.

### Dashboard pages — `variant="dashboard"`

```blade
<x-admin.page-header
    variant="dashboard"
    eyebrow="Accounting"
    title="Dashboard"
    subtitle="Period-based snapshot…"
    :period="'Period: ' . $periodLabel"
    icon="chart"
/>
```

Renders `erp-dash-h1` (larger than module H1).

### Main dashboard — `<x-dashboard.page-header>`

```blade
<x-dashboard.page-header
    title="Dashboard"
    subtitle="Sales, finance, production, and stock at a glance."
    :date="now()"
>
    <x-slot:actions>{{-- primary buttons --}}</x-slot:actions>
</x-dashboard.page-header>
```

**Date/time** sits top-right with actions — not under the page title.

### Domain dashboards — `<x-dashboard.hero>`

```blade
<x-dashboard.hero
    eyebrow="Sales"
    title="Sales dashboard"
    subtitle="Commercial performance across invoices, collections, agents, and products."
    :period="$periodLabel"
>
    <x-slot:actions>{{-- buttons --}}</x-slot:actions>
</x-dashboard.hero>
```

**Period badge** top-right; date filter lives in `<x-dashboard.period-filter>` below.

### Dashboard sections — `<x-dashboard.section-header>`

```blade
<x-dashboard.section-header
    title="Sales performance"
    description="Monthly order volume, agent targets, and the last seven days of activity."
/>
```

One **H2 title** + optional **one-line description** — no eyebrow + title + paragraph stack.

### KPI cards

```blade
<div class="erp-metric-card">
    <p class="erp-metric-card__label">Net sales</p>
    <p class="erp-metric-card__value">{{ $amount }}</p>
    <p class="erp-metric-card__hint">Invoice basis</p>
</div>
```

Or use `<x-admin.stat-card label="…" value="…" hint="…" />`.

### Data tables

Wrap with `erp-table-card` + `erp-table-wrap`, use `class="erp-table"` on `<table>`.  
Headers/cells inherit typography — no inline `text-xs font-semibold uppercase`.

### Forms (unified field system)

Use shared classes and Blade wrappers on every module form:

| Piece | Class / component |
|-------|-------------------|
| Page shell | `erp-form-page` + `erp-form-card` |
| Section | `<x-admin.form.section title="…">` |
| Field grid | `erp-form-grid` (2 columns on sm+) |
| Label | `erp-label` via `<x-admin.form.field>` |
| Text input | `erp-input` |
| Select | `erp-select` |
| Textarea | `erp-textarea` |
| Hint | `erp-form-hint` |
| Error | `erp-form-error` |
| Footer actions | `erp-form-actions` + `erp-btn-primary` |

Do not use one-off `px-4 py-2.5` input classes in new forms — use `erp-input` / `erp-select`.

---

## 13. Brand colors

Official Saf palette (CSS tokens in `resources/css/app.css` `@theme`):

| Role | Token / hex | Usage |
|------|-------------|--------|
| Primary purple | `brand-500` · `#5F4BFF` | Buttons, links, focus rings, charts |
| Primary dark | `brand-600` · `#4D39E6` | Hover, secondary accent |
| Deep black | `#0E0F14` | Dark canvas, primary text (light mode) |
| Light gray | `#F3F4F6` | Light canvas, dark-mode text |
| Soft lavender | `#E9E4FF` | Tinted surfaces, highlights |
| Soft blue | `#D6E6FF` | Info panels, calendar tints |
| Cool gray | `gray-400` · `#94A3B8` | Muted labels, borders |
| Fresh green | `success-500` · `#22B573` | Success, positive KPIs |
| Urgency orange | `warning-500` · `#FF8A24` | Warnings, due-soon states |
| Error red | `error-500` · `#EF4444` | Errors, overdue, destructive |

Admin → Settings → Colors can override primary/secondary and text colors. Use **Reset Saf brand palette** to restore defaults.

Do not introduce new hex values in Blade or JS — use Tailwind tokens (`text-brand-500`, `bg-success-50`, etc.).

---

## 12. Migration checklist for new pages

1. Page title → `<x-admin.page-header>` or `erp-h1` / `erp-dash-h1`
2. Subtitle → `erp-caption`
3. KPI cards → `erp-metric-card` or `x-admin.stat-card` with `<x-admin.metric-value />`
4. Tables → `erp-table` with `erp-table-num` for amounts/counts
5. No custom font sizes unless added to this guide first

---

## 14. Naming & vocabulary

**Locale:** British English (`cancelled`, `labour` where appropriate). **Casing:** sentence case in source strings; table headers may uppercase via CSS.

| Context | Rule | Example |
|---------|------|---------|
| Menu & page H1 | Sentence case | `Purchase orders`, `Sales dashboard` |
| KPI labels | Sentence case | `Net sales`, `Gross profit` |
| Buttons (repeat) | Abbreviation OK | `New PO`, `Save PO` |
| First mention on form | Spell out | `Purchase order`, then `PO` in hints |
| Status slugs | Humanise | `partial_received` → `Partial received` via `UiLabels::status()` |

**Canonical abbreviations:** PO, GRN, BOM, SKU, POD, COGS, VAT, MRP, QC.

**Terms (use consistently):**

- Selling partners → **Agents** (not “dealers”)
- Order list → **Sales orders**
- Revenue KPI → **Net sales**
- Cash report → **Cash flow** (two words)
- Export UI → **Export center**
- VAT screen → **VAT report** (not “tax report”)

Shared strings live in `lang/en/ui.php`. Use `__('ui.terms.net_sales')` or `App\Helpers\UiLabels::term('net_sales')` in new code.

---

## 15. Page & section header hierarchy

How enterprise ERPs (SAP Fiori, Odoo, Dynamics) structure headers — and what Saf ERP uses.

### Three levels only

| Level | HTML | Max text lines | Typical content |
|-------|------|----------------|-----------------|
| **Page** | `<h1>` | **2** (title + subtitle) | "Dashboard", "Agents", "Purchase orders" |
| **Section** | `<h2>` | **2** (title + description) | "Today at a glance", "Sales performance" |
| **Widget / card** | `<h3>` | **1** (title only) | "Top agents", chart panel name |

**Rule:** Never stack eyebrow + title + subtitle + description on the same block. Pick **at most two** lines.

### What each line is for

| Element | Class | When to use | When to skip |
|---------|-------|-------------|--------------|
| **Eyebrow** | `erp-eyebrow` | Domain dashboards only ("Sales", "Accounting") | Main dashboard, module lists, sections |
| **Title** | `erp-h1` / `erp-dash-h1` / `dash-section-title` | Always | — |
| **Subtitle** | `erp-page-subtitle` / `erp-caption` | Module page needs context | Title is self-explanatory |
| **Description** | `dash-section-desc` | Section explains non-obvious charts | Widget title is enough |
| **Period / date meta** | `dash-page-meta`, `dash-period-badge` | Top-right of page header | Not under the title stack |

### Module list pages

```
Agents                          [Search] [Add agent]
List, search, and manage your agent network.
```

### Main operations dashboard

```
Dashboard                                    Sunday, 7 June 2026
Sales, finance, production, and stock…       [New order] [Finance]
```

- No month badge in the page header — month context lives in KPI tiles and chart filters.
- Today's date top-right, above actions.

### Domain dashboards (Sales, Accounting, Manufacturing)

Use `<x-dashboard.hero>` with eyebrow + title + subtitle on the left, period badge + actions on the right. Period filter bar sits below.

### Section blocks

Use `<x-dashboard.section-header>` — one H2 + optional one-line description, left-aligned. No section eyebrows.

### Do / don't

**Do:** Put date, period, and actions in the header's right column. Keep subtitles to one line (~80 chars).

**Don't:** Put today's date under the H1. Repeat period in badge + subtitle + section eyebrow. Float section descriptions to the right.
