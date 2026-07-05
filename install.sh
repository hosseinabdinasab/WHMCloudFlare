#!/bin/bash
#
# WHMCloudFlare installer
# Pattern: LiteSpeed cgi/whmcloudflare + cPanel AppConfig + /var/cpanel/addons data
#
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
    echo "Run as root: sudo $0" >&2
    exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WHM_DOCROOT="/usr/local/cpanel/whostmgr/docroot"
ADDON_ROOT="/var/cpanel/addons/whmcloudflare"
CGI_DIR="${WHM_DOCROOT}/cgi/whmcloudflare"
REGISTER="/usr/local/cpanel/bin/register_appconfig"
UNREGISTER="/usr/local/cpanel/bin/unregister_appconfig"
MANAGE_HOOKS="/usr/local/cpanel/bin/manage_hooks"

echo "==> Installing WHMCloudFlare"

# Legacy cleanup
for legacy in \
    "${WHM_DOCROOT}/cgi/addons/whmcloudflare" \
    "${WHM_DOCROOT}/cgi/addons/WHMCloudFlare" \
    "/usr/local/cpanel/whm/addons/WHMCloudFlare" \
    "/usr/local/cpanel/whm/addons/whmcloudflare"
do
    if [[ -e "$legacy" ]]; then
        echo "    Removing legacy path: $legacy"
        rm -rf "$legacy"
    fi
done

if [[ -x "$UNREGISTER" ]]; then
    "$UNREGISTER" whmcloudflare 2>/dev/null || true
fi

mkdir -p "$ADDON_ROOT"/{lib,lang,ui,hooks,config,logs}
mkdir -p "$CGI_DIR"

install -m 0644 "$SCRIPT_DIR"/lib/*.php "$ADDON_ROOT/lib/"
install -m 0644 "$SCRIPT_DIR"/lang/*.php "$ADDON_ROOT/lang/"
install -m 0644 "$SCRIPT_DIR"/ui/index.php "$ADDON_ROOT/ui/"
install -m 0755 "$SCRIPT_DIR"/hooks/*.php "$ADDON_ROOT/hooks/"

install -m 0755 "$SCRIPT_DIR/cgi/index.cgi" "$CGI_DIR/index.cgi"
chown root:root "$CGI_DIR/index.cgi"

chmod 0700 "$ADDON_ROOT/config" "$ADDON_ROOT/logs"
chown -R root:root "$ADDON_ROOT"

# Migrate settings from older installs
for old_cfg in \
    "$ADDON_ROOT/settings.json" \
    "/var/cpanel/addons/WHMCloudFlare/config/settings.json" \
    "/var/cpanel/addons/whmcloudflare/settings.json"
do
    if [[ -f "$old_cfg" && ! -f "$ADDON_ROOT/config/settings.json" ]]; then
        echo "    Migrating config from $old_cfg"
        cp -a "$old_cfg" "$ADDON_ROOT/config/settings.json"
        chmod 0600 "$ADDON_ROOT/config/settings.json"
        break
    fi
done

if [[ -x "$REGISTER" ]]; then
    "$REGISTER" "$SCRIPT_DIR/appconfig/whmcloudflare.conf"
    echo "    Registered AppConfig"
else
    echo "ERROR: register_appconfig not found" >&2
    exit 1
fi

if [[ -x "$MANAGE_HOOKS" ]]; then
    "$MANAGE_HOOKS" add whmcloudflare Accounts::Create --category Whostmgr --stage post --hook "$ADDON_ROOT/hooks/createacct.php" --manual 1
    "$MANAGE_HOOKS" add whmcloudflare Accounts::Remove --category Whostmgr --stage post --hook "$ADDON_ROOT/hooks/removeacct.php" --manual 1
    "$MANAGE_HOOKS" add whmcloudflare Accounts::SiteIP::set --category Whostmgr --stage post --hook "$ADDON_ROOT/hooks/setsiteip.php" --manual 1
    echo "    Registered hooks"
fi

/usr/local/cpanel/bin/whmapi1 nvset datastore_version 1 >/dev/null 2>&1 || true

echo ""
echo "Installed."
echo "  Addon code : $ADDON_ROOT"
echo "  WHM URL    : /cgi/whmcloudflare/"
echo "  Config     : $ADDON_ROOT/config/settings.json"
echo "  Logs       : $ADDON_ROOT/logs/whmcloudflare.log"
echo ""
echo "Open WHM -> Plugins -> WHMCloudFlare"
