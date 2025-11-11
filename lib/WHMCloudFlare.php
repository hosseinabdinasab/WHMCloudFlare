<?php
/**
 * کلاس اصلی WHMCloudFlare
 * 
 * این کلاس منطق اصلی ماژول را مدیریت می‌کند
 */

require_once __DIR__ . '/CloudflareAPI.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/ZoneManager.php';
require_once __DIR__ . '/Cache.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/SSLManager.php';
require_once __DIR__ . '/Statistics.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/Language.php';

class WHMCloudFlare {
    private $cloudflare;
    private $logger;
    private $config;
    private $zoneManager;
    private $cache;
    private $auditLogger;
    private $statistics;
    private $notification;
    
    public function __construct($domain = null) {
        $this->config = new Config();
        $this->logger = new Logger();
        $this->cache = new Cache();
        $this->auditLogger = new AuditLogger();
        $this->zoneManager = new ZoneManager();
        $this->statistics = new Statistics();
        $this->notification = new Notification();
        
        // بارگذاری تنظیمات
        $settings = $this->config->getSettings();
        
        if (empty($settings['api_token']) && (empty($settings['api_email']) || empty($settings['api_key']))) {
            $lang = Language::getInstance();
            throw new Exception($lang->translate('cloudflare_settings_incomplete'));
        }
        
        // ایجاد اتصال به Cloudflare
        $this->cloudflare = new CloudflareAPI(
            $settings['api_token'] ?? null,
            $settings['api_email'] ?? null,
            $settings['api_key'] ?? null
        );
        
        // اگر دامنه مشخص شده، از Zone Manager استفاده کن
        if ($domain) {
            $zoneId = $this->zoneManager->getZoneForDomain($domain);
            if ($zoneId) {
                $this->cloudflare->setZoneId($zoneId);
            } elseif (!empty($settings['zone_id'])) {
                $this->cloudflare->setZoneId($settings['zone_id']);
            }
        } elseif (!empty($settings['zone_id'])) {
            $this->cloudflare->setZoneId($settings['zone_id']);
        }
    }
    
    /**
     * ایجاد رکوردهای DNS برای یک اکانت جدید
     * 
     * @param string $domain دامنه
     * @param string $ip آدرس IP
     * @param array $options گزینه‌های اضافی
     */
    public function createAccountDNS($domain, $ip, $options = []) {
        try {
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('log_start_dns_creation', ['domain' => $domain]));
            
            // استفاده از Zone Manager برای دامنه
            $zoneId = $this->zoneManager->getZoneForDomain($domain);
            if ($zoneId) {
                $this->cloudflare->setZoneId($zoneId);
            }
            
            $settings = $this->config->getSettings();
            
            // Audit log
            $this->auditLogger->log('create_account_dns', 'system', [
                'domain' => $domain,
                'ip' => $ip
            ], 'started');
            
            // ایجاد رکورد A برای دامنه اصلی
            if ($settings['auto_create_a'] ?? true) {
                $this->createRecord('A', $domain, $ip, $settings['proxied'] ?? false);
            }
            
            // ایجاد رکورد AAAA برای IPv6 (اگر موجود باشد)
            if (isset($options['ipv6']) && !empty($options['ipv6']) && ($settings['auto_create_aaaa'] ?? false)) {
                $this->createRecord('AAAA', $domain, $options['ipv6'], $settings['proxied'] ?? false);
            }
            
            // ایجاد رکورد www
            if ($settings['auto_create_www'] ?? true) {
                $this->createRecord('CNAME', 'www.' . $domain, $domain, false);
            }
            
            // ایجاد رکوردهای MX (اگر تنظیم شده باشد)
            if ($settings['auto_create_mx'] ?? false && !empty($settings['mx_records'])) {
                $mxRecords = json_decode($settings['mx_records'], true);
                foreach ($mxRecords as $mx) {
                    $this->createRecord('MX', $domain, $mx['host'], false, false, $mx['priority'] ?? 10);
                }
            }
            
            // ایجاد رکوردهای TXT (اگر تنظیم شده باشد)
            if ($settings['auto_create_txt'] ?? false && !empty($settings['txt_records'])) {
                $txtRecords = json_decode($settings['txt_records'], true);
                foreach ($txtRecords as $txt) {
                    $this->createRecord('TXT', $domain, $txt['content'], false);
                }
            }
            
            // مدیریت SSL/TLS اگر فعال باشد
            if ($settings['ssl_auto_manage'] ?? false) {
                $sslManager = new SSLManager($this->cloudflare, $zoneId);
                $sslManager->setSSLMode($settings['ssl_mode'] ?? 'full');
                if ($settings['always_use_https'] ?? false) {
                    $sslManager->setAlwaysUseHTTPS(true);
                }
                $sslManager->setMinTLSVersion($settings['min_tls_version'] ?? '1.2');
            }
            
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('log_dns_created_success', ['domain' => $domain]));
            
            // Audit log
            $this->auditLogger->log('create_account_dns', 'system', [
                'domain' => $domain,
                'ip' => $ip
            ], 'success');
            
            // ثبت آمار
            $this->statistics->recordOperation('create_account_dns', 'success', $domain);
            
            return true;
            
        } catch (Exception $e) {
            $lang = Language::getInstance();
            $this->logger->error($lang->translate('log_dns_creation_error', ['domain' => $domain, 'error' => $e->getMessage()]));
            
            // Audit log
            $this->auditLogger->log('create_account_dns', 'system', [
                'domain' => $domain,
                'ip' => $ip,
                'error' => $e->getMessage()
            ], 'failed');
            
            // ثبت آمار
            $this->statistics->recordOperation('create_account_dns', 'failed', $domain);
            
            // ارسال اعلان خطا
            $this->notification->notifyError(
                $lang->translate('log_dns_creation_error', ['domain' => $domain, 'error' => $e->getMessage()]),
                ['error' => $e->getMessage(), 'domain' => $domain, 'ip' => $ip]
            );
            
            throw $e;
        }
    }
    
    /**
     * حذف رکوردهای DNS یک اکانت
     */
    public function deleteAccountDNS($domain) {
        try {
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('log_start_dns_deletion', ['domain' => $domain]));
            
            $records = $this->cloudflare->findDNSRecord($domain);
            
            foreach ($records as $record) {
                // حذف رکوردهای مربوط به این دامنه
                if (strpos($record['name'], $domain) !== false) {
                    $this->cloudflare->deleteDNSRecord($record['id']);
                    $this->logger->info("رکورد {$record['type']} با نام {$record['name']} حذف شد");
                }
            }
            
            // حذف رکورد www
            $wwwRecords = $this->cloudflare->findDNSRecord('www.' . $domain);
            foreach ($wwwRecords as $record) {
                $this->cloudflare->deleteDNSRecord($record['id']);
            }
            
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('log_dns_deleted_success', ['domain' => $domain]));
            return true;
            
        } catch (Exception $e) {
            $lang = Language::getInstance();
            $this->logger->error($lang->translate('log_dns_deletion_error', ['domain' => $domain, 'error' => $e->getMessage()]));
            throw $e;
        }
    }
    
    /**
     * به‌روزرسانی IP یک دامنه
     */
    public function updateAccountIP($domain, $newIp, $ipv6 = null) {
        try {
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('log_ip_update', ['domain' => $domain, 'ip' => $newIp]));
            
            // پیدا کردن رکورد A
            $aRecords = $this->cloudflare->findDNSRecord($domain, 'A');
            
            if (!empty($aRecords)) {
                $record = $aRecords[0];
                $settings = $this->config->getSettings();
                
                $this->cloudflare->updateDNSRecord(
                    $record['id'],
                    'A',
                    $domain,
                    $newIp,
                    $record['ttl'] ?? 1,
                    $settings['proxied'] ?? false
                );
                
                $lang = Language::getInstance();
                $this->logger->info($lang->translate('log_ip_updated', ['domain' => $domain]));
            }
            
            // به‌روزرسانی IPv6 اگر موجود باشد
            if ($ipv6) {
                $aaaaRecords = $this->cloudflare->findDNSRecord($domain, 'AAAA');
                if (!empty($aaaaRecords)) {
                    $record = $aaaaRecords[0];
                    $this->cloudflare->updateDNSRecord(
                        $record['id'],
                        'AAAA',
                        $domain,
                        $ipv6,
                        $record['ttl'] ?? 1,
                        $settings['proxied'] ?? false
                    );
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $lang = Language::getInstance();
            $this->logger->error($lang->translate('log_ip_update_error', ['domain' => $domain, 'error' => $e->getMessage()]));
            throw $e;
        }
    }
    
    /**
     * ایجاد یک رکورد DNS (با Cache)
     */
    private function createRecord($type, $name, $content, $proxied = false, $ttl = 1, $priority = null) {
        try {
            $settings = $this->config->getSettings();
            $useCache = $settings['cache_enabled'] ?? true;
            
            // بررسی Cache
            if ($useCache) {
                $cacheKey = "dns_record_{$name}_{$type}";
                if ($this->cache->has($cacheKey)) {
                    $cached = $this->cache->get($cacheKey);
                    if ($cached === $content) {
                        $this->logger->debug("رکورد $type با نام $name از Cache استفاده شد");
                        return true;
                    }
                }
            }
            
            // بررسی وجود رکورد
            $existing = $this->cloudflare->findDNSRecord($name, $type);
            
            if (!empty($existing)) {
                $lang = Language::getInstance();
                $this->logger->warning($lang->translate('log_record_exists', ['type' => $type, 'name' => $name]));
                return false;
            }
            
            $result = $this->cloudflare->createDNSRecord($type, $name, $content, $ttl, $proxied, $priority);
            
            if ($result) {
                $lang = Language::getInstance();
                $this->logger->info($lang->translate('log_record_created', ['type' => $type, 'name' => $name]));
                
                // ذخیره در Cache
                if ($useCache) {
                    $cacheKey = "dns_record_{$name}_{$type}";
                    $this->cache->set($cacheKey, $content, $settings['cache_ttl'] ?? 3600);
                }
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $lang = Language::getInstance();
            $this->logger->error($lang->translate('log_record_creation_error', ['type' => $type, 'name' => $name, 'error' => $e->getMessage()]));
            throw $e;
        }
    }
    
    /**
     * تست اتصال به Cloudflare
     */
    public function testConnection() {
        return $this->cloudflare->testConnection();
    }
    
    /**
     * دریافت لیست Zone ها
     */
    public function getZones($name = null) {
        return $this->cloudflare->listZones($name);
    }
}

