<?php
/**
 * کلاس مدیریت SSL/TLS در Cloudflare
 */

require_once __DIR__ . '/CloudflareAPI.php';
require_once __DIR__ . '/Language.php';

class SSLManager {
    private $cloudflare;
    private $zoneId;
    
    public function __construct($cloudflare, $zoneId) {
        $this->cloudflare = $cloudflare;
        $this->zoneId = $zoneId;
    }
    
    /**
     * تنظیم SSL Mode
     * 
     * @param string $mode off, flexible, full, strict
     */
    public function setSSLMode($mode) {
        $validModes = ['off', 'flexible', 'full', 'strict'];
        if (!in_array($mode, $validModes)) {
            $lang = Language::getInstance();
            throw new Exception($lang->translate('ssl_mode_invalid', ['mode' => $mode]));
        }
        
        return $this->cloudflare->updateZoneSSL($this->zoneId, $mode);
    }
    
    /**
     * دریافت SSL Mode فعلی
     */
    public function getSSLMode() {
        return $this->cloudflare->getZoneSSL($this->zoneId);
    }
    
    /**
     * فعال کردن SSL/TLS
     */
    public function enableSSL($mode = 'full') {
        return $this->setSSLMode($mode);
    }
    
    /**
     * غیرفعال کردن SSL/TLS
     */
    public function disableSSL() {
        return $this->setSSLMode('off');
    }
    
    /**
     * تنظیم Always Use HTTPS
     */
    public function setAlwaysUseHTTPS($enabled = true) {
        return $this->cloudflare->updateZoneSetting($this->zoneId, 'always_use_https', $enabled ? 'on' : 'off');
    }
    
    /**
     * تنظیم Automatic HTTPS Rewrites
     */
    public function setAutomaticHTTPSRewrites($enabled = true) {
        return $this->cloudflare->updateZoneSetting($this->zoneId, 'automatic_https_rewrites', $enabled ? 'on' : 'off');
    }
    
    /**
     * تنظیم Minimum TLS Version
     */
    public function setMinTLSVersion($version = '1.2') {
        $validVersions = ['1.0', '1.1', '1.2', '1.3'];
        if (!in_array($version, $validVersions)) {
            $lang = Language::getInstance();
            throw new Exception($lang->translate('tls_version_invalid', ['version' => $version]));
        }
        
        return $this->cloudflare->updateZoneSetting($this->zoneId, 'min_tls_version', $version);
    }
}

