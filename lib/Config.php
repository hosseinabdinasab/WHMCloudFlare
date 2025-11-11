<?php
/**
 * کلاس مدیریت تنظیمات
 */

class Config {
    private $configFile;
    
    public function __construct() {
        $this->configFile = __DIR__ . '/../config/settings.json';
        
        // ایجاد دایرکتوری config اگر وجود ندارد
        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        // ایجاد فایل تنظیمات پیش‌فرض اگر وجود ندارد
        if (!file_exists($this->configFile)) {
            $this->saveSettings($this->getDefaultSettings());
        }
    }
    
    /**
     * دریافت تنظیمات پیش‌فرض
     */
    private function getDefaultSettings() {
        return [
            'api_token' => '',
            'api_email' => '',
            'api_key' => '',
            'zone_id' => '',
            'zone_mapping' => '{}', // برای پشتیبانی از چندین Zone
            'auto_create_a' => true,
            'auto_create_aaaa' => false,
            'auto_create_www' => true,
            'auto_create_mx' => false,
            'auto_create_txt' => false,
            'proxied' => false,
            'ttl' => 1, // Auto
            'mx_records' => '[]',
            'txt_records' => '[]',
            'enabled' => false,
            'ssl_auto_manage' => false,
            'ssl_mode' => 'full',
            'always_use_https' => false,
            'min_tls_version' => '1.2',
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'audit_enabled' => true,
            'notification_email' => '',
            'notification_enabled' => false
        ];
    }
    
    /**
     * دریافت تنظیمات (با رمزگشایی اطلاعات حساس)
     */
    public function getSettings() {
        if (!file_exists($this->configFile)) {
            return $this->getDefaultSettings();
        }
        
        $content = file_get_contents($this->configFile);
        $settings = json_decode($content, true);
        
        if (!$settings) {
            return $this->getDefaultSettings();
        }
        
        // ادغام با تنظیمات پیش‌فرض برای اطمینان از وجود تمام کلیدها
        $settings = array_merge($this->getDefaultSettings(), $settings);
        
        // رمزگشایی اطلاعات حساس
        require_once __DIR__ . '/Security.php';
        $security = new Security();
        
        if (!empty($settings['api_token']) && $this->isEncrypted($settings['api_token'])) {
            try {
                $settings['api_token'] = $security->decrypt($settings['api_token']);
            } catch (Exception $e) {
                // اگر رمزگشایی ناموفق بود، مقدار را خالی کن
                $settings['api_token'] = '';
            }
        }
        
        if (!empty($settings['api_key']) && $this->isEncrypted($settings['api_key'])) {
            try {
                $settings['api_key'] = $security->decrypt($settings['api_key']);
            } catch (Exception $e) {
                $settings['api_key'] = '';
            }
        }
        
        return $settings;
    }
    
    /**
     * بررسی اینکه آیا داده رمزنگاری شده است
     */
    private function isEncrypted($data) {
        // داده‌های رمزنگاری شده با base64 شروع می‌شوند و شامل :: هستند
        return strpos($data, '::') !== false && base64_decode($data, true) !== false;
    }
    
    /**
     * ذخیره تنظیمات (با رمزنگاری اطلاعات حساس)
     */
    public function saveSettings($settings) {
        require_once __DIR__ . '/Security.php';
        $security = new Security();
        
        // رمزنگاری اطلاعات حساس
        if (!empty($settings['api_token'])) {
            $settings['api_token'] = $security->encrypt($settings['api_token']);
        }
        if (!empty($settings['api_key'])) {
            $settings['api_key'] = $security->encrypt($settings['api_key']);
        }
        
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->configFile, $json) !== false;
    }
    
    /**
     * به‌روزرسانی یک تنظیم خاص
     */
    public function updateSetting($key, $value) {
        $settings = $this->getSettings();
        $settings[$key] = $value;
        return $this->saveSettings($settings);
    }
    
    /**
     * بررسی فعال بودن ماژول
     */
    public function isEnabled() {
        $settings = $this->getSettings();
        return $settings['enabled'] ?? false;
    }
}

