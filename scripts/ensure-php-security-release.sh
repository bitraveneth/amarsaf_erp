#!/usr/bin/env bash
# Check the PHP runtime for CVE-2026-109618 and CVE-2026-109619 (PHP2Shell).
# Ubuntu ships the fix inside the existing upstream version, so php -v can
# stay on 8.3.6 after the safe package is installed. This script understands
# those package revisions (USN-8948-1) and upstream releases 8.2.35, 8.3.36,
# 8.4.27, and 8.5.12.
set -euo pipefail

log() {
  printf '%s\n' "$1"
}

upstream_patched() {
  local id="$1"
  if [ "$id" -ge 80512 ]; then return 0; fi
  if [ "$id" -ge 80500 ]; then return 1; fi
  if [ "$id" -ge 80427 ]; then return 0; fi
  if [ "$id" -ge 80400 ]; then return 1; fi
  if [ "$id" -ge 80336 ]; then return 0; fi
  if [ "$id" -ge 80300 ]; then return 1; fi
  if [ "$id" -ge 80235 ]; then return 0; fi
  return 1
}

version_to_id() {
  local major minor patch
  IFS=. read -r major minor patch <<<"${1%%-*}"
  patch="${patch%%[^0-9]*}"
  printf '%d' "$((major * 10000 + minor * 100 + ${patch:-0}))"
}

os_version_id() {
  if [ -r /etc/os-release ]; then
    # shellcheck disable=SC1091
    . /etc/os-release
    printf '%s' "${VERSION_ID:-}"
  fi
}

ubuntu_minimum() {
  local version_id="$1"
  local family="$2"
  case "${version_id}:${family}" in
    22.04:php8.1) printf '%s' '8.1.2-1ubuntu2.28' ;;
    24.04:php8.3) printf '%s' '8.3.6-0ubuntu0.24.04.13' ;;
    26.04:php8.5) printf '%s' '8.5.4-0ubuntu1.5' ;;
    26.10:php8.5) printf '%s' '8.5.9-0ubuntu2.1' ;;
    *) return 1 ;;
  esac
}

package_family() {
  local name="$1"
  if [[ "$name" == libapache2-mod-php* ]]; then
    printf 'php%s' "${name#libapache2-mod-php}"
    return
  fi
  printf '%s' "$name" | sed -E 's/^(php[0-9]+\.[0-9]+).*/\1/'
}

as_root() {
  if [ "$(id -u)" -eq 0 ]; then
    "$@"
  elif command -v sudo >/dev/null 2>&1 && sudo -n true >/dev/null 2>&1; then
    sudo -n "$@"
  else
    return 1
  fi
}

collect_packages() {
  if ! command -v dpkg-query >/dev/null 2>&1; then
    return 0
  fi
  dpkg-query -W -f '${Package} ${Version}\n' 2>/dev/null \
    | grep -E '^(php[0-9]+\.[0-9]+|libapache2-mod-php[0-9])' || true
}

package_patched() {
  local version="$1"
  local family="$2"
  local os_id="$3"
  local upstream id minimum base
  upstream="${version%%-*}"
  id="$(version_to_id "$upstream")"
  if upstream_patched "$id"; then
    return 0
  fi
  if minimum="$(ubuntu_minimum "$os_id" "$family")"; then
    base="${minimum%%-*}"
    if [ "$upstream" = "$base" ] && dpkg --compare-versions "$version" ge "$minimum"; then
      return 0
    fi
  fi
  return 1
}

stack_patched() {
  local os_id pkg version family
  local saw=0
  local bad=0
  os_id="$(os_version_id)"

  while read -r pkg version; do
    [ -n "${pkg:-}" ] || continue
    saw=1
    family="$(package_family "$pkg")"
    if package_patched "$version" "$family" "$os_id"; then
      log "[ok] ${pkg} ${version}"
      continue
    fi
    log "[vulnerable] ${pkg} ${version} is missing CVE-2026-109618 / CVE-2026-109619"
    bad=1
  done < <(collect_packages)

  if [ "$saw" -eq 0 ]; then
    local id
    id="$(php -r 'echo PHP_VERSION_ID;')"
    if upstream_patched "$id"; then
      log "[ok] PHP $(php -r 'echo PHP_VERSION;') is a patched upstream release"
      return 0
    fi
    log "[vulnerable] PHP $(php -r 'echo PHP_VERSION;') is not 8.2.35, 8.3.36, 8.4.27, or 8.5.12 or newer"
    return 1
  fi

  [ "$bad" -eq 0 ]
}

upgrade_packages() {
  local pkgs=()
  local pkg version
  while read -r pkg version; do
    [ -n "${pkg:-}" ] || continue
    pkgs+=("$pkg")
  done < <(collect_packages)

  if [ "${#pkgs[@]}" -eq 0 ]; then
    log "No distro PHP packages to upgrade."
    return 1
  fi

  log "Installing vendor PHP security updates."
  as_root apt-get update
  as_root env DEBIAN_FRONTEND=noninteractive apt-get install -y --only-upgrade "${pkgs[@]}"
}

restart_workers() {
  local unit
  if ! command -v systemctl >/dev/null 2>&1; then
    return 0
  fi
  for unit in php8.1-fpm php8.2-fpm php8.3-fpm php8.4-fpm php8.5-fpm apache2; do
    if systemctl list-unit-files "${unit}.service" --no-legend 2>/dev/null | grep -q "^${unit}.service"; then
      log "Restarting ${unit} so web workers load the patched PHP."
      as_root systemctl try-restart "$unit" || true
    fi
  done
}

if stack_patched; then
  log "PHP runtime already includes the October 2026 security fixes."
  exit 0
fi

if upgrade_packages; then
  restart_workers
fi

if stack_patched; then
  log "PHP runtime is patched."
  exit 0
fi

cat <<'EOF'
This server is still running PHP without the fixes for CVE-2026-109618 and
CVE-2026-109619. Those bugs allow remote code execution against sites that
serve PHP. Update PHP, then restart php-fpm and Apache.

Upstream releases: 8.2.35, 8.3.36, 8.4.27, 8.5.12.
Ubuntu: USN-8948-1. On 24.04 the patched package is php8.3 8.3.6-0ubuntu0.24.04.13 or newer.

  sudo apt-get update
  sudo apt-get upgrade
  sudo systemctl restart php8.3-fpm apache2

Until that is done, keep the site off the public internet.
EOF
exit 1
