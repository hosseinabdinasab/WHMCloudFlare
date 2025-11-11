<?php
/**
 * کلاس مدیریت چندین Zone
 */

require_once __DIR__ . '/CloudflareAPI.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Language.php';

class ZoneManager {
    private $config;
    private $cloudflare;
    
    public function __construct() {
        $this->config = new Config();
        $settings = $this->config->getSettings();
        
        $this->cloudflare = new CloudflareAPI(
            $settings['api_token'] ?? null,
            $settings['api_email'] ?? null,
            $settings['api_key'] ?? null
        );
    }
    
    /**
     * دریافت Zone ID برای یک دامنه
     */
    public function getZoneForDomain($domain) {
        $settings = $this->config->getSettings();
        
        // بررسی Zone mapping
        $zoneMapping = json_decode($settings['zone_mapping'] ?? '{}', true);
        
        // جستجو در mapping
        foreach ($zoneMapping as $pattern => $zoneId) {
            if ($this->matchDomain($domain, $pattern)) {
                return $zoneId;
            }
        }
        
        // اگر mapping پیدا نشد، از Zone ID پیش‌فرض استفاده کن
        return $settings['zone_id'] ?? null;
    }
    
    /**
     * تطبیق دامنه با الگو
     */
    private function matchDomain($domain, $pattern) {
        // پشتیبانی از wildcard
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . $pattern . '$/';
        return preg_match($pattern, $domain);
    }
    
    /**
     * دریافت Cloudflare API برای Zone مشخص
     */
    public function getCloudflareForDomain($domain) {
        $zoneId = $this->getZoneForDomain($domain);
        if (!$zoneId) {
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_not_found_for_domain', ['domain' => $domain]));
        }
        
        $this->cloudflare->setZoneId($zoneId);
        return $this->cloudflare;
    }
    
    /**
     * افزودن Zone mapping
     */
    public function addZoneMapping($pattern, $zoneId) {
        $settings = $this->config->getSettings();
        $zoneMapping = json_decode($settings['zone_mapping'] ?? '{}', true);
        $zoneMapping[$pattern] = $zoneId;
        
        $settings['zone_mapping'] = json_encode($zoneMapping, JSON_UNESCAPED_UNICODE);
        $this->config->saveSettings($settings);
    }
    
    /**
     * حذف Zone mapping
     */
    public function removeZoneMapping($pattern) {
        $settings = $this->config->getSettings();
        $zoneMapping = json_decode($settings['zone_mapping'] ?? '{}', true);
        unset($zoneMapping[$pattern]);
        
        $settings['zone_mapping'] = json_encode($zoneMapping, JSON_UNESCAPED_UNICODE);
        $this->config->saveSettings($settings);
    }
    
    /**
     * دریافت تمام Zone mappings
     */
    public function getAllMappings() {
        $settings = $this->config->getSettings();
        return json_decode($settings['zone_mapping'] ?? '{}', true);
    }
}

