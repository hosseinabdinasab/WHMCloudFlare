<?php
/**
 * کلاس Cache برای بهینه‌سازی Performance
 */

class Cache {
    private $cacheDir;
    private $defaultTTL = 3600; // 1 ساعت
    
    public function __construct() {
        $this->cacheDir = __DIR__ . '/../cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * دریافت از Cache
     */
    public function get($key) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // بررسی انقضا
        if (isset($data['expires']) && $data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'] ?? null;
    }
    
    /**
     * ذخیره در Cache
     */
    public function set($key, $value, $ttl = null) {
        $file = $this->getCacheFile($key);
        $ttl = $ttl ?? $this->defaultTTL;
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($file, serialize($data)) !== false;
    }
    
    /**
     * حذف از Cache
     */
    public function delete($key) {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }
    
    /**
     * پاک کردن تمام Cache
     */
    public function clear() {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * دریافت مسیر فایل Cache
     */
    private function getCacheFile($key) {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * بررسی وجود در Cache
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
}

