#!/bin/sh

# /********************************************
# LiteSpeed Web Server Plugin for WHM
#
# @author LiteSpeed Technologies, Inc.
# @copyright 2008-2024 LiteSpeed Technologies, Inc.
# *********************************************/

#
# If from lsws install, 3 params
# WHM_PLUGIN_SRCDIR=$1
# LSWS_HOME=$2
# CPANEL_PLUGIN_AUTOINSTALL=$3
#
# To ignore the existing version, you can prefix the above params with FORCE.
#
# Note to new developers, this script calls lsws_whm_plugin_install_complete.sh which is
# used to complete the install.  It must be copied in this script so that 
# any additional installation functions are executed by the first instance
# after it's published.  All of those functions should be done there.

# I-5 (CWE-377): Previously a fixed /usr/src/lsws_whm path. /usr/src is
# root-owned on stock cPanel so this is not a casual race target, but pinning
# the name lets a local root-equivalent process (a misbehaving cron, a
# stale leftover from a prior aborted install) re-use a directory we did
# not just create. Compute a per-install scratch path under /usr/src with
# mktemp -d (O_EXCL, 0700, root:root) further below in the download branch.
WHM_PLUGIN_TEMPDIR=""
WHM_DOCROOT="/usr/local/cpanel/whostmgr/docroot"
WHM_PLUGIN_CGIDIR="${WHM_DOCROOT}/cgi"
WHM_PLUGIN_ICONDIR="${WHM_DOCROOT}/addon_plugins"
WHM_PLUGIN_INSDIR="${WHM_PLUGIN_CGIDIR}/lsws"
WHM_PLUGIN_TMPL_INSDIR="${WHM_DOCROOT}/templates/lsws"
WHM_PLUGIN_LSCWP_SRC_DIR="/usr/src/litespeed-wp-plugin"
WHM_PLUGIN_HTTPDIR="https://www.litespeedtech.com/packages/cpanel"
THEME_JUPITER_PLUGIN_DIR="/usr/local/cpanel/base/frontend/jupiter/ls_web_cache_manager"
THEME_PAPER_LANTERN_PLUGIN_DIR="/usr/local/cpanel/base/frontend/paper_lantern/ls_web_cache_manager"
CPANEL_PLUGIN_CAPABLE=0

if [ ! -d "${WHM_PLUGIN_CGIDIR}" ] ; then
    exit
fi

WHM_PLUGIN_INST_USER=$(id)
WHM_PLUGIN_INST_USER=$(expr "${WHM_PLUGIN_INST_USER}" : 'uid=.*(\(.*\)) gid=.*')

if [ "${WHM_PLUGIN_INST_USER}" != "root" ]  ; then
    echo "Require root permission to install this plugin. Abort!"
    exit
fi

if [ "${1}" = "FORCE" ] ; then
    FORCE="1"
    echo "Forcing install, ignore current version"
    shift
fi

if [ "${2}" = "" ] ; then
    LSWS_HOME="/usr/local/lsws"
else
    LSWS_HOME="${2}"
fi

WEBCACHE_MGR_DATA_DIR="${LSWS_HOME}/admin/lscdata"
CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG="${WHM_PLUGIN_INSDIR}/cpanel_autoinstall_off"
WHM_PLUGIN_DATA_DIR="${WHM_PLUGIN_INSDIR}/data"
WHM_PLUGIN_LSWS_HOME_FILE="${WHM_PLUGIN_INSDIR}/LSWS_HOME.config"

# LSWS-2026-007: Reinstall used to backup config to fixed /tmp paths
# (/tmp/lsws_whm_plugin_data_tmp, /tmp/LSWS_HOME.config,
# /tmp/cpanel_autoinstall_off). Any local user could pre-create those
# names as symlinks pointing to a root-owned file; the subsequent
# `mv` would follow the symlink and clobber it root-owned. We now
# create a per-install scratch directory owned root:root mode 0700
# inside /root/.cache (falling back to /tmp via mktemp, which uses
# O_EXCL and an unguessable suffix, so the race window is closed).
#
# We deliberately keep the backup *file names* identical inside the
# new scratch dir so the post-install restore block below doesn't
# care which path style was used.
WHM_PLUGIN_BACKUP_DIR=""

# mktemp -d both creates the directory atomically (O_EXCL) and gives
# it 0700 root:root by default. Prefer /root/.cache since /tmp may be
# noexec or shared with other tenants on hardened hosts; fall back
# to system temp if /root/.cache isn't writable for some reason.
if [ -w /root ] ; then
    mkdir -p /root/.cache 2>/dev/null
    WHM_PLUGIN_BACKUP_DIR=$(mktemp -d /root/.cache/lsws_whm_install.XXXXXXXX 2>/dev/null || true)
fi

if [ -z "${WHM_PLUGIN_BACKUP_DIR}" ] ; then
    WHM_PLUGIN_BACKUP_DIR=$(mktemp -d -t lsws_whm_install.XXXXXXXX)
fi

if [ -z "${WHM_PLUGIN_BACKUP_DIR}" ] || [ ! -d "${WHM_PLUGIN_BACKUP_DIR}" ] ; then
    echo "[ERROR] LSWS-2026-007: cannot create secure backup directory. Abort!"
    exit 1
fi

chmod 0700 "${WHM_PLUGIN_BACKUP_DIR}"

# Ensure the scratch dir is removed even on script failure.
# shellcheck disable=SC2064
trap "/bin/rm -rf '${WHM_PLUGIN_BACKUP_DIR}'" EXIT INT TERM

TMP_CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG="${WHM_PLUGIN_BACKUP_DIR}/cpanel_autoinstall_off"
TMP_WHM_PLUGIN_DATA_DIR="${WHM_PLUGIN_BACKUP_DIR}/lsws_whm_plugin_data_tmp"
TMP_WHM_PLUGIN_LSWS_HOME_FILE="${WHM_PLUGIN_BACKUP_DIR}/LSWS_HOME.config"
THIS_DIR=$(dirname "$(readlink -f "$0")")

whmPluginNeedsUpdate()
{
    CURR_WHM_VER="${1}"

    if ! LATEST_WHM_VER=$(wget -qO- "${WHM_PLUGIN_HTTPDIR}/WHM_LATEST_VER"); then
        echo "Failed to query latest WHM plugin version (wget failure). Abort!"
        exit;
    fi

    FILTERED_LATEST_WHM_VER="$(echo "${LATEST_WHM_VER}" | \
      sed -e 's/^[^[:digit:]]*\.//g' -e 's/\.\+$//g' -e 's/\.+$//g' -e 's/\.\.\+/\./g' -e 's/[^[:digit:]\.]//g')"

    if [ "${LATEST_WHM_VER}" != "${FILTERED_LATEST_WHM_VER}" ] ; then
        echo "Failed to query latest WHM plugin version (unexpected content). Abort!"
        exit;
    fi

    CURR_MAJOR=$(echo "${CURR_WHM_VER}" | awk -F"." '{print $1}')
    LATEST_MAJOR=$(echo "${LATEST_WHM_VER}" | awk -F"." '{print $1}')

    if [ "${CURR_MAJOR}" -lt "${LATEST_MAJOR}" ] ; then
        return 0
    elif [ "${CURR_MAJOR}" -gt "${LATEST_MAJOR}" ] ; then
        return 1
    fi

    CURR_MINOR=$(echo "${CURR_WHM_VER}" | awk -F"." '{print $2}')
    LATEST_MINOR=$(echo "${LATEST_WHM_VER}" | awk -F"." '{print $2}')

    if [ "${CURR_MINOR}" -lt "${LATEST_MINOR}" ] ; then
        return 0
    elif [ "${CURR_MINOR}" -gt "${LATEST_MINOR}" ] ; then
        return 1
    fi

    CURR_IMPROVEMENT=$(echo "${CURR_WHM_VER}" | awk -F"." '{print match($3, /[^ ]/) ? $3 : 0}')
    LATEST_IMPROVEMENT=$(echo "${LATEST_WHM_VER}" | awk -F"." '{print match($3, /[^ ]/) ? $3 : 0}')

    if [ "${CURR_IMPROVEMENT}" -lt "${LATEST_IMPROVEMENT}" ] ; then
        return 0
    elif [ "${CURR_IMPROVEMENT}" -gt "${LATEST_IMPROVEMENT}" ] ; then
        return 1
    fi

    CURR_PATCH=$(echo "${CURR_WHM_VER}" | awk -F"." '{print match($4, /[^ ]/) ? $4 : 0}')
    LATEST_PATCH=$(echo "${LATEST_WHM_VER}" | awk -F"." '{print match($4, /[^ ]/) ? $4 : 0}')

    if [ "${CURR_PATCH}" -lt "${LATEST_PATCH}" ] ; then
        return 0
    elif [ "${CURR_PATCH}" -gt "${LATEST_PATCH}" ] ; then
        return 1
    fi

    return 1
}

echo ""
echo " Install LiteSpeed Web Server Plugin for WHM"
echo "=============================================="
echo ""

CURR_WHM_VER_FILE="${WHM_PLUGIN_INSDIR}/VERSION"

if [ "${FORCE}" = "" ] && [ -e "${CURR_WHM_VER_FILE}" ] ; then

    CURR_WHM_VER=$(cat "${CURR_WHM_VER_FILE}")

    if ! whmPluginNeedsUpdate "${CURR_WHM_VER}" ; then
        echo "Installed WHM Plugin version already up-to-date. Abort!"
        exit;
    fi
fi

if [ "${1}" = "" ] ; then
    echo "... creating directories ..."

    # I-5 (CWE-377): per-install scratch dir under /usr/src via mktemp -d.
    # /usr/src is root-owned 0755 on stock cPanel; mktemp adds O_EXCL +
    # unguessable suffix and creates the dir 0700 root:root, so even a
    # privileged-leftover or symlinked /usr/src/lsws_whm cannot interfere
    # with our extraction destination.
    mkdir -p /usr/src 2>/dev/null
    WHM_PLUGIN_TEMPDIR=$(mktemp -d /usr/src/lsws_whm.XXXXXXXX 2>/dev/null || mktemp -d -t lsws_whm.XXXXXXXX)

    if [ -z "${WHM_PLUGIN_TEMPDIR}" ] || [ ! -d "${WHM_PLUGIN_TEMPDIR}" ] ; then
        echo "[ERROR] I-5: cannot create secure plugin download dir. Abort!"
        exit 1
    fi

    chmod 0700 "${WHM_PLUGIN_TEMPDIR}"
    echo "  Temp directory created (${WHM_PLUGIN_TEMPDIR})"

    # Ensure the scratch dir is removed even on script failure. We keep the
    # earlier scratch-dir trap intact by appending to the same handler.
    # shellcheck disable=SC2064
    trap "/bin/rm -rf '${WHM_PLUGIN_BACKUP_DIR}' '${WHM_PLUGIN_TEMPDIR}'" EXIT INT TERM

    cd "${WHM_PLUGIN_TEMPDIR}"

    echo "... downloading latest version of the plugin ..."

    if ! wget \
            "--output-document=${WHM_PLUGIN_TEMPDIR}/lsws_whm_plugin.tar.gz" \
            "${WHM_PLUGIN_HTTPDIR}/lsws_whm_plugin.tar.gz"
    then
        /bin/rm -rf "${WHM_PLUGIN_TEMPDIR}"

        echo ""
        echo "Failed to download lsws_whm_plugin.tar.gz. Abort!"
        exit;
    fi

    echo "Done downloading."
    echo ""

    echo "... extracting ..."

    # I-5 (CWE-377): --no-same-owner / --no-same-permissions stop the archive
    # from telling tar to keep arbitrary uid/gid/mode bits from the upstream
    # tarball; everything lands owned by the current (root) user with the
    # process umask applied. -p (preserve) would otherwise be the default
    # under root.
    if ! tar --no-same-owner --no-same-permissions -zxf lsws_whm_plugin.tar.gz; then
        /bin/rm -rf "${WHM_PLUGIN_TEMPDIR}"

        echo ""
        echo "Failed to to extract lsws_whm_plugin.tar.gz. Abort!"
        exit;
    fi

    echo ""
fi

# Create working directories for WHM PHP files and backup any existing data
if [ -e "${WHM_PLUGIN_INSDIR}" ] ; then

    if [ -e "${WHM_PLUGIN_CGIDIR}/addon_lsws.cgi" ] ; then
        echo "  Removing old entry script addon_lsws.cgi"
        /bin/rm -f "${WHM_PLUGIN_CGIDIR}/addon_lsws.cgi"
    fi

    if [ ! -e "${WEBCACHE_MGR_DATA_DIR}" ] ; then

        if [ -e "${LSWS_HOME}/admin" ] ; then
            mkdir "${WEBCACHE_MGR_DATA_DIR}"
        fi
    fi

    if [ -e "${CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}" ] ; then
        /bin/mv "${CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}" "${TMP_CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}"
    fi

    if [ -e "${WHM_PLUGIN_DATA_DIR}" ] ; then
        /bin/mv "${WHM_PLUGIN_DATA_DIR}" "${TMP_WHM_PLUGIN_DATA_DIR}"
    fi

    if [ -e "${WHM_PLUGIN_LSWS_HOME_FILE}" ] ; then
        /bin/mv "${WHM_PLUGIN_LSWS_HOME_FILE}" "${TMP_WHM_PLUGIN_LSWS_HOME_FILE}"
    fi

    echo "  Removing old working directory ${WHM_PLUGIN_INSDIR}"
    /bin/rm -rf "${WHM_PLUGIN_INSDIR}"
fi

if [ -e "${WHM_PLUGIN_TMPL_INSDIR}" ] ; then
    echo " Removing old template directory ${WHM_PLUGIN_TMPL_INSDIR}"
    /bin/rm -rf "${WHM_PLUGIN_TMPL_INSDIR}"
fi

#Cleanup old lsc data from installs < 2.1.12
if [ -e "${LSWS_HOME}/add-ons/webcachemgr/shared/lsc_versions_data" ] ; then
    /bin/rm -f "${LSWS_HOME}/add-ons/webcachemgr/shared/lsc_versions_data"
fi

if [ -e "${LSWS_HOME}/add-ons/webcachemgr/shared/lsc_manager_data" ] ; then
    /bin/rm -f "${LSWS_HOME}/add-ons/webcachemgr/shared/lsc_manager_data"
fi

#cleanup old lsc data/files from installs < 3.0.0
if [ -e "${WEBCACHE_MGR_DATA_DIR}/lsc_manager_data" ] ; then
    /bin/rm -f "${WEBCACHE_MGR_DATA_DIR}/lsc_manager_data"
fi

if [ -e "${WEBCACHE_MGR_DATA_DIR}/lsc_versions_data" ] ; then
    /bin/rm -f "${WEBCACHE_MGR_DATA_DIR}/lsc_versions_data"
fi

#force new data files permissions
if [ -e "${WEBCACHE_MGR_DATA_DIR}/lscm.data" ] ; then
    chmod 600 "${WEBCACHE_MGR_DATA_DIR}/lscm.data"
fi

if [ -e "${WEBCACHE_MGR_DATA_DIR}/lscm.data.cust" ] ; then
    chmod 600 "${WEBCACHE_MGR_DATA_DIR}/lscm.data.cust"
fi

mkdir -v "${WHM_PLUGIN_INSDIR}"
mkdir -v "${WHM_PLUGIN_TMPL_INSDIR}"

if [ -e "${TMP_CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}" ] ; then
    /bin/mv "${TMP_CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}" "${CPANEL_PLUGIN_AUTOINSTALL_DISABLE_FLAG}"
    echo "  Retained disable cPanel plugin auto install flag file"
fi

if [ -e "${TMP_WHM_PLUGIN_DATA_DIR}" ] ; then
    /bin/mv "${TMP_WHM_PLUGIN_DATA_DIR}" "${WHM_PLUGIN_DATA_DIR}"
    echo "  Retained WHM plugin data dir files"
fi

if [ -e "${TMP_WHM_PLUGIN_LSWS_HOME_FILE}" ] ; then
    /bin/mv "${TMP_WHM_PLUGIN_LSWS_HOME_FILE}" "${WHM_PLUGIN_LSWS_HOME_FILE}"
    echo "  Retained LSWS_HOME.config file"
fi

echo ""


if [ "${1}" = "" ] ; then
    WHM_PLUGIN_SRCDIR="${WHM_PLUGIN_TEMPDIR}/lsws_whm_plugin"
    /bin/cp -r "${WHM_PLUGIN_SRCDIR}"/* "${WHM_PLUGIN_INSDIR}/"

    cd "${WHM_PLUGIN_INSDIR}"
#    # Removes install files
    /bin/rm -rf "${WHM_PLUGIN_TEMPDIR}"
else
    # install from lsws addon
    WHM_PLUGIN_SRCDIR="${1}"
    /bin/cp -r "${WHM_PLUGIN_SRCDIR}"/* "${WHM_PLUGIN_INSDIR}/"
    echo "LSWS_HOME=${LSWS_HOME}" > "${WHM_PLUGIN_LSWS_HOME_FILE}"
fi

if [ -e "${WHM_PLUGIN_INSDIR}/lsws_whm_plugin_install_complete.sh" ]; then
    CPANEL_PLUGIN_AUTOINSTALL="${3}"
    COMPLETE_SCRIPT_DIR="${WHM_PLUGIN_INSDIR}"
    . "${WHM_PLUGIN_INSDIR}/lsws_whm_plugin_install_complete.sh"
elif [ -e "${THIS_DIR}/lsws_whm_plugin_install_complete.sh" ]; then
    # shellcheck disable=SC2034
    CPANEL_PLUGIN_AUTOINSTALL="${3}"
    # shellcheck disable=SC2034
    COMPLETE_SCRIPT_DIR="${THIS_DIR}"
    echo "Using ${THIS_DIR}/lsws_whm_plugin_install_complete.sh to finish the install (take note)"
    . "${THIS_DIR}/lsws_whm_plugin_install_complete.sh"
else
    echo "lsws_whm_plugin_install_complete.sh not found, can't complete the install \
    (${THIS_DIR}/lsws_whm_plugin_install_complete.sh)"
    exit 1
fi

