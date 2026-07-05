<?php

final class Security {
    public static function whmUser(): ?string {
        $user = $_SERVER['REMOTE_USER'] ?? getenv('REMOTE_USER') ?: null;
        return ($user && preg_match('/^[a-z0-9_]+$/i', $user)) ? $user : null;
    }

    public static function requireWhmAuth(): string {
        $user = self::whmUser();
        if (!$user) {
            header('HTTP/1.1 403 Forbidden');
            echo 'WHM authentication required.';
            exit;
        }
        return $user;
    }

    public static function csrfToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['whmcf_csrf'])) {
            $_SESSION['whmcf_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['whmcf_csrf'];
    }

    public static function verifyCsrf(?string $token): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return is_string($token)
            && !empty($_SESSION['whmcf_csrf'])
            && hash_equals($_SESSION['whmcf_csrf'], $token);
    }

    public static function encrypt(string $plain): string {
        $key = self::encryptionKey();
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipher);
    }

    public static function decrypt(string $encoded): string {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 17) {
            return '';
        }
        $key = self::encryptionKey();
        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $plain !== false ? $plain : '';
    }

    private static function encryptionKey(): string {
        $file = Paths::config('.encryption_key');
        if (!is_file($file)) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            file_put_contents($file, bin2hex(random_bytes(32)), LOCK_EX);
            chmod($file, 0600);
        }
        return hash('sha256', (string) file_get_contents($file), true);
    }
}
