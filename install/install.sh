#!/bin/bash

###############################################################################
# WHMCloudFlare - Installation Script (Internal)
# This script is called by the main installer
###############################################################################

INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# رنگ‌ها
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Installing WHMCloudFlare${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ This script must be run as root${NC}"
    exit 1
fi

# ایجاد دایرکتوری نصب
echo -e "${BLUE}📁 Creating directories...${NC}"
mkdir -p "$INSTALL_DIR"
mkdir -p "$INSTALL_DIR/logs"
mkdir -p "$INSTALL_DIR/config"
mkdir -p "$INSTALL_DIR/cache"
mkdir -p "$INSTALL_DIR/lang"

# کپی فایل‌ها
echo -e "${BLUE}📋 Copying files...${NC}"
cp -r "$CURRENT_DIR/lib" "$INSTALL_DIR/" 2>/dev/null
cp -r "$CURRENT_DIR/hooks" "$INSTALL_DIR/" 2>/dev/null
cp -r "$CURRENT_DIR/ui" "$INSTALL_DIR/" 2>/dev/null
cp -r "$CURRENT_DIR/cpanel" "$INSTALL_DIR/" 2>/dev/null
cp -r "$CURRENT_DIR/lang" "$INSTALL_DIR/" 2>/dev/null

# تنظیم دسترسی‌ها
echo -e "${BLUE}🔐 Setting permissions...${NC}"
chmod -R 755 "$INSTALL_DIR"
chmod 777 "$INSTALL_DIR/logs"
chmod 777 "$INSTALL_DIR/config"
chmod 777 "$INSTALL_DIR/cache"

# ثبت Hook ها در WHM
echo -e "${BLUE}🔗 Registering WHM hooks...${NC}"

# حذف Hook های قدیمی
/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/createacct.php" \
    --category Whostmgr --event Accounts::Create 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/removeacct.php" \
    --category Whostmgr --event Accounts::Remove 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/changepackage.php" \
    --category Whostmgr --event Accounts::ChangePackage 2>/dev/null

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/setsiteip.php" \
    --category Whostmgr --event Accounts::SetSiteIP 2>/dev/null

# ثبت Hook های جدید
/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/createacct.php" \
    --category Whostmgr \
    --event Accounts::Create \
    --stage post \
    --hook "$INSTALL_DIR/hooks/createacct.php" \
    --exectype script 2>/dev/null

/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/removeacct.php" \
    --category Whostmgr \
    --event Accounts::Remove \
    --stage post \
    --hook "$INSTALL_DIR/hooks/removeacct.php" \
    --exectype script 2>/dev/null

/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/changepackage.php" \
    --category Whostmgr \
    --event Accounts::ChangePackage \
    --stage post \
    --hook "$INSTALL_DIR/hooks/changepackage.php" \
    --exectype script 2>/dev/null

/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/setsiteip.php" \
    --category Whostmgr \
    --event Accounts::SetSiteIP \
    --stage post \
    --hook "$INSTALL_DIR/hooks/setsiteip.php" \
    --exectype script 2>/dev/null

echo ""
echo -e "${GREEN}✅ Installation completed successfully!${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Go to WHM > Plugins > WHMCloudFlare"
echo "2. Enter your Cloudflare API Token or API Key + Email"
echo "3. Set your Zone ID"
echo "4. Enable the module"
echo ""

