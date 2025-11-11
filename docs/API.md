# مستندات API - WHMCloudFlare

## مقدمه

این مستندات شامل توضیحات کامل API های داخلی WHMCloudFlare است.

## کلاس‌های اصلی

### CloudflareAPI

کلاس اصلی برای ارتباط با API Cloudflare.

#### متدها

##### `__construct($apiToken, $apiEmail, $apiKey)`
سازنده کلاس. می‌تواند از API Token یا API Key + Email استفاده کند.

**پارامترها:**
- `$apiToken` (string): API Token Cloudflare
- `$apiEmail` (string): Email Cloudflare (اگر از API Key استفاده می‌شود)
- `$apiKey` (string): API Key Cloudflare (اگر از API Key استفاده می‌شود)

##### `setZoneId($zoneId)`
تنظیم Zone ID برای عملیات بعدی.

##### `createDNSRecord($type, $name, $content, $ttl, $proxied, $priority)`
ایجاد رکورد DNS جدید.

**پارامترها:**
- `$type` (string): نوع رکورد (A, AAAA, CNAME, MX, TXT, ...)
- `$name` (string): نام رکورد
- `$content` (string): محتوای رکورد
- `$ttl` (int): TTL (پیش‌فرض: 1 = Auto)
- `$proxied` (bool): استفاده از Cloudflare Proxy (فقط A و AAAA)
- `$priority` (int): اولویت (برای MX)

**مثال:**
```php
$api = new CloudflareAPI($token);
$api->setZoneId('zone_id_here');
$result = $api->createDNSRecord('A', 'example.com', '192.0.2.1', 1, false);
```

##### `updateDNSRecord($recordId, $type, $name, $content, $ttl, $proxied, $priority)`
به‌روزرسانی رکورد DNS موجود.

##### `deleteDNSRecord($recordId)`
حذف رکورد DNS.

##### `findDNSRecord($name, $type)`
جستجوی رکورد DNS.

##### `listZones($name)`
دریافت لیست Zone ها.

---

### WHMCloudFlare

کلاس اصلی ماژول که منطق اصلی را مدیریت می‌کند.

#### متدها

##### `__construct($domain = null)`
سازنده کلاس. می‌تواند دامنه را برای انتخاب خودکار Zone دریافت کند.

##### `createAccountDNS($domain, $ip, $options = [])`
ایجاد رکوردهای DNS برای یک اکانت جدید.

**پارامترها:**
- `$domain` (string): دامنه
- `$ip` (string): آدرس IP
- `$options` (array): گزینه‌های اضافی (ipv6, user, ...)

**مثال:**
```php
$whmcf = new WHMCloudFlare();
$whmcf->createAccountDNS('example.com', '192.0.2.1', [
    'ipv6' => '2001:db8::1'
]);
```

##### `deleteAccountDNS($domain)`
حذف رکوردهای DNS یک اکانت.

##### `updateAccountIP($domain, $newIp, $ipv6 = null)`
به‌روزرسانی IP یک دامنه.

---

### ZoneManager

کلاس مدیریت چندین Zone.

#### متدها

##### `getZoneForDomain($domain)`
دریافت Zone ID برای یک دامنه.

##### `addZoneMapping($pattern, $zoneId)`
افزودن Zone mapping.

**مثال:**
```php
$zoneManager = new ZoneManager();
$zoneManager->addZoneMapping('*.example.com', 'zone_id_here');
```

---

### SSLManager

کلاس مدیریت SSL/TLS.

#### متدها

##### `setSSLMode($mode)`
تنظیم SSL Mode (off, flexible, full, strict).

##### `enableSSL($mode = 'full')`
فعال کردن SSL/TLS.

##### `setAlwaysUseHTTPS($enabled)`
تنظیم Always Use HTTPS.

---

## Hook ها

### createacct.php
Hook برای ایجاد اکانت جدید.

### removeacct.php
Hook برای حذف اکانت.

### changepackage.php
Hook برای تغییر پکیج.

### setsiteip.php
Hook برای تغییر IP سایت.

---

## خطاها

تمام خطاها به صورت Exception پرتاب می‌شوند. برای مدیریت خطاها:

```php
try {
    $whmcf = new WHMCloudFlare();
    $whmcf->createAccountDNS('example.com', '192.0.2.1');
} catch (Exception $e) {
    echo "خطا: " . $e->getMessage();
}
```

