# Amarsaf Webmail login branding — upload guide

This package updates **https://webmail.amarsaf.com/** login only.  
It is **Roundcube on the mail VPS**, not Saf ERP (`erp.amarsaf.com`).

Brand assets come from [amarsaf-website](https://github.com/bitraveneth/amarsaf-website) (SAF logo, purple `#6d2bd5`, Plus Jakarta Sans).

## What’s inside

```text
skins/elastic/templates/login.html      ← login layout + SAF logo
skins/elastic/styles/amarsaf-login.css  ← brand CSS
skins/elastic/images/saf-logo-white.svg
skins/elastic/images/saf-logomark.svg
skins/elastic/images/pattern-1-white.svg
install-on-server.sh
UPLOAD.md
```

## Option A — easiest (from your PC)

### 1) Copy the zip to the server

From your laptop (or any machine with the zip):

```bash
scp amarsaf-webmail-login.zip root@72.60.71.192:/root/
```

Or use Hostinger file manager / SFTP to upload `amarsaf-webmail-login.zip` into `/root/`.

### 2) On the server (SSH console)

```bash
cd /root
unzip -o amarsaf-webmail-login.zip -d amarsaf-webmail-login
cd amarsaf-webmail-login
chmod +x install-on-server.sh
bash install-on-server.sh
```

### 3) Check

Open https://webmail.amarsaf.com/ and hard-refresh (`Ctrl+Shift+R`).

You should see the **SAF logo**, purple panel, and “Welcome back”.

---

## Option B — manual copy

```bash
# find Roundcube root
grep -R webmail.amarsaf.com /etc/apache2/sites-enabled -n

# example if root is /var/www/webmail-new
ROOT=/var/www/webmail-new

cp skins/elastic/templates/login.html   $ROOT/skins/elastic/templates/login.html
cp skins/elastic/styles/amarsaf-login.css $ROOT/skins/elastic/styles/amarsaf-login.css
cp skins/elastic/images/*.svg           $ROOT/skins/elastic/images/
chown www-data:www-data $ROOT/skins/elastic/templates/login.html \
  $ROOT/skins/elastic/styles/amarsaf-login.css \
  $ROOT/skins/elastic/images/saf-*.svg \
  $ROOT/skins/elastic/images/pattern-1-white.svg
```

---

## Rollback

The installer saves a backup under:

```text
$ROUNDCUBE/skins/elastic/_backup-amarsaf-YYYYMMDD-HHMMSS/
```

Restore:

```bash
cp $BACKUP/templates/login.html $ROOT/skins/elastic/templates/login.html
```

---

## Notes

- ERP login (`erp.amarsaf.com`) is separate — this does not change it.
- Login still uses Linux/PAM passwords (`ceo`, `info`, …).
- If CSS doesn’t load, confirm `amarsaf-login.css` is readable:

```bash
curl -I https://webmail.amarsaf.com/skins/elastic/styles/amarsaf-login.css
```
