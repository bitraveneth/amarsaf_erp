# V4 accounting — deploy and update

Release **4.0.0** is the accounting and reports polish: payment methods, clearer dashboards, ledger gaps, and redesigned report pages.

Branch: `v4`.

## Which host

| Host | What to do |
|---|---|
| `demo.amarsaf.com` | Deploy this branch. Prefer a full reseed so payment methods and menu tidy migrations land cleanly. |
| `erp.amarsaf.com` | Deploy with `migrate --force` only. Do **not** run `migrate:fresh`. |

Keep demo and live on different folders and different MySQL databases.

## Before you start

1. SSH into the VPS.
2. Confirm the app folder (`pwd`) and that `.env` points at the intended database (`grep ^DB_DATABASE .env`).
3. Backup:

```bash
php artisan down --retry=60 || true
mkdir -p storage/app/backups/database
php artisan db:backup || mysqldump -u"$DB_USER" -p "$DB_NAME" > "storage/app/backups/database/before-v4-$(date +%F-%H%M).sql"
```

## Deploy (leave `.env` alone)

```bash
cd /path/to/your-app
git fetch --prune origin
git checkout v4
git pull --ff-only origin v4

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build
```

If the footer still shows 3.0.0, set `APP_VERSION=4.0.0` in `.env`. Nothing else in `.env` needs editing for this release.

## Demo: full reseed (demo only)

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

First login after seed:

- Email: `super@saferpv.local`
- Password: `password`

## Live company: migrate only

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Never run `migrate:fresh` or `erp:reset-for-production` on the live company database unless you have planned a wipe.

## Check after it is up

- Accounting → **Payment methods** (not “Banks & payment methods”).
- Expenses → **Category mapping** (not a root Accounting menu item).
- Accounting dashboard shows the Quick tips strip and clearer KPI labels.
- Reports → Delivery performance, Sales register, Commissions, Outstanding invoices, Inventory valuation.
- `storage/logs/laravel.log` has no new HTTP 500 lines.
