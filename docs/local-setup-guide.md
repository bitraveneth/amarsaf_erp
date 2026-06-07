# Saf ERP — Local Setup Guide (Windows)

Use this guide to run the full ERP on your machine for demo, UAT, and client presentation.

## What you need

| Requirement | Version | Notes |
|---|---|---|
| PHP | 8.1+ | Required by Laravel 10 |
| Composer | 2.x | PHP dependency manager |
| MySQL | 8.x | Primary database |
| Node.js | 18+ | Frontend build (you already have this) |
| npm | 9+ | Bundles Tailwind/Vite assets |

**Easiest Windows options**

1. **Laragon** (recommended): PHP + MySQL + Apache in one installer  
   https://laragon.org/download/
2. **Docker Desktop + Laravel Sail**: if you prefer containers  
   Start Docker Desktop first, then use Sail commands below.

The project is cloned at:

`C:\Users\Alex\Projects\Saf_Erp`

---

## Option A — Laragon (fastest on Windows)

### 1. Install Laragon

1. Download and install Laragon (Full).
2. Start Laragon → **Start All**.
3. Add Laragon PHP to PATH if needed (Laragon menu → PHP → Version → add to path).

### 2. Create database

Open HeidiSQL (bundled with Laragon) or MySQL CLI:

```sql
CREATE DATABASE saferp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configure environment

In PowerShell:

```powershell
cd C:\Users\Alex\Projects\Saf_Erp
copy .env.example .env
```

Edit `.env`:

```env
APP_NAME="Saf ERP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://saf-erp.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saferp
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install and bootstrap

```powershell
composer install
php artisan key:generate
npm ci
npm run build
php artisan migrate --seed
php artisan storage:link
```

### 5. Point Laragon to the project

Either:

- Copy/symlink project into `C:\laragon\www\Saf_Erp`, or
- Add a virtual host in Laragon for `C:\Users\Alex\Projects\Saf_Erp\public`

Then open: **http://saf-erp.test/admin** (or Laragon auto URL)

### 6. Demo login accounts

Default password for all seeded users: **`password`**

| Role | Email |
|---|---|
| Super Admin | super@saferpv.local |
| Admin | admin@saferpv.local |
| Accounts Officer | accounts@saferpv.local |
| Purchase Executive | purchase@saferpv.local |
| Warehouse Officer | warehouse@saferpv.local |
| Production Officer | production@saferpv.local |
| Sales Officer | employee@saferpv.local |
| Delivery Coordinator | sales.manager@saferpv.local |
| QC Officer | qc@saferpv.local |

For production seeding, set `SEED_USER_PASSWORD` in `.env` before running `db:seed`.

---

## Option B — Docker Desktop + Laravel Sail

### 1. Start Docker Desktop

Docker must be running before any container command.

### 2. Install Sail (one-time)

```powershell
cd C:\Users\Alex\Projects\Saf_Erp
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php83-composer:latest bash -c "composer install && php artisan sail:install --with=mysql"
```

### 3. Start stack

```powershell
.\vendor\bin\sail up -d
.\vendor\bin\sail artisan key:generate
.\vendor\bin\sail npm ci
.\vendor\bin\sail npm run build
.\vendor\bin\sail artisan migrate --seed
```

Open: **http://localhost/admin**

---

## Option C — Built-in PHP server (quick test only)

After Laragon/PHP is installed:

```powershell
cd C:\Users\Alex\Projects\Saf_Erp
php artisan serve
```

Open: **http://127.0.0.1:8000/admin**

Use this for quick checks; Laragon or Sail is better for full demo stability.

---

## Post-install verification

Run the release regression tests:

```powershell
php artisan test --filter='test_goods_receipt_posts_stock_entry_and_goods_receipt_movement|test_production_confirm_posts_consumption_and_output_movements|test_order_reservation_and_delivery_post_stock_movements|test_customer_return_reuses_batched_available_entry_and_logs_movement|test_stock_transfer_requires_destination_rules_and_preserves_location'
```

Smoke checks in the UI:

| Screen | URL |
|---|---|
| Admin dashboard | `/admin` |
| Help / configuration | `/admin/help` |
| Profit & Loss | `/admin/reports/pl` |
| Balance Sheet | `/admin/reports/bs` |
| VAT report | `/admin/reports/vat` |
| Finance | `/admin/finance` |
| Production | `/admin/production` |
| Orders | `/admin/orders` |

---

## Full ERP demo walkthrough (recommended order)

Use seeded data first, then repeat live for the client:

1. **Master data** — Products, materials, BOM, agents, warehouses  
2. **Procurement** — PO → GRN → supplier bill  
3. **Production** — production run → QC → confirm stock  
4. **Sales** — agent order → stock reserve  
5. **Delivery** — dispatch → POD → mark delivered  
6. **Finance** — invoice from delivered order → receipt → bank reco  
7. **Reports** — P&L, balance sheet, VAT, agent performance  

Detailed step tables: `docs/client-user-guide.md`  
End-to-end cycle: `docs/system-cycle.md`

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `php` not recognized | Install Laragon or add PHP to PATH |
| Docker pipe error | Start Docker Desktop and retry |
| 500 on first load | Run `php artisan key:generate` and check `.env` DB settings |
| Blank CSS | Run `npm run build` |
| Login works but menus missing | Log in as `super@saferpv.local` |
| No demo data | Run `php artisan migrate:fresh --seed` (destroys local DB) |

---

## Scheduler (optional for notifications)

For live-like alert behaviour, add a Windows Task Scheduler job or cron:

```bash
* * * * * cd /path/to/Saf_Erp && php artisan schedule:run
```

This runs `erp:notifications:sync` for ERP alerts.
