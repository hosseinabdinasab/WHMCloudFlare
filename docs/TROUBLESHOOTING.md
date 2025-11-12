# راهنمای عیب‌یابی - WHMCloudFlare

## مشکلات رایج و راه‌حل‌ها

### 1. ماژول فعال نیست

**علائم:**
- رکوردها ایجاد نمی‌شوند
- Hook ها اجرا نمی‌شوند

**راه‌حل:**
1. وارد WHM شوید
2. به Plugins > WHMCloudFlare بروید
3. گزینه "فعال کردن ماژول" را فعال کنید
4. تنظیمات را ذخیره کنید

---

### 2. خطای اتصال به Cloudflare

**علائم:**
- خطای "خطا در ارتباط با Cloudflare"
- تست اتصال ناموفق است

**راه‌حل:**
1. بررسی کنید که API Token یا API Key معتبر است
2. بررسی کنید که سرور به اینترنت دسترسی دارد
3. بررسی کنید که Zone ID صحیح است
4. از دکمه "تست اتصال" در صفحه تنظیمات استفاده کنید

---

### 3. رکوردها ایجاد نمی‌شوند

**علائم:**
- اکانت ایجاد می‌شود اما رکوردهای DNS ایجاد نمی‌شوند

**راه‌حل:**
1. بررسی لاگ‌ها:
   ```bash
   tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/whmcloudflare-*.log
   ```

2. بررسی Hook ها:
   ```bash
   /usr/local/cpanel/bin/manage_hooks list | grep WHMCloudFlare
   ```

3. بررسی تنظیمات:
   - ماژول فعال است؟
   - Zone ID صحیح است؟
   - API Token/Key معتبر است؟

---

### 4. خطای "Zone ID تنظیم نشده است"

**علائم:**
- خطای Zone ID در لاگ‌ها

**راه‌حل:**
1. Zone ID را در تنظیمات وارد کنید
2. یا Zone Mapping را برای دامنه تنظیم کنید

---

### 5. رکوردهای تکراری

**علائم:**
- خطای "رکورد از قبل وجود دارد"

**راه‌حل:**
این خطا طبیعی است و نشان می‌دهد که ماژول به درستی کار می‌کند. اگر می‌خواهید رکورد را به‌روزرسانی کنید، ابتدا آن را حذف کنید.

---

### 6. Hook ها اجرا نمی‌شوند

**علائم:**
- هیچ عملیاتی انجام نمی‌شود
- لاگ‌ها خالی هستند

**راه‌حل:**
1. بررسی ثبت Hook ها:
   ```bash
   /usr/local/cpanel/bin/manage_hooks list | grep WHMCloudFlare
   ```
   باید 4 Hook نمایش داده شود.

2. اگر Hook ها ثبت نشده‌اند، نصب کننده خودکار را دوباره اجرا کنید:
   ```bash
   cd /usr/local/cpanel/whm/addons/WHMCloudFlare
   sudo ./install.sh
   ```
   
   یا اگر در ریشه پروژه هستید:
   ```bash
   sudo ./install.sh
   ```

3. بررسی دسترسی فایل‌ها:
   ```bash
   ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/
   chmod +x /usr/local/cpanel/whm/addons/WHMCloudFlare/hooks/*.php
   ```

---

### 7. خطای رمزگشایی

**علائم:**
- خطای "خطا در رمزگشایی"
- تنظیمات ذخیره نمی‌شوند

**راه‌حل:**
1. بررسی دسترسی فایل کلید:
   ```bash
   ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/config/.encryption_key
   ```

2. اگر فایل وجود ندارد، تنظیمات را دوباره ذخیره کنید

---

### 8. Cache مشکلات ایجاد می‌کند

**علائم:**
- تغییرات اعمال نمی‌شوند
- داده‌های قدیمی نمایش داده می‌شوند

**راه‌حل:**
1. Cache را در تنظیمات غیرفعال کنید
2. یا Cache را پاک کنید:
   ```bash
   rm -rf /usr/local/cpanel/whm/addons/WHMCloudFlare/cache/*
   ```

---

### 9. مشکلات SSL/TLS

**علائم:**
- SSL تنظیم نمی‌شود
- خطای SSL در Cloudflare

**راه‌حل:**
1. بررسی کنید که "مدیریت خودکار SSL/TLS" فعال است
2. بررسی SSL Mode انتخاب شده
3. بررسی لاگ‌ها برای خطاهای SSL

---

### 10. مشکلات Performance

**علائم:**
- عملیات کند است
- Timeout می‌شود

**راه‌حل:**
1. Cache را فعال کنید
2. Retry mechanism فعال است (به صورت پیش‌فرض فعال است)
3. بررسی سرعت اینترنت سرور
4. بررسی Rate Limiting در Cloudflare

---

## بررسی لاگ‌ها

### لاگ‌های اصلی
```bash
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/whmcloudflare-*.log
```

### Audit Log
```bash
tail -f /usr/local/cpanel/whm/addons/WHMCloudFlare/logs/audit.log
```

### لاگ‌های cPanel
```bash
tail -f /usr/local/cpanel/logs/error_log
```

---

## تست دستی

### تست اتصال
```php
<?php
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
$whmcf = new WHMCloudFlare();
$result = $whmcf->testConnection();
print_r($result);
?>
```

### تست ایجاد رکورد
```php
<?php
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
$whmcf = new WHMCloudFlare('example.com');
$whmcf->createAccountDNS('example.com', '192.0.2.1');
?>
```

---

## تماس با پشتیبانی

اگر مشکل شما حل نشد:
1. لاگ‌ها را جمع‌آوری کنید
2. جزئیات مشکل را بنویسید
3. Issue در مخزن پروژه ایجاد کنید

