# راهنمای نصب WHMCloudFlare

## پیش‌نیازها

- WHM/cPanel نسخه 11.80 یا بالاتر
- PHP 7.4 یا بالاتر
- دسترسی root به سرور
- API Token یا API Key + Email از Cloudflare
- دسترسی به اینترنت برای ارتباط با API Cloudflare

## مراحل نصب

### روش 1: نصب خودکار (توصیه می‌شود) ⭐

این روش ساده‌ترین و سریع‌ترین روش نصب است:

```bash
# 1. Clone یا دانلود پروژه
git clone https://github.com/hosseinabdinasab/WHMCloudFlare.git
cd WHMCloudFlare

# 2. اجرای نصب کننده خودکار
chmod +x install.sh
sudo ./install.sh
```

**نصب کننده خودکار چه کارهایی انجام می‌دهد:**
- ✅ بررسی پیش‌نیازها (WHM, PHP, cURL)
- ✅ ایجاد Backup از نصب قبلی (در صورت وجود)
- ✅ ایجاد دایرکتوری‌های لازم
- ✅ کپی تمام فایل‌ها
- ✅ تنظیم دسترسی‌های مناسب
- ✅ ایجاد تنظیمات پیش‌فرض
- ✅ ثبت Hook های WHM
- ✅ تست syntax فایل‌های PHP
- ✅ اعتبارسنجی نصب

**مزایای نصب خودکار:**
- 🚀 سریع و آسان
- 🛡️ بررسی خودکار پیش‌نیازها
- 💾 Backup خودکار
- ✅ تست و اعتبارسنجی
- 📊 نمایش پیشرفت با رنگ‌ها

### روش 2: نصب دستی

اگر می‌خواهید به صورت دستی نصب کنید:

```bash
# 1. کپی فایل‌های پروژه به دایرکتوری مناسب
cp -r WHMCloudFlare /usr/local/cpanel/whm/addons/

# 2. اجرای اسکریپت نصب
cd /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod +x install/install.sh
./install/install.sh
```

### 3. تنظیم دسترسی‌ها

**نکته:** اگر از نصب کننده خودکار استفاده کرده‌اید، این مرحله به صورت خودکار انجام شده است.

```bash
chmod -R 755 /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/logs
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/config
chmod 777 /usr/local/cpanel/whm/addons/WHMCloudFlare/cache
```

### 4. دریافت API Token از Cloudflare

1. وارد حساب Cloudflare خود شوید
2. به بخش **My Profile** > **API Tokens** بروید
3. روی **Create Token** کلیک کنید
4. از Template **Edit zone DNS** استفاده کنید
5. Zone را انتخاب کنید
6. Token را کپی کنید (فقط یک بار نمایش داده می‌شود!)

**یا** می‌توانید از API Key استفاده کنید:
- **Email**: ایمیل حساب Cloudflare
- **Global API Key**: از بخش **My Profile** > **API Tokens** > **Global API Key**

### 5. دریافت Zone ID

1. وارد Cloudflare Dashboard شوید
2. دامنه مورد نظر را انتخاب کنید
3. در بخش **Overview**، Zone ID را در سمت راست پیدا کنید

### 6. تنظیمات در WHM

1. وارد WHM شوید
2. به بخش **Plugins** > **WHMCloudFlare** بروید
3. اطلاعات زیر را وارد کنید:
   - **API Token** (یا API Email + API Key)
   - **Zone ID**
4. تنظیمات خودکارسازی را فعال کنید
5. روی **ذخیره تنظیمات** کلیک کنید
6. **تست اتصال** را انجام دهید

### 7. فعال کردن ماژول

در صفحه تنظیمات، گزینه **فعال کردن ماژول** را فعال کنید.

## تست نصب

### بررسی نصب

پس از نصب، می‌توانید نصب را بررسی کنید:

```bash
# بررسی Hook های ثبت شده
/usr/local/cpanel/bin/manage_hooks list | grep WHMCloudFlare

# بررسی فایل‌های نصب شده
ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/

# بررسی لاگ‌ها
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/*.log
```

### تست عملکرد

برای تست عملکرد:

1. یک اکانت تست در WHM ایجاد کنید
2. بررسی کنید که رکوردهای DNS در Cloudflare ایجاد شده‌اند
3. لاگ‌ها را در صفحه تنظیمات بررسی کنید
4. از داشبورد آماری برای مشاهده آمار استفاده کنید

## حذف نصب

### روش 1: استفاده از اسکریپت حذف خودکار

```bash
cd /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod +x install/uninstall.sh
sudo ./install/uninstall.sh
```

**نکته:** اسکریپت حذف به صورت خودکار:
- ✅ تنظیمات را Backup می‌کند
- ✅ Hook های WHM را حذف می‌کند
- ✅ فایل‌ها را حذف می‌کند

### روش 2: حذف دستی

```bash
# حذف Hook ها
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/createacct.php --category Whostmgr --event Accounts::Create
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/removeacct.php --category Whostmgr --event Accounts::Remove
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/changepackage.php --category Whostmgr --event Accounts::ChangePackage
/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/setsiteip.php --category Whostmgr --event Accounts::SetSiteIP

# حذف فایل‌ها
rm -rf /usr/local/cpanel/whm/addons/WHMCloudFlare
```

## عیب‌یابی

### مشکل: Hook ها اجرا نمی‌شوند

```bash
# بررسی Hook های ثبت شده
/usr/local/cpanel/bin/manage_hooks list

# بررسی لاگ‌ها
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/whmcloudflare-*.log
```

### مشکل: خطای اتصال به Cloudflare

- بررسی کنید که API Token یا API Key معتبر است
- بررسی کنید که Zone ID صحیح است
- بررسی کنید که سرور به اینترنت دسترسی دارد
- از دکمه **تست اتصال** در صفحه تنظیمات استفاده کنید

### مشکل: رکوردها ایجاد نمی‌شوند

- بررسی کنید که ماژول فعال است
- بررسی لاگ‌ها برای خطاها
- بررسی کنید که Zone ID صحیح است
- بررسی کنید که دامنه در Zone انتخاب شده وجود دارد

## پشتیبانی

برای گزارش مشکل یا پیشنهاد ویژگی جدید، لطفاً Issue در مخزن پروژه ایجاد کنید.

