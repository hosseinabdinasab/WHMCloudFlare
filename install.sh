#!/bin/bash
#
# WHMCloudFlare installer (LiteSpeed WHM plugin pattern)
#
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
    echo "Run as root: sudo $0" >&2
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WHM_DOCROOT="/usr/local/cpanel/whostmgr/docroot"
PLUGIN_SRC="${SCRIPT_DIR}/whmcloudflare"
PLUGIN_DIR="${WHM_DOCROOT}/cgi/whmcloudflare"
TMPL_DIR="${WHM_DOCROOT}/templates/whmcloudflare"
REGISTER="/usr/local/cpanel/bin/register_appconfig"
UNREGISTER="/usr/local/cpanel/bin/unregister_appconfig"
MANAGE_HOOKS="/usr/local/cpanel/bin/manage_hooks"

echo "==> Installing WHMCloudFlare"

if [[ ! -d "$PLUGIN_SRC" ]]; then
    echo "ERROR: missing ${PLUGIN_SRC}" >&2
    exit 1
fi

# Remove legacy layouts
for legacy in \
    "${WHM_DOCROOT}/cgi/addons/whmcloudflare" \
    "${WHM_DOCROOT}/cgi/addons/WHMCloudFlare" \
    "/usr/local/cpanel/whm/addons/WHMCloudFlare" \
    "/usr/local/cpanel/whm/addons/whmcloudflare" \
    "${PLUGIN_DIR}/index.cgi"
do
    if [[ -e "$legacy" ]]; then
        echo "    Removing legacy: $legacy"
        rm -rf "$legacy"
    fi
done

if [[ -x "$UNREGISTER" ]]; then
    "$UNREGISTER" whmcloudflare 2>/dev/null || true
fi

# Backup data from previous install locations
TMP_DATA=""
for old_data in \
    "${PLUGIN_DIR}/data" \
    "/var/cpanel/addons/whmcloudflare/config" \
    "/var/cpanel/addons/whmcloudflare/data"
do
    if [[ -d "$old_data" && -z "$TMP_DATA" ]]; then
        TMP_DATA="$(mktemp -d /root/.cache/whmcf-data.XXXXXX)"
        cp -a "$old_data/." "$TMP_DATA/" 2>/dev/null || true
        echo "    Backed up data from $old_data"
    fi
done

if [[ -d "$PLUGIN_DIR" ]]; then
    if [[ -d "${PLUGIN_DIR}/data" && -z "$TMP_DATA" ]]; then
        TMP_DATA="$(mktemp -d /root/.cache/whmcf-data.XXXXXX)"
        cp -a "${PLUGIN_DIR}/data/." "$TMP_DATA/"
    fi
    rm -rf "$PLUGIN_DIR"
fi

rm -rf "$TMPL_DIR"
mkdir -p "$PLUGIN_DIR" "$TMPL_DIR"

echo "    Copying plugin files..."
cp -a "${PLUGIN_SRC}/." "$PLUGIN_DIR/"
install -m 0644 "${SCRIPT_DIR}/templates/whmcloudflare.html.tt" "${TMPL_DIR}/whmcloudflare.html.tt"

mkdir -p "${PLUGIN_DIR}/data/config" "${PLUGIN_DIR}/data/logs"
chmod 700 "${PLUGIN_DIR}/data" "${PLUGIN_DIR}/data/config" "${PLUGIN_DIR}/data/logs"

# Migrate settings.json into data/config/
migrate_cfg() {
    local src="$1"
    local dst="${PLUGIN_DIR}/data/config/settings.json"
    if [[ -f "$src" && ! -f "$dst" ]]; then
        cp -a "$src" "$dst"
        chmod 0600 "$dst"
        echo "    Migrated config from $src"
    fi
}

migrate_cfg "/var/cpanel/addons/whmcloudflare/config/settings.json"
migrate_cfg "/var/cpanel/addons/whmcloudflare/settings.json"

if [[ -n "$TMP_DATA" ]]; then
    if [[ -f "${TMP_DATA}/settings.json" ]]; then
        migrate_cfg "${TMP_DATA}/settings.json"
    fi
    if [[ -f "${TMP_DATA}/config/settings.json" ]]; then
        migrate_cfg "${TMP_DATA}/config/settings.json"
    fi
    if [[ -d "${TMP_DATA}/logs" ]]; then
        cp -an "${TMP_DATA}/logs/." "${PLUGIN_DIR}/data/logs/" 2>/dev/null || true
    fi
    rm -rf "$TMP_DATA"
fi

chmod 700 "${PLUGIN_DIR}/whmcloudflare.cgi"
chmod 700 "${PLUGIN_DIR}/hooks/"*.php
chmod -R go-rwx "${PLUGIN_DIR}/data"
chown -R root:root "$PLUGIN_DIR" "$TMPL_DIR"

if [[ -x "$REGISTER" ]]; then
    "$REGISTER" "${SCRIPT_DIR}/appconfig/whmcloudflare.conf"
    echo "    Registered AppConfig"
else
    echo "ERROR: register_appconfig not found" >&2
    exit 1
fi

HOOK_DIR="${PLUGIN_DIR}/hooks"
if [[ -x "$MANAGE_HOOKS" ]]; then
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::Create --category Whostmgr --stage post 2>/dev/null || true
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::Remove --category Whostmgr --stage post 2>/dev/null || true
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::SiteIP::set --category Whostmgr --stage post 2>/dev/null || true

    "$MANAGE_HOOKS" add whmcloudflare Accounts::Create --category Whostmgr --stage post --hook "${HOOK_DIR}/createacct.php" --manual 1
    "$MANAGE_HOOKS" add whmcloudflare Accounts::Remove --category Whostmgr --stage post --hook "${HOOK_DIR}/removeacct.php" --manual 1
    "$MANAGE_HOOKS" add whmcloudflare Accounts::SiteIP::set --category Whostmgr --stage post --hook "${HOOK_DIR}/setsiteip.php" --manual 1
    echo "    Registered hooks"
fi

/usr/local/cpanel/bin/whmapi1 nvset datastore_version 1 >/dev/null 2>&1 || true

echo ""
echo "Installed."
echo "  Plugin     : ${PLUGIN_DIR}"
echo "  Template   : ${TMPL_DIR}/whmcloudflare.html.tt"
echo "  WHM URL    : /cgi/whmcloudflare/whmcloudflare.cgi"
echo "  Config     : ${PLUGIN_DIR}/data/config/settings.json"
echo "  Logs       : ${PLUGIN_DIR}/data/logs/whmcloudflare.log"
echo ""
echo "Open WHM -> Plugins -> WHMCloudFlare"
