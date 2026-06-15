# ERP v2 Production Launch Runbook

## Pre-launch (T-24h)

- [ ] Announce maintenance window to users
- [ ] Final production database backup (off-server)
- [ ] Verify `.env`: `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Confirm real admin accounts exist; disable demo `@saferpv.local` users

## Launch sequence

1. Enable maintenance mode: `php artisan down --secret="launch-token"`
2. Deploy application code
3. `composer install --no-dev --optimize-autoloader`
4. `npm ci && npm run build`
5. `php artisan migrate --force` (never `migrate:fresh` on live DB unless planned)
6. If fresh production DB: `php artisan db:seed --class=ProductionSeeder`
7. If existing DB with demo data: `php artisan erp:wipe-transactions --force` (staging only by default)
8. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
9. Smoke test 10 critical paths (see below)
10. `php artisan up`

## Critical smoke paths

1. Login with production admin
2. Dashboard loads
3. Create sales order
4. Post invoice
5. Record receipt
6. Income statement report
7. Trial balance
8. Production run (if manufacturing live day 1)
9. Export center CSV
10. Permissions: restricted role gets 403

## Business data entry order (post go-live)

1. Review chart of accounts (seeded)
2. Products, BOMs, packaging
3. Warehouses, locations, fleet/routes
4. Agents, price lists, commission rules
5. Suppliers, employees
6. Opening stock and opening balances
7. First real production run → first real invoice → verify reports

## Monitoring (48h)

- Watch `storage/logs/laravel.log` for 500 errors
- Confirm scheduled jobs run (`erp:notifications:sync`)
- Client support channel active for first-week issues

## Rollback

1. `php artisan down`
2. Restore database from backup
3. Redeploy previous release tag
4. `php artisan up`
