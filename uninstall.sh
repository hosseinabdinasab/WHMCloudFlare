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
PLUGIN_DIR="${WHM_DOCROOT}/cgi/whmcloudflare"
TMPL_DIR="${WHM_DOCROOT}/templates/whmcloudflare"
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
    "$UNREGISTER" whmcloudflare_cpanel 2>/dev/null || true
fi

for theme in jupiter paper_lantern; do
    rm -rf "/usr/local/cpanel/base/frontend/${theme}/whmcloudflare"
done

rm -rf "$TMPL_DIR"
rm -rf "${WHM_DOCROOT}/cgi/addons/whmcloudflare"
rm -rf "${WHM_DOCROOT}/cgi/addons/WHMCloudFlare"
rm -rf "/usr/local/cpanel/whm/addons/WHMCloudFlare"
rm -rf "/var/cpanel/addons/whmcloudflare"

read -r -p "Remove plugin and data in ${PLUGIN_DIR}? [y/N] " ans
case "${ans}" in
    [yY]|[yY][eE][sS])
        rm -rf "$PLUGIN_DIR"
        echo "Removed ${PLUGIN_DIR}"
        ;;
    *)
        if [[ -d "$PLUGIN_DIR" ]]; then
            find "$PLUGIN_DIR" -mindepth 1 -maxdepth 1 ! -name data -exec rm -rf {} +
            echo "Kept ${PLUGIN_DIR}/data (config/logs preserved)"
        fi
        ;;
esac

/usr/local/cpanel/bin/whmapi1 nvset datastore_version 1 >/dev/null 2>&1 || true
echo "Uninstall complete."
