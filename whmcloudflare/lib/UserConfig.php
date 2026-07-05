<?php

final class UserConfig {
    private static array $cache = [];

    public static function load(string $cpuser): array {
        if (isset(self::$cache[$cpuser])) {
            return self::$cache[$cpuser];
        }
        $file = Paths::userConfig($cpuser);
        if (!is_file($file)) {
            self::$cache[$cpuser] = self::defaults();
            return self::$cache[$cpuser];
        }
        $json = json_decode((string) file_get_contents($file), true);
        self::$cache[$cpuser] = is_array($json) ? array_merge(self::defaults(), $json) : self::defaults();
        return self::$cache[$cpuser];
    }

    public static function save(string $cpuser, array $data): bool {
        $dir = Paths::userDir($cpuser);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $merged = array_merge(self::defaults(), $data);
        foreach (['api_token', 'global_api_key'] as $key) {
            if (!empty($merged[$key]) && strpos((string) $merged[$key], 'enc:') !== 0) {
                $merged[$key] = 'enc:' . Security::encrypt((string) $merged[$key]);
            }
        }
        self::$cache[$cpuser] = $merged;
        $file = Paths::userConfig($cpuser);
        $ok = (bool) file_put_contents($file, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        if ($ok) {
            chmod($file, 0600);
            if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
                @chown($file, $cpuser);
                @chgrp($file, $cpuser);
            }
        }
        return $ok;
    }

    public static function credentials(string $cpuser): Credentials {
        return Credentials::fromEncryptedConfig(self::load($cpuser));
    }

    public static function isConnected(string $cpuser): bool {
        $cfg = self::load($cpuser);
        return !empty($cfg['connected']) && self::credentials($cpuser)->isConfigured();
    }

    public static function listUsers(): array {
        $base = Paths::data() . '/users';
        if (!is_dir($base)) {
            return [];
        }
        $users = [];
        foreach (scandir($base) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_file(Paths::userConfig($entry)) && self::isConnected($entry)) {
                $users[] = $entry;
            }
        }
        sort($users);
        return $users;
    }

    private static function defaults(): array {
        return [
            'connected' => false,
            'api_token' => '',
            'email' => '',
            'global_api_key' => '',
            'auth_mode' => 'token',
            'auto_sync' => true,
            'proxied' => true,
            'ttl' => 1,
            'zone_id' => '',
            'last_sync' => '',
        ];
    }
}
