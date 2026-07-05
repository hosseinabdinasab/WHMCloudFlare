<?php

final class Config {
    private static ?array $data = null;

    public static function load(): array {
        if (self::$data !== null) {
            return self::$data;
        }
        $file = Paths::config();
        if (!is_file($file)) {
            self::$data = self::defaults();
            return self::$data;
        }
        $json = json_decode((string) file_get_contents($file), true);
        self::$data = is_array($json) ? array_merge(self::defaults(), $json) : self::defaults();
        return self::$data;
    }

    public static function save(array $data): bool {
        $dir = dirname(Paths::config());
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $merged = array_merge(self::defaults(), $data);
        if (!empty($merged['api_token']) && strpos($merged['api_token'], 'enc:') !== 0) {
            $merged['api_token'] = 'enc:' . Security::encrypt($merged['api_token']);
        }
        if (!empty($merged['global_api_key']) && strpos($merged['global_api_key'], 'enc:') !== 0) {
            $merged['global_api_key'] = 'enc:' . Security::encrypt($merged['global_api_key']);
        }
        self::$data = $merged;
        $ok = (bool) file_put_contents(Paths::config(), json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        if ($ok) {
            chmod(Paths::config(), 0600);
        }
        return $ok;
    }

    public static function get(string $key, $default = null) {
        $data = self::load();
        return $data[$key] ?? $default;
    }

    public static function apiToken(): string {
        $raw = (string) self::get('api_token', '');
        if ($raw === '') {
            return '';
        }
        if (strpos($raw, 'enc:') === 0) {
            return Security::decrypt(substr($raw, 4));
        }
        return $raw;
    }

    public static function globalApiKey(): string {
        $raw = (string) self::get('global_api_key', '');
        if ($raw === '') {
            return '';
        }
        if (strpos($raw, 'enc:') === 0) {
            return Security::decrypt(substr($raw, 4));
        }
        return $raw;
    }

    public static function email(): string {
        return (string) self::get('email', '');
    }

    public static function isEnabled(): bool {
        return (bool) self::get('enabled', false);
    }

    private static function defaults(): array {
        return [
            'enabled' => false,
            'api_token' => '',
            'email' => '',
            'global_api_key' => '',
            'auth_mode' => 'token',
            'auto_create_dns' => true,
            'auto_delete_dns' => true,
            'auto_update_ip' => true,
            'proxied' => true,
            'ttl' => 1,
            'language' => 'en',
            'log_level' => 'info',
        ];
    }
}
