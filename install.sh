#!/bin/bash

###############################################################################
# WHMCloudFlare - Automated Installation Script
# Version: 1.0.0
# Author: Hossein Abdinasab
# GitHub: https://github.com/hosseinabdinasab/WHMCloudFlare
###############################################################################

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# متغیرها
INSTALL_DIR="/usr/local/cpanel/whm/addons/WHMCloudFlare"
CURRENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCRIPT_NAME="WHMCloudFlare Installer"
VERSION="1.0.0"

###############################################################################
# توابع کمکی
###############################################################################

print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}  $SCRIPT_NAME v$VERSION${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_step() {
    echo ""
    echo -e "${BLUE}[STEP]${NC} $1"
    echo "----------------------------------------"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then 
        print_error "This script must be run as root"
        echo "Please run: sudo $0"
        exit 1
    fi
}

check_whm() {
    if [ ! -d "/usr/local/cpanel" ]; then
        print_error "WHM/cPanel is not installed on this server"
        exit 1
    fi
    
    WHM_VERSION=$(/usr/local/cpanel/bin/whmapi1 version | grep version | awk '{print $2}')
    if [ -z "$WHM_VERSION" ]; then
        print_warning "Could not detect WHM version, continuing anyway..."
    else
        print_success "WHM version detected: $WHM_VERSION"
    fi
}

check_php() {
    PHP_VERSION=$(php -v 2>/dev/null | head -n 1 | cut -d " " -f 2 | cut -c 1-3)
    if [ -z "$PHP_VERSION" ]; then
        print_error "PHP is not installed or not in PATH"
        exit 1
    fi
    
    REQUIRED_VERSION="7.4"
    if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
        print_warning "PHP version $PHP_VERSION detected. Recommended: PHP 7.4 or higher"
    else
        print_success "PHP version $PHP_VERSION detected"
    fi
}

check_curl() {
    if ! command -v curl &> /dev/null; then
        print_error "cURL is not installed"
        print_info "Installing cURL..."
        if command -v yum &> /dev/null; then
            yum install -y curl
        elif command -v apt-get &> /dev/null; then
            apt-get update && apt-get install -y curl
        else
            print_error "Could not install cURL automatically. Please install it manually."
            exit 1
        fi
    fi
    print_success "cURL is installed"
}

backup_existing() {
    if [ -d "$INSTALL_DIR" ]; then
        print_warning "Existing installation found at $INSTALL_DIR"
        BACKUP_DIR="${INSTALL_DIR}_backup_$(date +%Y%m%d_%H%M%S)"
        print_info "Creating backup to $BACKUP_DIR"
        cp -r "$INSTALL_DIR" "$BACKUP_DIR"
        print_success "Backup created successfully"
    fi
}

create_directories() {
    print_step "Creating directories"
    
    mkdir -p "$INSTALL_DIR"
    mkdir -p "$INSTALL_DIR/logs"
    mkdir -p "$INSTALL_DIR/config"
    mkdir -p "$INSTALL_DIR/cache"
    mkdir -p "$INSTALL_DIR/lang"
    
    print_success "Directories created"
}

copy_files() {
    print_step "Copying files"
    
    # کپی فایل‌ها
    cp -r "$CURRENT_DIR/lib" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/hooks" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/ui" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/cpanel" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/lang" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/docs" "$INSTALL_DIR/" 2>/dev/null
    cp -r "$CURRENT_DIR/install" "$INSTALL_DIR/" 2>/dev/null
    
    # کپی فایل‌های مستندات
    if [ -f "$CURRENT_DIR/README.md" ]; then
        cp "$CURRENT_DIR/README.md" "$INSTALL_DIR/"
    fi
    if [ -f "$CURRENT_DIR/LICENSE" ]; then
        cp "$CURRENT_DIR/LICENSE" "$INSTALL_DIR/"
    fi
    
    print_success "Files copied successfully"
}

set_permissions() {
    print_step "Setting permissions"
    
    # تنظیم دسترسی‌های اصلی
    chown -R root:root "$INSTALL_DIR"
    chmod -R 755 "$INSTALL_DIR"
    
    # دسترسی‌های خاص
    chmod 777 "$INSTALL_DIR/logs"
    chmod 777 "$INSTALL_DIR/config"
    chmod 777 "$INSTALL_DIR/cache"
    
    # دسترسی اجرایی برای اسکریپت‌ها
    chmod +x "$INSTALL_DIR/install/install.sh" 2>/dev/null
    chmod +x "$INSTALL_DIR/install/uninstall.sh" 2>/dev/null
    
    # دسترسی خواندن برای فایل‌های PHP
    find "$INSTALL_DIR" -type f -name "*.php" -exec chmod 644 {} \;
    
    print_success "Permissions set successfully"
}

register_hooks() {
    print_step "Registering WHM hooks"
    
    # حذف Hook های قدیمی (اگر وجود دارند)
    /usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/createacct.php" \
        --category Whostmgr --event Accounts::Create 2>/dev/null
    
    /usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/removeacct.php" \
        --category Whostmgr --event Accounts::Remove 2>/dev/null
    
    /usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/changepackage.php" \
        --category Whostmgr --event Accounts::ChangePackage 2>/dev/null
    
    /usr/local/cpanel/bin/manage_hooks delete script "$INSTALL_DIR/hooks/setsiteip.php" \
        --category Whostmgr --event Accounts::SetSiteIP 2>/dev/null
    
    # ثبت Hook های جدید
    print_info "Registering createacct hook..."
    /usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/createacct.php" \
        --category Whostmgr \
        --event Accounts::Create \
        --stage post \
        --hook "$INSTALL_DIR/hooks/createacct.php" \
        --exectype script
    
    print_info "Registering removeacct hook..."
    /usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/removeacct.php" \
        --category Whostmgr \
        --event Accounts::Remove \
        --stage post \
        --hook "$INSTALL_DIR/hooks/removeacct.php" \
        --exectype script
    
    print_info "Registering changepackage hook..."
    /usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/changepackage.php" \
        --category Whostmgr \
        --event Accounts::ChangePackage \
        --stage post \
        --hook "$INSTALL_DIR/hooks/changepackage.php" \
        --exectype script
    
    print_info "Registering setsiteip hook..."
    /usr/local/cpanel/bin/manage_hooks add script "$INSTALL_DIR/hooks/setsiteip.php" \
        --category Whostmgr \
        --event Accounts::SetSiteIP \
        --stage post \
        --hook "$INSTALL_DIR/hooks/setsiteip.php" \
        --exectype script
    
    print_success "Hooks registered successfully"
}

verify_installation() {
    print_step "Verifying installation"
    
    # بررسی وجود فایل‌های مهم
    local files=(
        "$INSTALL_DIR/lib/WHMCloudFlare.php"
        "$INSTALL_DIR/lib/CloudflareAPI.php"
        "$INSTALL_DIR/lib/Config.php"
        "$INSTALL_DIR/lib/Language.php"
        "$INSTALL_DIR/hooks/createacct.php"
        "$INSTALL_DIR/ui/index.php"
        "$INSTALL_DIR/lang/fa.php"
        "$INSTALL_DIR/lang/en.php"
    )
    
    local all_exist=true
    for file in "${files[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "Required file not found: $file"
            all_exist=false
        fi
    done
    
    if [ "$all_exist" = true ]; then
        print_success "All required files are present"
    else
        print_error "Some required files are missing"
        return 1
    fi
    
    # بررسی Hook های ثبت شده
    print_info "Checking registered hooks..."
    HOOK_COUNT=$(/usr/local/cpanel/bin/manage_hooks list | grep -c "WHMCloudFlare" || echo "0")
    if [ "$HOOK_COUNT" -ge 4 ]; then
        print_success "All hooks are registered ($HOOK_COUNT found)"
    else
        print_warning "Some hooks may not be registered properly"
    fi
}

create_config() {
    print_step "Creating default configuration"
    
    if [ ! -f "$INSTALL_DIR/config/settings.json" ]; then
        cat > "$INSTALL_DIR/config/settings.json" << 'EOF'
{
    "api_token": "",
    "api_email": "",
    "api_key": "",
    "zone_id": "",
    "zone_mapping": "{}",
    "auto_create_a": true,
    "auto_create_aaaa": false,
    "auto_create_www": true,
    "auto_create_mx": false,
    "auto_create_txt": false,
    "proxied": false,
    "ttl": 1,
    "mx_records": "[]",
    "txt_records": "[]",
    "enabled": false,
    "ssl_auto_manage": false,
    "ssl_mode": "full",
    "always_use_https": false,
    "min_tls_version": "1.2",
    "cache_enabled": true,
    "cache_ttl": 3600,
    "audit_enabled": true,
    "notification_email": "",
    "notification_enabled": false
}
EOF
        print_success "Default configuration created"
    else
        print_info "Configuration file already exists, skipping..."
    fi
}

test_php_syntax() {
    print_step "Testing PHP syntax"
    
    local php_files=$(find "$INSTALL_DIR" -name "*.php" -type f)
    local errors=0
    
    for file in $php_files; do
        if ! php -l "$file" > /dev/null 2>&1; then
            print_error "Syntax error in: $file"
            errors=$((errors + 1))
        fi
    done
    
    if [ $errors -eq 0 ]; then
        print_success "All PHP files have valid syntax"
    else
        print_warning "$errors PHP file(s) have syntax errors"
    fi
}

show_summary() {
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}  Installation Completed Successfully!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo -e "${BLUE}Installation Directory:${NC} $INSTALL_DIR"
    echo -e "${BLUE}Configuration File:${NC} $INSTALL_DIR/config/settings.json"
    echo -e "${BLUE}Logs Directory:${NC} $INSTALL_DIR/logs"
    echo ""
    echo -e "${YELLOW}Next Steps:${NC}"
    echo "1. Log in to WHM"
    echo "2. Go to: Plugins > WHMCloudFlare"
    echo "3. Enter your Cloudflare API Token or API Key + Email"
    echo "4. Set your Zone ID"
    echo "5. Enable the module"
    echo "6. Save settings"
    echo ""
    echo -e "${BLUE}Documentation:${NC}"
    echo "- Installation Guide: $INSTALL_DIR/INSTALL.md"
    echo "- API Documentation: $INSTALL_DIR/docs/API.md"
    echo "- FAQ: $INSTALL_DIR/docs/FAQ.md"
    echo ""
    echo -e "${GREEN}Thank you for using WHMCloudFlare!${NC}"
    echo ""
}

###############################################################################
# اجرای اصلی
###############################################################################

main() {
    print_header
    
    # بررسی‌های اولیه
    print_step "Checking prerequisites"
    check_root
    check_whm
    check_php
    check_curl
    
    # نصب
    print_step "Starting installation"
    backup_existing
    create_directories
    copy_files
    set_permissions
    create_config
    register_hooks
    test_php_syntax
    verify_installation
    
    # خلاصه
    show_summary
}

# اجرای اسکریپت
main "$@"

