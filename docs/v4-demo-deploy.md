# Saf ERP — V4 / V4.1 demo & live release guide

Use this checklist for every deploy or reseed. Skipping the **heal** step is what causes the recurring HTTP 500 on `demo.amarsaf.com`.

| Release | Branch | Notes |
|---|---|---|
| 4.0.0 | `v4` | Accounting / reports polish |
| 4.1.0 | `v4.1` | Ledger reverse fix, AR/AP landscape PDF, report UX |

---

## 0. Golden rules (read first)

1. **Demo and live are different folders and different MySQL databases.** Never reseed live.
2. Deploying / reseeding as `root` breaks file ownership. Apache runs as `www-data`. Always heal after.
3. Never open the site in a browser until the heal checklist passes `curl -I` with **HTTP 200**.
4. Leave `.env` alone except optional `APP_VERSION=…`.

| Host | Path (typical) | Database action |
|---|---|---|
| `demo.amarsaf.com` | `/var/www/demo.amarsaf.com` | `migrate:fresh --seed` OK |
| `erp.amarsaf.com` | live app folder | `migrate --force` **only** — never fresh/seed |

---

## 1. Before you start

```bash
cd /var/www/demo.amarsaf.com   # or the live path — confirm first
pwd
grep -E '^(APP_URL|DB_DATABASE|APP_VERSION)=' .env
```

Confirm `DB_DATABASE` is the **demo** DB before any fresh/seed.

Backup (optional but recommended):

```bash
php artisan down --retry=60 || true
mkdir -p storage/app/backups/database
php artisan db:backup \
  || mysqldump -u"$DB_USER" -p "$DB_NAME" > "storage/app/backups/database/before-$(date +%F-%H%M).sql"
```

---

## 2. Pull the release code

```bash
cd /var/www/demo.amarsaf.com

git fetch --prune origin
git checkout v4.1
git pull --ff-only origin v4.1

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci
npm run build
```

Optional footer version:

```bash
# in .env
APP_VERSION=4.1.0
```

---

## 3. Database step

### Demo only — clean sandbox reseed

```bash
php artisan migrate:fresh --seed --force
php artisan storage:link
```

Login after seed:

- Email: `super@saferpv.local`
- Password: `password`

### Live company — migrate only

```bash
php artisan migrate --force
php artisan storage:link
```

**Never** run `migrate:fresh`, `--seed`, or `erp:reset-for-production` on live.

---

## 4. HEAL (mandatory after every root deploy / reseed)

This is the step that was missing. Do it **every time**, even if the site “looked fine” last week.

### 4a. Ownership & permissions

Root pulls leave some files as `root:root` mode `600`. PHP-FPM / Apache (`www-data`) then cannot read `config/database.php` → HTTP 500.

```bash
cd /var/www/demo.amarsaf.com

# App code + config readable by the web user
chown -R www-data:www-data /var/www/demo.amarsaf.com
find /var/www/demo.amarsaf.com -type d -exec chmod 755 {} \;
find /var/www/demo.amarsaf.com -type f -exec chmod 644 {} \;

# Writable runtime dirs
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

# Secrets stay tighter
chmod 640 .env
chown www-data:www-data .env
```

Quick proof (must show `www-data` and `-rw-r--r--`, not `root` / `-rw-------`):

```bash
ls -la config/database.php config/app.php .env
sudo -u www-data test -r config/database.php && echo CONFIG_READ_OK || echo CONFIG_READ_FAIL
sudo -u www-data test -r .env && echo ENV_READ_OK || echo ENV_READ_FAIL
```

### 4b. Rebuild Laravel bootstrap caches

Broken / empty `bootstrap/cache/{packages,services,config}.php` causes:

`Target class [view] does not exist`

Clear and rediscover **before** caching again:

```bash
cd /var/www/demo.amarsaf.com

rm -f bootstrap/cache/config.php \
      bootstrap/cache/packages.php \
      bootstrap/cache/services.php \
      bootstrap/cache/routes-v7.php \
      bootstrap/cache/routes.php

composer dump-autoload -o
sudo -u www-data php artisan package:discover --ansi
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan up
```

Prefer running artisan as `www-data` so new cache files are not owned by root again.

---

## 5. Verify before you leave SSH

```bash
curl -I https://demo.amarsaf.com
# expect: HTTP/2 200

sudo -u www-data php artisan about | head -30
tail -n 30 storage/logs/laravel.log
```

Smoke-check in the browser:

- Login works (`super@saferpv.local` / `password` on demo)
- Accounting dashboard loads
- Balance sheet + AR aging PDF open
- No fresh 500 lines in `storage/logs/laravel.log`

---

## 6. One-shot copy/paste (demo reseed + heal)

```bash
cd /var/www/demo.amarsaf.com
pwd
grep -E '^(APP_URL|DB_DATABASE)=' .env

php artisan down --retry=60 || true

git fetch --prune origin
git checkout v4.1
git pull --ff-only origin v4.1
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm ci && npm run build

php artisan migrate:fresh --seed --force
php artisan storage:link

# --- HEAL (do not skip) ---
chown -R www-data:www-data /var/www/demo.amarsaf.com
find /var/www/demo.amarsaf.com -type d -exec chmod 755 {} \;
find /var/www/demo.amarsaf.com -type f -exec chmod 644 {} \;
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
chmod 640 .env
chown www-data:www-data .env

rm -f bootstrap/cache/config.php bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/routes-v7.php bootstrap/cache/routes.php
composer dump-autoload -o
sudo -u www-data php artisan package:discover --ansi
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan up

curl -I https://demo.amarsaf.com
```

---

## 7. Troubleshooting — recurring HTTP 500

| Symptom / log line | Cause | Fix |
|---|---|---|
| `Failed opening required …/config/database.php` | File owned by `root` mode `600` | §4a ownership |
| `Target class [view] does not exist` | Broken `bootstrap/cache/packages.php` / `services.php` | §4b clear + `package:discover` |
| `Log [] channel is not defined` | Empty / bad `.env` or stale config cache | Check `LOG_CHANNEL=stack`, then §4b |
| Old error dated days ago in `tail` | Looking at stale log | `curl` the site once, then `tail` again |
| 500 only in browser, artisan OK | Web user can't read files artisan (root) can | Always test with `sudo -u www-data …` |

Get **today’s** error only:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://demo.amarsaf.com
tail -n 80 storage/logs/laravel.log
```

---

## 8. Why this kept happening

1. SSH deploy as **root** → git/composer write files as root.
2. `config/database.php` (and sometimes caches) become unreadable by `www-data`.
3. Or artisan `config:cache` / optimize as root writes broken bootstrap caches → `view` binding missing.
4. Browser shows a blank HTTP 500; without §4 heal it looks like “reseed broke the site.”

Reseed itself is fine. **Skipping heal after root deploy** is what breaks demo.
