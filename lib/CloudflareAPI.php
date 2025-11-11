<?php
/**
 * Cloudflare API Client
 * 
 * این کلاس برای ارتباط با API Cloudflare استفاده می‌شود
 */

class CloudflareAPI {
    private $apiToken;
    private $apiEmail;
    private $apiKey;
    private $baseUrl = 'https://api.cloudflare.com/client/v4';
    private $zoneId;
    private $useToken = true;
    
    /**
     * Constructor
     * 
     * @param string $apiToken API Token Cloudflare (ترجیحاً)
     * @param string $apiEmail Email Cloudflare (اگر از API Key استفاده می‌شود)
     * @param string $apiKey API Key Cloudflare (اگر از API Key استفاده می‌شود)
     */
    public function __construct($apiToken = null, $apiEmail = null, $apiKey = null) {
        if (!empty($apiToken)) {
            $this->apiToken = $apiToken;
            $this->useToken = true;
        } elseif (!empty($apiEmail) && !empty($apiKey)) {
            $this->apiEmail = $apiEmail;
            $this->apiKey = $apiKey;
            $this->useToken = false;
        } else {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('api_token_required'));
        }
    }
    
    /**
     * تنظیم Zone ID
     */
    public function setZoneId($zoneId) {
        $this->zoneId = $zoneId;
    }
    
    /**
     * ارسال درخواست به API Cloudflare با Retry
     */
    private function makeRequest($method, $endpoint, $data = null, $retry = true) {
        $url = $this->baseUrl . $endpoint;
        
        $executeRequest = function() use ($method, $url, $data) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            // تنظیم هدرها
            $headers = [
                'Content-Type: application/json'
            ];
            
            if ($this->useToken) {
                $headers[] = 'Authorization: Bearer ' . $this->apiToken;
            } else {
                $headers[] = 'X-Auth-Email: ' . $this->apiEmail;
                $headers[] = 'X-Auth-Key: ' . $this->apiKey;
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                require_once __DIR__ . '/Language.php';
                $lang = Language::getInstance();
                throw new Exception($lang->translate('cloudflare_connection_error') . ': ' . $error);
            }
            
            $result = json_decode($response, true);
            
            if ($httpCode >= 400) {
                require_once __DIR__ . '/Language.php';
                $lang = Language::getInstance();
                $errorMsg = isset($result['errors'][0]['message']) 
                    ? $result['errors'][0]['message'] 
                    : $lang->translate('cloudflare_unknown_error');
                throw new Exception($errorMsg . ' (HTTP ' . $httpCode . ')');
            }
            
            return $result;
        };
        
        // استفاده از Retry برای خطاهای موقت
        if ($retry) {
            require_once __DIR__ . '/RetryHandler.php';
            $retryHandler = new RetryHandler(3, 1, 2);
            try {
                return $retryHandler->execute($executeRequest, ['429', '500', '502', '503', '504']);
            } catch (Exception $e) {
                // اگر Retry موفق نبود، خطا را پرتاب کن
                throw $e;
            }
        } else {
            return $executeRequest();
        }
    }
    
    /**
     * دریافت لیست Zone ها
     */
    public function listZones($name = null) {
        $endpoint = '/zones';
        if ($name) {
            $endpoint .= '?name=' . urlencode($name);
        }
        $result = $this->makeRequest('GET', $endpoint);
        return $result['result'] ?? [];
    }
    
    /**
     * ایجاد رکورد DNS
     * 
     * @param string $type نوع رکورد (A, AAAA, CNAME, MX, TXT, ...)
     * @param string $name نام رکورد
     * @param string $content محتوای رکورد
     * @param int $ttl TTL (اختیاری، پیش‌فرض: 1 = Auto)
     * @param bool $proxied استفاده از Cloudflare Proxy (فقط برای A و AAAA)
     * @param int $priority اولویت (برای MX)
     */
    public function createDNSRecord($type, $name, $content, $ttl = 1, $proxied = false, $priority = null) {
        if (empty($this->zoneId)) {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_id_not_set'));
        }
        
        $data = [
            'type' => strtoupper($type),
            'name' => $name,
            'content' => $content,
            'ttl' => $ttl
        ];
        
        // برای رکوردهای A و AAAA می‌توان proxied را تنظیم کرد
        if (in_array($type, ['A', 'AAAA']) && $proxied) {
            $data['proxied'] = true;
        }
        
        // برای رکوردهای MX باید priority تنظیم شود
        if ($type === 'MX' && $priority !== null) {
            $data['priority'] = $priority;
        }
        
        $endpoint = '/zones/' . $this->zoneId . '/dns_records';
        $result = $this->makeRequest('POST', $endpoint, $data);
        
        return $result['result'] ?? null;
    }
    
    /**
     * به‌روزرسانی رکورد DNS
     */
    public function updateDNSRecord($recordId, $type, $name, $content, $ttl = 1, $proxied = false, $priority = null) {
        if (empty($this->zoneId)) {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_id_not_set'));
        }
        
        $data = [
            'type' => strtoupper($type),
            'name' => $name,
            'content' => $content,
            'ttl' => $ttl
        ];
        
        if (in_array($type, ['A', 'AAAA']) && $proxied) {
            $data['proxied'] = true;
        }
        
        if ($type === 'MX' && $priority !== null) {
            $data['priority'] = $priority;
        }
        
        $endpoint = '/zones/' . $this->zoneId . '/dns_records/' . $recordId;
        $result = $this->makeRequest('PUT', $endpoint, $data);
        
        return $result['result'] ?? null;
    }
    
    /**
     * حذف رکورد DNS
     */
    public function deleteDNSRecord($recordId) {
        if (empty($this->zoneId)) {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_id_not_set'));
        }
        
        $endpoint = '/zones/' . $this->zoneId . '/dns_records/' . $recordId;
        $result = $this->makeRequest('DELETE', $endpoint);
        
        return $result['success'] ?? false;
    }
    
    /**
     * جستجوی رکورد DNS
     */
    public function findDNSRecord($name, $type = null) {
        if (empty($this->zoneId)) {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_id_not_set'));
        }
        
        $endpoint = '/zones/' . $this->zoneId . '/dns_records?name=' . urlencode($name);
        if ($type) {
            $endpoint .= '&type=' . strtoupper($type);
        }
        
        $result = $this->makeRequest('GET', $endpoint);
        return $result['result'] ?? [];
    }
    
    /**
     * دریافت تمام رکوردهای DNS یک Zone
     */
    public function listDNSRecords($type = null) {
        if (empty($this->zoneId)) {
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            throw new Exception($lang->translate('zone_id_not_set'));
        }
        
        $endpoint = '/zones/' . $this->zoneId . '/dns_records';
        if ($type) {
            $endpoint .= '?type=' . strtoupper($type);
        }
        
        $result = $this->makeRequest('GET', $endpoint);
        return $result['result'] ?? [];
    }
    
    /**
     * تست اتصال و اعتبار API
     */
    public function testConnection() {
        try {
            $result = $this->makeRequest('GET', '/user/tokens/verify');
            require_once __DIR__ . '/Language.php';
            $lang = Language::getInstance();
            return [
                'success' => true,
                'message' => $lang->translate('connection_successful'),
                'user' => $result['result'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * به‌روزرسانی SSL Mode یک Zone
     */
    public function updateZoneSSL($zoneId, $sslMode) {
        $endpoint = '/zones/' . $zoneId . '/settings/ssl';
        $data = ['value' => $sslMode];
        $result = $this->makeRequest('PATCH', $endpoint, $data);
        return $result['result'] ?? null;
    }
    
    /**
     * دریافت SSL Mode یک Zone
     */
    public function getZoneSSL($zoneId) {
        $endpoint = '/zones/' . $zoneId . '/settings/ssl';
        $result = $this->makeRequest('GET', $endpoint);
        return $result['result']['value'] ?? null;
    }
    
    /**
     * به‌روزرسانی تنظیمات Zone
     */
    public function updateZoneSetting($zoneId, $settingName, $value) {
        $endpoint = '/zones/' . $zoneId . '/settings/' . $settingName;
        $data = ['value' => $value];
        $result = $this->makeRequest('PATCH', $endpoint, $data);
        return $result['result'] ?? null;
    }
    
    /**
     * دریافت تنظیمات Zone
     */
    public function getZoneSetting($zoneId, $settingName) {
        $endpoint = '/zones/' . $zoneId . '/settings/' . $settingName;
        $result = $this->makeRequest('GET', $endpoint);
        return $result['result']['value'] ?? null;
    }
}

