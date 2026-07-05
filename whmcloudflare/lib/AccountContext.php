<?php

final class AccountContext {
    public static function cpuserForDomain(string $domain): ?string {
        $domain = strtolower(trim($domain));
        $file = '/etc/trueuserdomains';
        if (!is_readable($file)) {
            return null;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $parts = preg_split('/\s*:\s*/', trim($line), 2);
            if (count($parts) === 2 && strtolower($parts[0]) === $domain) {
                return $parts[1];
            }
        }
        return null;
    }

    public static function primaryDomain(string $cpuser): ?string {
        if (function_exists('exec')) {
            $out = [];
            $code = 0;
            @exec('/usr/local/cpanel/bin/whmapi1 accountsummary user=' . escapeshellarg($cpuser) . ' --output=json', $out, $code);
            if ($code === 0 && $out) {
                $json = json_decode(implode("\n", $out), true);
                $domain = $json['data']['acct'][0]['domain'] ?? null;
                if ($domain) {
                    return strtolower($domain);
                }
            }
        }
        $userdata = '/var/cpanel/users/' . $cpuser;
        if (is_readable($userdata)) {
            $content = (string) file_get_contents($userdata);
            if (preg_match('/^DNS=\s*(.+)$/m', $content, $m)) {
                return strtolower(trim($m[1]));
            }
        }
        return null;
    }

    public static function credentialsForDomain(string $domain): Credentials {
        $cpuser = self::cpuserForDomain($domain);
        return self::credentialsForUser($cpuser, $domain);
    }

    public static function credentialsForUser(?string $cpuser, ?string $domain = null): Credentials {
        if ($cpuser && Config::get('allow_user_cloudflare', true) && UserConfig::isConnected($cpuser)) {
            return UserConfig::credentials($cpuser);
        }
        return Credentials::fromEncryptedConfig(Config::load());
    }

    public static function proxiedForDomain(string $domain): bool {
        $cpuser = self::cpuserForDomain($domain);
        if ($cpuser && UserConfig::isConnected($cpuser)) {
            return (bool) UserConfig::load($cpuser)['proxied'];
        }
        return (bool) Config::get('proxied', true);
    }

    public static function ttlForDomain(string $domain): int {
        $cpuser = self::cpuserForDomain($domain);
        if ($cpuser && UserConfig::isConnected($cpuser)) {
            return max(1, (int) UserConfig::load($cpuser)['ttl']);
        }
        return max(1, (int) Config::get('ttl', 1));
    }
}
