#!/bin/bash

# اسکریپت حذف WHMCloudFlare

INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"

echo "=========================================="
echo "حذف WHMCloudFlare"
echo "=========================================="

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ این اسکریپت باید با دسترسی root اجرا شود"
    exit 1
fi

# حذف Hook ها
echo "🔗 حذف Hook های WHM..."

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/createacct.php" \
    --category Whostmgr \
    --event Accounts::Create

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/removeacct.php" \
    --category Whostmgr \
    --event Accounts::Remove

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/changepackage.php" \
    --category Whostmgr \
    --event Accounts::ChangePackage

/usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/setsiteip.php" \
    --category Whostmgr \
    --event Accounts::SetSiteIP

# حذف فایل‌ها
echo "🗑️ حذف فایل‌ها..."
if [ -d "$INSTALL_DIR" ]; then
    rm -rf "$INSTALL_DIR"
    echo "✅ فایل‌ها حذف شدند"
else
    echo "⚠️ دایرکتوری نصب یافت نشد"
fi

echo ""
echo "✅ حذف با موفقیت انجام شد!"

