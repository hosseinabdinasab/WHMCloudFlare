#!/bin/bash

###############################################################################
# WHMCloudFlare - Fix Plugin Display in WHM
# این اسکریپت مشکل نمایش پلاگین در لیست WHM را برطرف می‌کند
###############################################################################

INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# رنگ‌ها
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Fixing WHMCloudFlare Plugin Display${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ This script must be run as root${NC}"
    exit 1
fi

# بررسی وجود دایرکتوری نصب
if [ ! -d "$INSTALL_DIR" ]; then
    echo -e "${RED}❌ Installation directory not found: $INSTALL_DIR${NC}"
    echo -e "${YELLOW}Please run the installer first: ./install.sh${NC}"
    exit 1
fi

echo -e "${BLUE}Step 1: Copying configuration file...${NC}"
if [ -f "$CURRENT_DIR/whmcloudflare.cpanel.yml" ]; then
    cp "$CURRENT_DIR/whmcloudflare.cpanel.yml" "$INSTALL_DIR/"
    chmod 644 "$INSTALL_DIR/whmcloudflare.cpanel.yml"
    echo -e "${GREEN}✓ Configuration file copied${NC}"
else
    echo -e "${YELLOW}⚠ Configuration file not found in source directory${NC}"
fi

echo ""
echo -e "${BLUE}Step 2: Setting correct permissions...${NC}"
chmod 755 "$INSTALL_DIR"
chmod 644 "$INSTALL_DIR/whmcloudflare.cpanel.yml" 2>/dev/null
chmod 644 "$INSTALL_DIR/ui/index.php"
echo -e "${GREEN}✓ Permissions set${NC}"

echo ""
echo -e "${BLUE}Step 3: Registering addon via WHMAPI...${NC}"
/usr/local/cpanel/bin/whmapi1 register_addon \
    name=WHMCloudFlare \
    displayname="WHMCloudFlare" \
    link="/usr/local/cpanel/whm/addons/WHMCloudFlare/ui/index.php" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Addon registered via WHMAPI${NC}"
else
    echo -e "${YELLOW}⚠ Could not register via WHMAPI (may already exist)${NC}"
fi

echo ""
echo -e "${BLUE}Step 4: Clearing WHM cache...${NC}"
/usr/local/cpanel/bin/whmapi1 clear_cache 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ Could not clear cache (trying alternative method)${NC}"
    /scripts/rebuild_cpanel_cache 2>/dev/null
fi

echo ""
echo -e "${BLUE}Step 5: Verifying files...${NC}"
if [ -f "$INSTALL_DIR/whmcloudflare.cpanel.yml" ]; then
    echo -e "${GREEN}✓ Configuration file exists${NC}"
    echo -e "${BLUE}  Location: $INSTALL_DIR/whmcloudflare.cpanel.yml${NC}"
else
    echo -e "${RED}✗ Configuration file not found${NC}"
fi

if [ -f "$INSTALL_DIR/ui/index.php" ]; then
    echo -e "${GREEN}✓ UI file exists${NC}"
    echo -e "${BLUE}  Location: $INSTALL_DIR/ui/index.php${NC}"
else
    echo -e "${RED}✗ UI file not found${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Fix Completed!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Log out of WHM"
echo "2. Log back in to WHM"
echo "3. Go to: Plugins > WHMCloudFlare"
echo ""
echo -e "${BLUE}Direct Access URL:${NC}"
echo "https://YOUR_SERVER_IP:2087/whm/addons/WHMCloudFlare/ui/index.php"
echo ""
echo -e "${BLUE}If the plugin still doesn't appear, you can access it directly via:${NC}"
echo "https://YOUR_SERVER_IP:2087/whm/addons/WHMCloudFlare/ui/index.php"
echo ""

