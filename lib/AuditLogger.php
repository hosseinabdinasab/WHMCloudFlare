<?php
/**
 * کلاس Audit Logging برای ثبت تمام عملیات
 */

require_once __DIR__ . '/Logger.php';

class AuditLogger {
    private $logger;
    private $auditFile;
    
    public function __construct() {
        $this->logger = new Logger();
        $this->auditFile = __DIR__ . '/../logs/audit.log';
    }
    
    /**
     * ثبت عملیات Audit
     */
    public function log($action, $user, $details = [], $status = 'success') {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'user' => $user,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'details' => $details,
            'status' => $status
        ];
        
        $logLine = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($this->auditFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // همچنین در Logger معمولی هم ثبت می‌کنیم
        $this->logger->info("AUDIT: $action by $user - Status: $status");
    }
    
    /**
     * خواندن Audit Log ها
     */
    public function readLogs($limit = 100) {
        if (!file_exists($this->auditFile)) {
            return [];
        }
        
        $lines = file($this->auditFile);
        $logs = [];
        
        foreach (array_slice($lines, -$limit) as $line) {
            $logs[] = json_decode(trim($line), true);
        }
        
        return array_reverse($logs);
    }
    
    /**
     * جستجو در Audit Log ها
     */
    public function search($filters = []) {
        if (!file_exists($this->auditFile)) {
            return [];
        }
        
        $lines = file($this->auditFile);
        $results = [];
        
        foreach ($lines as $line) {
            $entry = json_decode(trim($line), true);
            if (!$entry) continue;
            
            $match = true;
            foreach ($filters as $key => $value) {
                if (isset($entry[$key]) && $entry[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                $results[] = $entry;
            }
        }
        
        return array_reverse($results);
    }
}

