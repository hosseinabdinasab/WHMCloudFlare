#!/bin/bash

###############################################################################
# WHMCloudFlare - Uninstallation Script
###############################################################################

INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"

# رنگ‌ها
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Uninstalling WHMCloudFlare${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ This script must be run as root${NC}"
    exit 1
fi

# تایید حذف
read -p "Are you sure you want to uninstall WHMCloudFlare? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Uninstallation cancelled${NC}"
    exit 0
fi

# حذف Hook ها
echo -e "${BLUE}🔗 Removing WHM hooks...${NC}"

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/createacct.php" \
    --category Whostmgr \
    --event Accounts::Create 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/removeacct.php" \
    --category Whostmgr \
    --event Accounts::Remove 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/changepackage.php" \
    --category Whostmgr \
    --event Accounts::ChangePackage 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/setsiteip.php" \
    --category Whostmgr \
    --event Accounts::SetSiteIP 2>/dev/null

# حذف فایل‌ها
echo -e "${BLUE}🗑️  Removing files...${NC}"
if [ -d "$INSTALL_DIR" ]; then
    # Backup تنظیمات قبل از حذف
    if [ -f "$INSTALL_DIR/config/settings.json" ]; then
        BACKUP_FILE="${INSTALL_DIR}_settings_backup_$(date +%Y%m%d_%H%M%S).json"
        cp "$INSTALL_DIR/config/settings.json" "$BACKUP_FILE"
        echo -e "${YELLOW}⚠ Settings backed up to: $BACKUP_FILE${NC}"
    fi
    
    rm -rf "$INSTALL_DIR"
    echo -e "${GREEN}✅ Files removed${NC}"
else
    echo -e "${YELLOW}⚠ Installation directory not found${NC}"
fi

echo ""
echo -e "${GREEN}✅ Uninstallation completed successfully!${NC}"
echo ""

