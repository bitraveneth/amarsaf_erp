# V3 invoice — deploy and reseed demo.amarsaf.com

Release **3.0.0** is the V3 commercial document sheet. Invoices, purchase orders, sales orders, delivery challans (customer, office, and transport), goods receipts, production orders, and the other printable documents share one purple A4 layout. Screen preview, browser print, and PDF download use that same sheet.

Branch: `v3-invoice`.

## Which host to reseed

| Host | What to do |
|---|---|
| `demo.amarsaf.com` | Deploy this branch and **fully reseed**. `migrate:fresh` deletes every row in that database. |
| `erp.amarsaf.com` | Do **not** run `migrate:fresh` here. That database is the live company. |

The two hosts must stay on different folders and different MySQL databases.

## Before you start

1. SSH into the VPS.
2. Confirm you are in the **demo** app directory, not the live ERP directory. `pwd` and `grep ^DB_DATABASE .env` should name the demo database.
3. Take a backup anyway:

```bash
php artisan down --retry=60 || true
mkdir -p storage/app/backups/database
php artisan db:backup || mysqldump -u"$DB_USER" -p "$DB_NAME" > "storage/app/backups/database/demo-before-v3-$(date +%F-%H%M).sql"
```

If `db:backup` is not registered, the `mysqldump` line is enough. Use the demo database name from `.env`.

## Deploy

```bash
cd /path/to/demo.amarsaf.com
git fetch --prune origin
git checkout v3-invoice
git pull --ff-only origin v3-invoice

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build
```

In the demo `.env`, set:

```env
APP_ENV=production
APP_DEBUG=false
APP_VERSION=3.0.0
SEED_SALES_DEMO=true
```

`SEED_SALES_DEMO=true` loads the one-year sample company (orders, production, payroll, logistics) and the ten printable samples of each document. `SEED_SALES_DEMO=false` still seeds masters and the printable samples, without that one-year timeline.

## Full reseed (demo only)

This drops every table in the database named by `DB_DATABASE` and loads the seeders again.

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

First login after the seed:

- Email: `super@saferpv.local`
- Password: `password`

Change that password on the demo if the site is public.

## Check after it is up

- `/` login page shows the purple SAF logo.
- Finance → open an invoice. The sheet is the purple V3 layout, with totals on the right under Amount.
- Open a purchase order and a delivery challan. The challan has Customer, Office, and Transport copies.
- Download PDF from those pages and confirm the file opens.
- `storage/logs/laravel.log` has no new HTTP 500 lines.

## Live company database

On `erp.amarsaf.com`, deploy with `php artisan migrate --force` only. Never `migrate:fresh`, and do not run `erp:reset-for-production` unless you have planned a wipe and set `ALLOW_PRODUCTION_WIPE=true`.
