<?php
/**
 * Hook: حذف اکانت
 * 
 * این Hook هنگام حذف اکانت از WHM اجرا می‌شود
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';

// دریافت اطلاعات از stdin
$input = file_get_contents('php://stdin');
$data = json_decode($input, true);

if (!$data) {
    $data = $_ENV;
}

$domain = $data['domain'] ?? null;

if (empty($domain)) {
    exit(0);
}

try {
    $whmcf = new WHMCloudFlare();
    
    // بررسی فعال بودن ماژول
    $config = new Config();
    if (!$config->isEnabled()) {
        exit(0);
    }
    
    // حذف رکوردهای DNS
    $whmcf->deleteAccountDNS($domain);
    
} catch (Exception $e) {
    error_log("WHMCloudFlare Error (removeacct): " . $e->getMessage());
}

exit(0);

