# ERP v2 Staging Launch Runbook

Use this checklist when deploying v2 to a staging/UAT environment for client sign-off.

## Pre-deploy

- [ ] Merge launch branch to staging
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm ci && npm run build`
- [ ] Copy `.env.staging` with `APP_DEBUG=false`, real mail/storage drivers
- [ ] Full backup of staging database (if replacing existing data)

## Deploy commands

```bash
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder   # fresh DB only
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Post-deploy smoke test (logged in as admin)

1. `/admin/dashboard` — charts load, no console errors
2. `/admin/reports-dashboard` — reports library expands
3. `/admin/reports/income-statement` — P&L renders
4. `/admin/reports/pl` — 301 redirect to income statement
5. Create one test order → invoice → verify AR aging
6. Export CSV from expense summary

## Automatic database backups

Add to server crontab (Linux) or Task Scheduler (Windows):

```bash
* * * * * cd /path/to/saf_erp && php artisan schedule:run >> /dev/null 2>&1
```

Default `.env` settings (recommended for production ERP):

```env
DB_BACKUP_SCHEDULE_ENABLED=true
DB_BACKUP_SCHEDULE=daily
DB_BACKUP_SCHEDULE_TIME=02:00
DB_BACKUP_RETENTION_DAYS=14
```

For staging/dev only, weekly is acceptable:

```env
DB_BACKUP_SCHEDULE=weekly
DB_BACKUP_SCHEDULE_DAY=0
```

Manual test: `php artisan erp:backup-database`

## UAT data entry (minimal)

After `ProductionSeeder` on a fresh DB:

1. Create real admin user (do not use `@saferpv.local` demo accounts)
2. One warehouse + locations
3. Two products with BOM
4. One agent + price list
5. One month of sample transactions for report validation

## Client sign-off

Use [uat-matrix.md](uat-matrix.md) module by module. Fix only P0/P1 bugs before production.

## Rollback

- Restore database backup
- Redeploy previous git tag (`pre-v2-baseline` or last stable release)
