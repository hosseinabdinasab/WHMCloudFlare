<?php
/**
 * کلاس مدیریت آمار و گزارش‌گیری
 */

require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/AuditLogger.php';

class Statistics {
    private $logger;
    private $auditLogger;
    private $statsFile;
    
    public function __construct() {
        $this->logger = new Logger();
        $this->auditLogger = new AuditLogger();
        $this->statsFile = __DIR__ . '/../config/statistics.json';
    }
    
    /**
     * ثبت آمار عملیات
     */
    public function recordOperation($operation, $status = 'success', $domain = null) {
        $stats = $this->getStats();
        
        $date = date('Y-m-d');
        if (!isset($stats[$date])) {
            $stats[$date] = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'operations' => []
            ];
        }
        
        $stats[$date]['total']++;
        if ($status === 'success') {
            $stats[$date]['success']++;
        } else {
            $stats[$date]['failed']++;
        }
        
        $stats[$date]['operations'][] = [
            'operation' => $operation,
            'status' => $status,
            'domain' => $domain,
            'timestamp' => time()
        ];
        
        // نگه داشتن فقط 90 روز اخیر
        $this->cleanOldStats($stats);
        
        $this->saveStats($stats);
    }
    
    /**
     * دریافت آمار
     */
    public function getStats($days = 30) {
        if (!file_exists($this->statsFile)) {
            return [];
        }
        
        $stats = json_decode(file_get_contents($this->statsFile), true);
        if (!$stats) {
            return [];
        }
        
        // فیلتر بر اساس تعداد روز
        $cutoffDate = date('Y-m-d', strtotime("-$days days"));
        $filtered = [];
        
        foreach ($stats as $date => $data) {
            if ($date >= $cutoffDate) {
                $filtered[$date] = $data;
            }
        }
        
        return $filtered;
    }
    
    /**
     * دریافت خلاصه آمار
     */
    public function getSummary($days = 30) {
        $stats = $this->getStats($days);
        
        $summary = [
            'total_operations' => 0,
            'successful_operations' => 0,
            'failed_operations' => 0,
            'success_rate' => 0,
            'daily_average' => 0,
            'domains_managed' => []
        ];
        
        $domains = [];
        
        foreach ($stats as $date => $data) {
            $summary['total_operations'] += $data['total'];
            $summary['successful_operations'] += $data['success'];
            $summary['failed_operations'] += $data['failed'];
            
            foreach ($data['operations'] as $op) {
                if (!empty($op['domain'])) {
                    $domains[$op['domain']] = true;
                }
            }
        }
        
        $summary['domains_managed'] = count($domains);
        $summary['success_rate'] = $summary['total_operations'] > 0 
            ? round(($summary['successful_operations'] / $summary['total_operations']) * 100, 2)
            : 0;
        $summary['daily_average'] = $days > 0 
            ? round($summary['total_operations'] / $days, 2)
            : 0;
        
        return $summary;
    }
    
    /**
     * دریافت آمار عملیات بر اساس نوع
     */
    public function getOperationStats($operation = null, $days = 30) {
        $stats = $this->getStats($days);
        $operationStats = [];
        
        foreach ($stats as $date => $data) {
            foreach ($data['operations'] as $op) {
                if ($operation === null || $op['operation'] === $operation) {
                    if (!isset($operationStats[$op['operation']])) {
                        $operationStats[$op['operation']] = [
                            'total' => 0,
                            'success' => 0,
                            'failed' => 0
                        ];
                    }
                    
                    $operationStats[$op['operation']]['total']++;
                    if ($op['status'] === 'success') {
                        $operationStats[$op['operation']]['success']++;
                    } else {
                        $operationStats[$op['operation']]['failed']++;
                    }
                }
            }
        }
        
        return $operationStats;
    }
    
    /**
     * پاک کردن آمار قدیمی
     */
    private function cleanOldStats(&$stats) {
        $cutoffDate = date('Y-m-d', strtotime('-90 days'));
        foreach ($stats as $date => $data) {
            if ($date < $cutoffDate) {
                unset($stats[$date]);
            }
        }
    }
    
    /**
     * ذخیره آمار
     */
    private function saveStats($stats) {
        file_put_contents($this->statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

