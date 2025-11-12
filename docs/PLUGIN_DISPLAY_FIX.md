# راهنمای رفع مشکل نمایش پلاگین در لیست WHM

## مشکل
پلاگین WHMCloudFlare در لیست پلاگین‌های WHM نمایش داده نمی‌شود.

## راه‌حل‌ها

### روش 1: بررسی فایل پیکربندی

```bash
# بررسی وجود فایل
ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/whmcloudflare.cpanel.yml

# بررسی محتوای فایل
cat /usr/local/cpanel/whm/addons/WHMCloudFlare/whmcloudflare.cpanel.yml
```

### روش 2: کپی دستی فایل پیکربندی

```bash
# اگر فایل وجود ندارد، کپی کنید
cp /path/to/WHMCloudFlare/whmcloudflare.cpanel.yml /usr/local/cpanel/whm/addons/WHMCloudFlare/
chmod 644 /usr/local/cpanel/whm/addons/WHMCloudFlare/whmcloudflare.cpanel.yml
```

### روش 3: دسترسی مستقیم به رابط کاربری

اگر پلاگین در لیست نمایش داده نمی‌شود، می‌توانید مستقیماً به رابط کاربری دسترسی داشته باشید:

```
https://your-server-ip:2087/whm/addons/WHMCloudFlare/ui/index.php
```

یا:

```
https://your-server-ip:2087/cgi-bin/whmcloudflare.cgi
```

### روش 4: ایجاد لینک مستقیم در WHM

می‌توانید یک لینک مستقیم در WHM ایجاد کنید:

1. وارد WHM شوید
2. به **Home** > **Addon Modules** بروید
3. روی **Create an Addon Module** کلیک کنید
4. اطلاعات زیر را وارد کنید:
   - **Display Name**: WHMCloudFlare
   - **Link**: `/usr/local/cpanel/whm/addons/WHMCloudFlare/ui/index.php`
   - **Icon**: (اختیاری)
5. ذخیره کنید

### روش 5: استفاده از دستور WHMAPI

```bash
# بررسی پلاگین‌های ثبت شده
/usr/local/cpanel/bin/whmapi1 list_addons

# ثبت دستی پلاگین
/usr/local/cpanel/bin/whmapi1 register_addon name=WHMCloudFlare displayname="WHMCloudFlare" link="/usr/local/cpanel/whm/addons/WHMCloudFlare/ui/index.php"
```

### روش 6: Refresh کش WHM

```bash
# پاک کردن کش WHM
/usr/local/cpanel/bin/whmapi1 clear_cache

# یا
/scripts/rebuild_cpanel_cache
```

### روش 7: بررسی دسترسی فایل

```bash
# بررسی دسترسی‌ها
ls -la /usr/local/cpanel/whm/addons/WHMCloudFlare/

# تنظیم دسترسی‌های صحیح
chmod 755 /usr/local/cpanel/whm/addons/WHMCloudFlare
chmod 644 /usr/local/cpanel/whm/addons/WHMCloudFlare/whmcloudflare.cpanel.yml
chmod 644 /usr/local/cpanel/whm/addons/WHMCloudFlare/ui/index.php
```

## بررسی نهایی

پس از اعمال تغییرات:

1. از WHM خارج شوید
2. دوباره وارد شوید
3. به بخش **Plugins** بروید
4. پلاگین WHMCloudFlare باید نمایش داده شود

## دسترسی مستقیم

اگر هنوز نمایش داده نمی‌شود، می‌توانید مستقیماً از URL زیر استفاده کنید:

```
https://YOUR_SERVER_IP:2087/whm/addons/WHMCloudFlare/ui/index.php
```

یا از طریق SSH:

```bash
# ایجاد لینک نمادین
ln -s /usr/local/cpanel/whm/addons/WHMCloudFlare/ui/index.php /usr/local/cpanel/whm/addons/WHMCloudFlare/index.php
```

سپس از این URL استفاده کنید:

```
https://YOUR_SERVER_IP:2087/whm/addons/WHMCloudFlare/index.php
```

