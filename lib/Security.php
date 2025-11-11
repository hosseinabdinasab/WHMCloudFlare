<?php
/**
 * کلاس امنیت - رمزنگاری و مدیریت امنیت
 */

class Security {
    private static $encryptionMethod = 'AES-256-CBC';
    private static $keyFile;
    
    public function __construct() {
        self::$keyFile = __DIR__ . '/../config/.encryption_key';
        $this->ensureKeyExists();
    }
    
    /**
     * اطمینان از وجود کلید رمزنگاری
     */
    private function ensureKeyExists() {
        if (!file_exists(self::$keyFile)) {
            $key = $this->generateKey();
            file_put_contents(self::$keyFile, $key);
            chmod(self::$keyFile, 0600);
        }
    }
    
    /**
     * تولید کلید رمزنگاری
     */
    private function generateKey() {
        return base64_encode(random_bytes(32));
    }
    
    /**
     * دریافت کلید رمزنگاری
     */
    private function getKey() {
        return base64_decode(file_get_contents(self::$keyFile));
    }
    
    /**
     * رمزنگاری داده
     */
    public function encrypt($data) {
        $key = $this->getKey();
        $iv = random_bytes(openssl_cipher_iv_length(self::$encryptionMethod));
        $encrypted = openssl_encrypt($data, self::$encryptionMethod, $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * رمزگشایی داده
     */
    public function decrypt($encryptedData) {
        $key = $this->getKey();
        list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
        return openssl_decrypt($encrypted, self::$encryptionMethod, $key, 0, $iv);
    }
    
    /**
     * Hash کردن داده (یک‌طرفه)
     */
    public function hash($data) {
        return password_hash($data, PASSWORD_BCRYPT);
    }
    
    /**
     * بررسی Hash
     */
    public function verifyHash($data, $hash) {
        return password_verify($data, $hash);
    }
    
    /**
     * تولید Token امن
     */
    public function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

