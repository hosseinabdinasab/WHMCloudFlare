<?php
/**
 * Hook: ایجاد اکانت جدید
 * 
 * این Hook هنگام ایجاد اکانت جدید در WHM اجرا می‌شود
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';

// دریافت اطلاعات از stdin (WHM Hook ها داده را از stdin می‌فرستند)
$input = file_get_contents('php://stdin');
$data = json_decode($input, true);

// اگر JSON نبود، از متغیرهای محیطی استفاده کن
if (!$data) {
    $data = $_ENV;
}

$domain = $data['domain'] ?? $data['newdomain'] ?? null;
$ip = $data['ip'] ?? $data['newip'] ?? null;
$user = $data['user'] ?? $data['username'] ?? null;

if (empty($domain) || empty($ip)) {
    exit(0); // اگر اطلاعات کافی نیست، خروج بدون خطا
}

try {
    $whmcf = new WHMCloudFlare();
    
    // بررسی فعال بودن ماژول
    $config = new Config();
    if (!$config->isEnabled()) {
        exit(0);
    }
    
    // دریافت IPv6 اگر موجود باشد
    $ipv6 = $data['ipv6'] ?? null;
    
    // ایجاد رکوردهای DNS
    $whmcf->createAccountDNS($domain, $ip, [
        'ipv6' => $ipv6,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    // در صورت خطا، فقط لاگ می‌کنیم و ادامه می‌دهیم
    error_log("WHMCloudFlare Error (createacct): " . $e->getMessage());
}

exit(0);

