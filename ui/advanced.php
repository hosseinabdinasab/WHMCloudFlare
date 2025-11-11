<?php
/**
 * رابط کاربری پیشرفته با Dark mode و Export/Import
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Language.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'root') {
    die('دسترسی غیرمجاز');
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
$darkMode = $_COOKIE['dark_mode'] ?? 'false';

// پردازش Export/Import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'export_settings':
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="whmcloudflare-settings-' . date('Y-m-d') . '.json"');
                echo json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
                
            case 'import_settings':
                if (isset($_FILES['settings_file']) && $_FILES['settings_file']['error'] === UPLOAD_ERR_OK) {
                    $content = file_get_contents($_FILES['settings_file']['tmp_name']);
                    $imported = json_decode($content, true);
                    if ($imported) {
                        // ادغام با تنظیمات فعلی (بدون بازنویسی کامل)
                        $merged = array_merge($settings, $imported);
                        if ($config->saveSettings($merged)) {
                            $message = __('import_success');
                            $messageType = 'success';
                            $settings = $config->getSettings();
                        } else {
                            $message = __('import_error');
                            $messageType = 'error';
                        }
                    } else {
                        $message = __('invalid_file');
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>" dir="<?php echo $lang->getDirection(); ?>" data-theme="<?php echo $darkMode === 'true' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('advanced'); ?> - <?php echo __('app_name'); ?></title>
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
            --accent: #667eea;
            --accent-secondary: #764ba2;
        }
        
        [data-theme="dark"] {
            --bg-primary: #1e1e1e;
            --bg-secondary: #2d2d2d;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --border-color: #404040;
            --accent: #667eea;
            --accent-secondary: #764ba2;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.3s, color 0.3s;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-secondary) 100%);
            padding: 20px;
            min-height: 100vh;
            color: var(--text-primary);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg-primary);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-secondary) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .theme-toggle {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
            background: var(--bg-primary);
        }
        
        .section {
            margin-bottom: 40px;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-secondary) 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 10px;">
                <select id="languageSelect" onchange="changeLanguage(this.value)" style="padding: 8px; border-radius: 5px; border: none; background: rgba(255,255,255,0.2); color: white;">
                    <option value="fa" <?php echo $lang->getCurrentLanguage() === 'fa' ? 'selected' : ''; ?>>فارسی</option>
                    <option value="en" <?php echo $lang->getCurrentLanguage() === 'en' ? 'selected' : ''; ?>>English</option>
                </select>
                <button class="theme-toggle" onclick="toggleTheme()">
                    🌓 <?php echo __('toggle_theme'); ?>
                </button>
            </div>
            <h1>⚙️ <?php echo __('advanced'); ?></h1>
            <p><?php echo __('export'); ?>/<?php echo __('import'); ?> <?php echo __('settings'); ?></p>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>📤 <?php echo __('export_settings'); ?></h2>
                <p><?php echo __('export_description'); ?></p>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="export_settings">
                    <button type="submit" class="btn btn-primary"><?php echo __('download_settings'); ?></button>
                </form>
            </div>
            
            <div class="section">
                <h2>📥 <?php echo __('import_settings'); ?></h2>
                <p><?php echo __('import_description'); ?></p>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_settings">
                    <div class="form-group">
                        <label><?php echo __('select_file'); ?>:</label>
                        <input type="file" name="settings_file" accept=".json" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo __('import'); ?> <?php echo __('settings'); ?></button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function changeLanguage(lang) {
            window.location.href = '?lang=' + lang;
        }
        
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            document.cookie = 'dark_mode=' + (newTheme === 'dark' ? 'true' : 'false') + '; path=/';
        }
    </script>
</body>
</html>

