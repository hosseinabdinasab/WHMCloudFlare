<?php
/**
 * کلاس مدیریت Retry برای خطاهای موقت
 */

class RetryHandler {
    private $maxRetries;
    private $delay;
    private $backoffMultiplier;
    
    public function __construct($maxRetries = 3, $delay = 1, $backoffMultiplier = 2) {
        $this->maxRetries = $maxRetries;
        $this->delay = $delay;
        $this->backoffMultiplier = $backoffMultiplier;
    }
    
    /**
     * اجرای عملیات با Retry
     */
    public function execute(callable $operation, $retryableExceptions = []) {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $this->maxRetries) {
            try {
                return $operation();
            } catch (Exception $e) {
                $lastException = $e;
                
                // بررسی اینکه آیا این خطا قابل Retry است
                if (!empty($retryableExceptions)) {
                    $shouldRetry = false;
                    foreach ($retryableExceptions as $retryableException) {
                        if ($e instanceof $retryableException || 
                            strpos($e->getMessage(), $retryableException) !== false) {
                            $shouldRetry = true;
                            break;
                        }
                    }
                    
                    if (!$shouldRetry) {
                        throw $e;
                    }
                }
                
                // بررسی خطاهای HTTP که قابل Retry هستند
                $httpCode = $this->extractHttpCode($e->getMessage());
                if ($httpCode && ($httpCode >= 500 || $httpCode === 429)) {
                    $attempt++;
                    if ($attempt < $this->maxRetries) {
                        $delay = $this->delay * pow($this->backoffMultiplier, $attempt - 1);
                        sleep($delay);
                        continue;
                    }
                } else {
                    // خطای غیرقابل Retry
                    throw $e;
                }
            }
        }
        
        throw $lastException;
    }
    
    /**
     * استخراج کد HTTP از پیام خطا
     */
    private function extractHttpCode($message) {
        if (preg_match('/HTTP (\d+)/', $message, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }
}

