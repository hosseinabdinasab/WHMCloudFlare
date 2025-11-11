<?php
/**
 * Hook: تغییر پکیج
 * 
 * این Hook هنگام تغییر پکیج یک اکانت اجرا می‌شود
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';

$input = file_get_contents('php://stdin');
$data = json_decode($input, true);

if (!$data) {
    $data = $_ENV;
}

$domain = $data['domain'] ?? null;
$ip = $data['ip'] ?? $data['newip'] ?? null;

if (empty($domain) || empty($ip)) {
    exit(0);
}

try {
    $whmcf = new WHMCloudFlare();
    
    $config = new Config();
    if (!$config->isEnabled()) {
        exit(0);
    }
    
    // به‌روزرسانی IP در صورت تغییر
    $ipv6 = $data['ipv6'] ?? null;
    $whmcf->updateAccountIP($domain, $ip, $ipv6);
    
} catch (Exception $e) {
    error_log("WHMCloudFlare Error (changepackage): " . $e->getMessage());
}

exit(0);

