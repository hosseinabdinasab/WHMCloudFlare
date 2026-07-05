<?php

final class WHMAccount {
    public static function usernameFromHook(array $data): ?string {
        $user = HookData::get($data, 'user')
            ?? HookData::get($data, 'username');
        return ($user && preg_match('/^[a-z0-9_]+$/i', $user)) ? strtolower($user) : null;
    }

    public static function domainFromHook(array $data): ?string {
        $domain = HookData::get($data, 'domain');
        if ($domain) {
            return strtolower($domain);
        }
        return null;
    }

    public static function ipFromHook(array $data): ?string {
        $ip = HookData::get($data, 'ip')
            ?? HookData::get($data, 'newip')
            ?? HookData::get($data, 'new_ip');
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        return self::ipForDomain(self::domainFromHook($data));
    }

    public static function ipForDomain(?string $domain): ?string {
        if (!$domain) {
            return null;
        }
        if (function_exists('gethostbyname') && gethostbyname($domain) !== $domain) {
            $ip = gethostbyname($domain);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return null;
    }
}
