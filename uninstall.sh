#!/bin/bash
#
# WHMCloudFlare uninstaller
#
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
    echo "Run as root: sudo $0" >&2
    exit 1
fi

WHM_DOCROOT="/usr/local/cpanel/whostmgr/docroot"
ADDON_ROOT="/var/cpanel/addons/whmcloudflare"
CGI_DIR="${WHM_DOCROOT}/cgi/whmcloudflare"
UNREGISTER="/usr/local/cpanel/bin/unregister_appconfig"
MANAGE_HOOKS="/usr/local/cpanel/bin/manage_hooks"

echo "==> Uninstalling WHMCloudFlare"

if [[ -x "$MANAGE_HOOKS" ]]; then
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::Create --category Whostmgr --stage post 2>/dev/null || true
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::Remove --category Whostmgr --stage post 2>/dev/null || true
    "$MANAGE_HOOKS" delete whmcloudflare Accounts::SiteIP::set --category Whostmgr --stage post 2>/dev/null || true
fi

if [[ -x "$UNREGISTER" ]]; then
    "$UNREGISTER" whmcloudflare 2>/dev/null || true
fi

rm -rf "$CGI_DIR"
rm -rf "${WHM_DOCROOT}/cgi/addons/whmcloudflare"
rm -rf "${WHM_DOCROOT}/cgi/addons/WHMCloudFlare"
rm -rf "/usr/local/cpanel/whm/addons/WHMCloudFlare"
rm -rf "/usr/local/cpanel/whm/addons/whmcloudflare"

read -r -p "Remove config and logs in $ADDON_ROOT? [y/N] " ans
if [[ "${ans,,}" == "y" ]]; then
    rm -rf "$ADDON_ROOT"
    echo "Removed addon data."
else
    echo "Kept $ADDON_ROOT (config/logs preserved)."
    rm -rf "$ADDON_ROOT"/{lib,lang,ui,hooks}
fi

/usr/local/cpanel/bin/whmapi1 nvset datastore_version 1 >/dev/null 2>&1 || true

echo "Uninstall complete."
