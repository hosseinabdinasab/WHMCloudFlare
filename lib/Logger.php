<?php
/**
 * کلاس مدیریت لاگ
 */

class Logger {
    private $logFile;
    private $logDir;
    
    public function __construct() {
        $this->logDir = __DIR__ . '/../logs';
        
        // ایجاد دایرکتوری logs اگر وجود ندارد
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // فایل لاگ روزانه
        $this->logFile = $this->logDir . '/whmcloudflare-' . date('Y-m-d') . '.log';
    }
    
    /**
     * نوشتن لاگ
     */
    private function writeLog($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * لاگ اطلاعاتی
     */
    public function info($message) {
        $this->writeLog('INFO', $message);
    }
    
    /**
     * لاگ هشدار
     */
    public function warning($message) {
        $this->writeLog('WARNING', $message);
    }
    
    /**
     * لاگ خطا
     */
    public function error($message) {
        $this->writeLog('ERROR', $message);
    }
    
    /**
     * لاگ دیباگ
     */
    public function debug($message) {
        $this->writeLog('DEBUG', $message);
    }
    
    /**
     * خواندن لاگ‌ها
     */
    public function readLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $file = file($this->logFile);
        return array_slice($file, -$lines);
    }
    
    /**
     * پاک کردن لاگ‌های قدیمی (بیشتر از 30 روز)
     */
    public function cleanOldLogs($days = 30) {
        $files = glob($this->logDir . '/whmcloudflare-*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

