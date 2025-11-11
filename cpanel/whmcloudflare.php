<?php
/**
 * رابط کاربری cPanel برای WHMCloudFlare
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Logger.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Language.php';

// بررسی دسترسی کاربر
if (!isset($_SESSION['user'])) {
    $lang = Language::getInstance();
    die($lang->translate('permission_denied'));
}

// بارگذاری سیستم زبان
$lang = Language::getInstance();

// پردازش تغییر زبان
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'])) {
    $lang->setLanguage($_GET['lang']);
    header('Location: ' . str_replace('?lang=' . $_GET['lang'], '', $_SERVER['REQUEST_URI']));
    exit;
}

$config = new Config();
$settings = $config->getSettings();

// دریافت دامنه‌های کاربر
$user = $_SESSION['user'];
$domains = [];

// دریافت دامنه‌های کاربر از cPanel API
if (function_exists('cpanel_api_request')) {
    $result = cpanel_api_request('DomainInfo', 'list_domains', []);
    if ($result && isset($result['data'])) {
        $domains = $result['data'];
    }
}

// پردازش درخواست‌ها
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $action;
    
    try {
        $whmcf = new WHMCloudFlare();
        
        switch ($action) {
            case 'view_records':
                $domain = $_POST['domain'] ?? null;
                if ($domain) {
                    $zoneId = $whmcf->getZoneForDomain($domain);
                    if ($zoneId) {
                        $whmcf->cloudflare->setZoneId($zoneId);
                        $records = $whmcf->cloudflare->listDNSRecords();
                    }
                }
                break;
        }
    } catch (Exception $e) {
        $message = $lang->translate('error') . ': ' . $e->getMessage();
        $messageType = 'error';
    }
}

// نمایش رابط کاربری
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>" dir="<?php echo $lang->getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo $lang->getCurrentLanguage() === 'fa' ? 'مدیریت DNS' : 'DNS Management'; ?></title>
    <link rel="stylesheet" href="/cpanel/whmcloudflare.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="position: absolute; top: 20px; left: 20px;">
                <select id="languageSelect" onchange="changeLanguage(this.value)" style="padding: 8px; border-radius: 5px; border: none; background: rgba(255,255,255,0.2); color: white;">
                    <option value="fa" <?php echo $lang->getCurrentLanguage() === 'fa' ? 'selected' : ''; ?>>فارسی</option>
                    <option value="en" <?php echo $lang->getCurrentLanguage() === 'en' ? 'selected' : ''; ?>>English</option>
                </select>
            </div>
            <h1>🌐 <?php echo __('app_name'); ?></h1>
            <p><?php echo __('app_description'); ?></p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <div class="section">
                <h2><?php echo $lang->getCurrentLanguage() === 'fa' ? 'دامنه‌های شما' : 'Your Domains'; ?></h2>
                <div class="domains-list">
                    <?php if (empty($domains)): ?>
                        <p><?php echo $lang->getCurrentLanguage() === 'fa' ? 'دامنه‌ای یافت نشد' : 'No domains found'; ?></p>
                    <?php else: ?>
                        <?php foreach ($domains as $domain): ?>
                            <div class="domain-card">
                                <h3><?php echo htmlspecialchars($domain); ?></h3>
                                <button onclick="viewRecords('<?php echo htmlspecialchars($domain); ?>')">
                                    <?php echo $lang->getCurrentLanguage() === 'fa' ? 'مشاهده رکوردها' : 'View Records'; ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function changeLanguage(lang) {
            window.location.href = '?lang=' + lang;
        }
        
        function viewRecords(domain) {
            window.location.href = '?action=view_records&domain=' + encodeURIComponent(domain);
        }
    </script>
</body>
</html>

