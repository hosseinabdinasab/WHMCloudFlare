#!/bin/bash

# اسکریپت نصب WHMCloudFlare

INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "=========================================="
echo "نصب WHMCloudFlare"
echo "=========================================="

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ این اسکریپت باید با دسترسی root اجرا شود"
    exit 1
fi

# ایجاد دایرکتوری نصب
echo "📁 ایجاد دایرکتوری نصب..."
mkdir -p "$INSTALL_DIR"
mkdir -p "$INSTALL_DIR/logs"
mkdir -p "$INSTALL_DIR/config"

# کپی فایل‌ها
echo "📋 کپی فایل‌ها..."
cp -r "$CURRENT_DIR/lib" "$INSTALL_DIR/"
cp -r "$CURRENT_DIR/hooks" "$INSTALL_DIR/"
cp -r "$CURRENT_DIR/ui" "$INSTALL_DIR/"

# تنظیم دسترسی‌ها
echo "🔐 تنظیم دسترسی‌ها..."
chmod -R 755 "$INSTALL_DIR"
chmod 777 "$INSTALL_DIR/logs"
chmod 777 "$INSTALL_DIR/config"

# ثبت Hook ها در WHM
echo "🔗 ثبت Hook های WHM..."

# Hook ایجاد اکانت
/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/createacct.php" \
    --category Whostmgr \
    --event Accounts::Create \
    --stage post \
    --hook "$INSTALL_DIR/hooks/createacct.php" \
    --exectype script

# Hook حذف اکانت
/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/removeacct.php" \
    --category Whostmgr \
    --event Accounts::Remove \
    --stage post \
    --hook "$INSTALL_DIR/hooks/removeacct.php" \
    --exectype script

# Hook تغییر پکیج
/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/changepackage.php" \
    --category Whostmgr \
    --event Accounts::ChangePackage \
    --stage post \
    --hook "$INSTALL_DIR/hooks/changepackage.php" \
    --exectype script

# Hook تغییر IP
/usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/setsiteip.php" \
    --category Whostmgr \
    --event Accounts::SetSiteIP \
    --stage post \
    --hook "$INSTALL_DIR/hooks/setsiteip.php" \
    --exectype script

echo ""
echo "✅ نصب با موفقیت انجام شد!"
echo ""
echo "📝 مراحل بعدی:"
echo "1. از طریق WHM > Plugins > WHMCloudFlare وارد تنظیمات شوید"
echo "2. API Token یا API Key + Email Cloudflare را وارد کنید"
echo "3. Zone ID را تنظیم کنید"
echo "4. ماژول را فعال کنید"
echo ""
echo "📚 برای اطلاعات بیشتر به README.md مراجعه کنید"

