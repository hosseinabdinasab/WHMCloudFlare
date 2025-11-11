<?php
/**
 * رابط کاربری اصلی WHMCloudFlare
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Logger.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Language.php';

// بررسی دسترسی ادمین
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'root') {
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
$logger = new Logger();
$settings = $config->getSettings();
$message = '';
$messageType = '';

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_settings':
                $newSettings = [
                    'api_token' => $_POST['api_token'] ?? '',
                    'api_email' => $_POST['api_email'] ?? '',
                    'api_key' => $_POST['api_key'] ?? '',
                    'zone_id' => $_POST['zone_id'] ?? '',
                    'auto_create_a' => isset($_POST['auto_create_a']),
                    'auto_create_aaaa' => isset($_POST['auto_create_aaaa']),
                    'auto_create_www' => isset($_POST['auto_create_www']),
                    'auto_create_mx' => isset($_POST['auto_create_mx']),
                    'auto_create_txt' => isset($_POST['auto_create_txt']),
                    'proxied' => isset($_POST['proxied']),
                    'ttl' => intval($_POST['ttl'] ?? 1),
                    'mx_records' => $_POST['mx_records'] ?? '[]',
                    'txt_records' => $_POST['txt_records'] ?? '[]',
                    'enabled' => isset($_POST['enabled'])
                ];
                
                if ($config->saveSettings($newSettings)) {
                    $message = __('settings_saved');
                    $messageType = 'success';
                    $settings = $config->getSettings();
                } else {
                    $message = __('settings_save_error');
                    $messageType = 'error';
                }
                break;
                
            case 'test_connection':
                try {
                    $whmcf = new WHMCloudFlare();
                    $result = $whmcf->testConnection();
                    
                    if ($result['success']) {
                        $message = __('connection_success');
                        $messageType = 'success';
                    } else {
                        $message = __('connection_error') . ': ' . $result['message'];
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = __('error') . ': ' . $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'get_zones':
                try {
                    $whmcf = new WHMCloudFlare();
                    $zones = $whmcf->getZones();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'zones' => $zones]);
                    exit;
                } catch (Exception $e) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                break;
        }
    }
}

// دریافت لاگ‌ها
$logs = $logger->readLogs(50);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>" dir="<?php echo $lang->getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?> - <?php echo __('settings'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-left: 10px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .logs {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .logs .log-entry {
            margin-bottom: 5px;
            padding: 5px;
        }
        
        .logs .log-entry.INFO {
            color: #4ec9b0;
        }
        
        .logs .log-entry.WARNING {
            color: #dcdcaa;
        }
        
        .logs .log-entry.ERROR {
            color: #f48771;
        }
    </style>
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
        
        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="save_settings">
                
                <!-- تنظیمات اتصال -->
                <div class="section">
                    <h2>⚙️ <?php echo __('connection_settings'); ?></h2>
                    
                    <div class="form-group">
                        <label><?php echo __('api_token'); ?> (<?php echo $lang->getCurrentLanguage() === 'fa' ? 'ترجیحاً' : 'Preferred'; ?>)</label>
                        <input type="text" name="api_token" value="<?php echo htmlspecialchars($settings['api_token']); ?>" placeholder="<?php echo __('api_token'); ?>">
                        <div class="help-text"><?php echo __('api_token_help'); ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo $lang->getCurrentLanguage() === 'fa' ? 'یا ' : 'Or '; ?><?php echo __('api_email'); ?> (<?php echo $lang->getCurrentLanguage() === 'fa' ? 'اگر از API Key استفاده می‌کنید' : 'if using API Key'; ?>)</label>
                        <input type="email" name="api_email" value="<?php echo htmlspecialchars($settings['api_email']); ?>" placeholder="email@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo __('api_key'); ?> (<?php echo $lang->getCurrentLanguage() === 'fa' ? 'اگر از API Key استفاده می‌کنید' : 'if using API Key'; ?>)</label>
                        <input type="text" name="api_key" value="<?php echo htmlspecialchars($settings['api_key']); ?>" placeholder="<?php echo __('api_key'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo __('zone_id'); ?></label>
                        <input type="text" name="zone_id" value="<?php echo htmlspecialchars($settings['zone_id']); ?>" placeholder="<?php echo __('zone_id'); ?>">
                        <button type="button" class="btn btn-secondary" onclick="loadZones()"><?php echo __('load_zones'); ?></button>
                        <div class="help-text"><?php echo __('zone_id_help'); ?></div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary" onclick="testConnection()"><?php echo __('test_connection'); ?></button>
                </div>
                
                <!-- تنظیمات خودکارسازی -->
                <div class="section">
                    <h2>🤖 <?php echo __('automation_settings'); ?></h2>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="enabled" id="enabled" <?php echo $settings['enabled'] ? 'checked' : ''; ?>>
                        <label for="enabled"><?php echo __('enable_module'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="auto_create_a" id="auto_create_a" <?php echo $settings['auto_create_a'] ? 'checked' : ''; ?>>
                        <label for="auto_create_a"><?php echo __('auto_create_a'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="auto_create_aaaa" id="auto_create_aaaa" <?php echo $settings['auto_create_aaaa'] ? 'checked' : ''; ?>>
                        <label for="auto_create_aaaa"><?php echo __('auto_create_aaaa'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="auto_create_www" id="auto_create_www" <?php echo $settings['auto_create_www'] ? 'checked' : ''; ?>>
                        <label for="auto_create_www"><?php echo __('auto_create_www'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="auto_create_mx" id="auto_create_mx" <?php echo $settings['auto_create_mx'] ? 'checked' : ''; ?>>
                        <label for="auto_create_mx"><?php echo __('auto_create_mx'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="auto_create_txt" id="auto_create_txt" <?php echo $settings['auto_create_txt'] ? 'checked' : ''; ?>>
                        <label for="auto_create_txt"><?php echo __('auto_create_txt'); ?></label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="proxied" id="proxied" <?php echo $settings['proxied'] ? 'checked' : ''; ?>>
                        <label for="proxied"><?php echo __('proxied'); ?></label>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo __('ttl'); ?></label>
                        <input type="number" name="ttl" value="<?php echo $settings['ttl']; ?>" min="1">
                        <div class="help-text"><?php echo __('ttl_help'); ?></div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 <?php echo __('save'); ?> <?php echo __('settings'); ?></button>
            </form>
            
            <!-- لاگ‌ها -->
            <div class="section">
                <h2>📋 <?php echo __('recent_logs'); ?></h2>
                <div class="logs">
                    <?php if (empty($logs)): ?>
                        <div><?php echo $lang->getCurrentLanguage() === 'fa' ? 'لاگی وجود ندارد' : 'No logs available'; ?></div>
                    <?php else: ?>
                        <?php foreach (array_reverse($logs) as $log): ?>
                            <div class="log-entry <?php echo strpos($log, '[ERROR]') !== false ? 'ERROR' : (strpos($log, '[WARNING]') !== false ? 'WARNING' : 'INFO'); ?>">
                                <?php echo htmlspecialchars($log); ?>
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
        
        function testConnection() {
            const confirmMsg = '<?php echo $lang->getCurrentLanguage() === 'fa' ? 'آیا می‌خواهید اتصال به Cloudflare را تست کنید؟' : 'Do you want to test connection to Cloudflare?'; ?>';
            if (!confirm(confirmMsg)) {
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="test_connection">';
            document.body.appendChild(form);
            form.submit();
        }
        
        function loadZones() {
            fetch('?action=get_zones', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_zones'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let zones = data.zones.map(z => `${z.name} (${z.id})`).join('\n');
                    const alertMsg = '<?php echo $lang->getCurrentLanguage() === 'fa' ? 'Zone های موجود:' : 'Available Zones:'; ?>';
                    alert(alertMsg + '\n\n' + zones);
                } else {
                    const errorMsg = '<?php echo __('error'); ?>';
                    alert(errorMsg + ': ' + data.message);
                }
            })
            .catch(error => {
                const errorMsg = '<?php echo $lang->getCurrentLanguage() === 'fa' ? 'خطا در بارگذاری Zone ها' : 'Error loading zones'; ?>';
                alert(errorMsg);
            });
        }
    </script>
</body>
</html>

