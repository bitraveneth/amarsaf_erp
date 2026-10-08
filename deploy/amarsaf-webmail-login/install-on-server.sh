#!/usr/bin/env bash
# Run this ON the mail VPS as root, from inside the uploaded package folder.
set -euo pipefail

echo "== Find Roundcube document root =="
ROOT=""
for candidate in \
  /var/www/webmail-new \
  /var/www/mail.amarsaf.com \
  /var/www/webmail.amarsaf.com \
  /var/www/html/webmail \
  /var/www/roundcube
do
  if [[ -f "$candidate/skins/elastic/templates/login.html" ]]; then
    ROOT="$candidate"
    break
  fi
done

if [[ -z "$ROOT" ]]; then
  ROOT=$(grep -R "webmail.amarsaf.com" /etc/apache2/sites-enabled 2>/dev/null \
    | grep -i DocumentRoot \
    | head -1 \
    | awk '{print $2}' \
    | tr -d '"' || true)
fi

if [[ -z "${ROOT:-}" || ! -d "${ROOT:-/nonexistent}/skins/elastic" ]]; then
  echo "Could not auto-detect Roundcube path."
  echo "Find it with:"
  echo "  grep -R webmail.amarsaf.com /etc/apache2/sites-enabled -n"
  echo "Then run:"
  echo "  ROUNDCUBE_ROOT=/path/to/roundcube bash install-on-server.sh"
  exit 1
fi

ROOT="${ROUNDCUBE_ROOT:-$ROOT}"
echo "Using: $ROOT"

STAMP=$(date +%Y%m%d-%H%M%S)
BACKUP="$ROOT/skins/elastic/_backup-amarsaf-$STAMP"
mkdir -p "$BACKUP/templates" "$BACKUP/styles" "$BACKUP/images"

PKG_DIR="$(cd "$(dirname "$0")" && pwd)"

if [[ -f "$ROOT/skins/elastic/templates/login.html" ]]; then
  cp -a "$ROOT/skins/elastic/templates/login.html" "$BACKUP/templates/"
fi

cp -a "$PKG_DIR/skins/elastic/templates/login.html" "$ROOT/skins/elastic/templates/login.html"
cp -a "$PKG_DIR/skins/elastic/styles/amarsaf-login.css" "$ROOT/skins/elastic/styles/amarsaf-login.css"
cp -a "$PKG_DIR/skins/elastic/images/saf-logo-white.svg" "$ROOT/skins/elastic/images/saf-logo-white.svg"
cp -a "$PKG_DIR/skins/elastic/images/saf-logomark.svg" "$ROOT/skins/elastic/images/saf-logomark.svg"
cp -a "$PKG_DIR/skins/elastic/images/pattern-1-white.svg" "$ROOT/skins/elastic/images/pattern-1-white.svg"

# Keep product name branded
CFG="$ROOT/config/config.inc.php"
if [[ -f "$CFG" ]]; then
  if grep -q "product_name" "$CFG"; then
    sed -i "s/\$config\['product_name'\]\s*=\s*'[^']*'/\$config['product_name'] = 'Amarsaf Webmail'/" "$CFG" || true
  else
    echo "\$config['product_name'] = 'Amarsaf Webmail';" >> "$CFG"
  fi
fi

chown -R www-data:www-data \
  "$ROOT/skins/elastic/templates/login.html" \
  "$ROOT/skins/elastic/styles/amarsaf-login.css" \
  "$ROOT/skins/elastic/images/saf-logo-white.svg" \
  "$ROOT/skins/elastic/images/saf-logomark.svg" \
  "$ROOT/skins/elastic/images/pattern-1-white.svg" 2>/dev/null || true

echo
echo "Installed."
echo "Backup: $BACKUP"
echo "Open: https://webmail.amarsaf.com/  (hard refresh: Ctrl+Shift+R)"
