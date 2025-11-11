<?php
/**
 * کلاس مدیریت زبان و ترجمه
 */

class Language {
    private static $instance = null;
    private $currentLanguage = 'fa';
    private $translations = [];
    private $languageDir;
    
    private function __construct() {
        $this->languageDir = __DIR__ . '/../lang';
        
        // ایجاد دایرکتوری lang اگر وجود ندارد
        if (!is_dir($this->languageDir)) {
            mkdir($this->languageDir, 0755, true);
        }
        
        // دریافت زبان از Cookie یا Session
        if (isset($_COOKIE['whmcf_lang'])) {
            $this->currentLanguage = $_COOKIE['whmcf_lang'];
        } elseif (isset($_SESSION['whmcf_lang'])) {
            $this->currentLanguage = $_SESSION['whmcf_lang'];
        } elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'])) {
            $this->currentLanguage = $_GET['lang'];
        }
        
        // بارگذاری ترجمه‌ها
        $this->loadTranslations();
    }
    
    /**
     * دریافت نمونه Singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * بارگذاری فایل‌های ترجمه
     */
    private function loadTranslations() {
        $langFile = $this->languageDir . '/' . $this->currentLanguage . '.php';
        
        if (file_exists($langFile)) {
            $this->translations = include $langFile;
        } else {
            // اگر فایل ترجمه وجود ندارد، از زبان پیش‌فرض استفاده کن
            $this->currentLanguage = 'fa';
            $langFile = $this->languageDir . '/fa.php';
            if (file_exists($langFile)) {
                $this->translations = include $langFile;
            }
        }
    }
    
    /**
     * ترجمه متن
     */
    public function translate($key, $params = []) {
        $text = $this->translations[$key] ?? $key;
        
        // جایگزینی پارامترها
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $text = str_replace(':' . $param, $value, $text);
            }
        }
        
        return $text;
    }
    
    /**
     * دریافت زبان فعلی
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }
    
    /**
     * تنظیم زبان
     */
    public function setLanguage($lang) {
        $validLanguages = ['fa', 'en'];
        if (in_array($lang, $validLanguages)) {
            $this->currentLanguage = $lang;
            $_SESSION['whmcf_lang'] = $lang;
            setcookie('whmcf_lang', $lang, time() + (365 * 24 * 60 * 60), '/');
            $this->loadTranslations();
        }
    }
    
    /**
     * دریافت جهت متن (RTL/LTR)
     */
    public function getDirection() {
        return $this->currentLanguage === 'fa' ? 'rtl' : 'ltr';
    }
    
    /**
     * دریافت تمام ترجمه‌ها
     */
    public function getAllTranslations() {
        return $this->translations;
    }
}

/**
 * تابع کمکی برای ترجمه
 */
function __($key, $params = []) {
    $lang = Language::getInstance();
    return $lang->translate($key, $params);
}

