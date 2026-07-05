<?php

final class SyncService {
    public static function syncDomain(string $domain, ?string $ip = null): array {
        $cpuser = AccountContext::cpuserForDomain($domain);
        $userOk = $cpuser && UserConfig::isConnected($cpuser);
        if (!Config::isEnabled() && !$userOk) {
            return ['ok' => false, 'message' => 'Integration disabled'];
        }

        $auth = AccountContext::credentialsForDomain($domain);
        if (!$auth->isConfigured()) {
            return ['ok' => false, 'message' => 'No Cloudflare credentials'];
        }

        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found in Cloudflare'];
        }

        if ($ip === null) {
            $ip = WHMAccount::ipForDomain($domain);
        }
        if (!$ip) {
            return ['ok' => false, 'message' => 'Could not resolve server IP'];
        }

        $fqdn = rtrim(strtolower($domain), '.') . '.';
        $proxied = AccountContext::proxiedForDomain($domain);
        $ttl = AccountContext::ttlForDomain($domain);
        $records = CloudflareAPI::listDnsRecords($zoneId, $auth, $fqdn, 'A');

        if (!empty($records[0]['id'])) {
            $response = CloudflareAPI::updateDnsRecord($zoneId, $records[0]['id'], [
                'type' => 'A',
                'name' => $fqdn,
                'content' => $ip,
                'ttl' => $ttl,
                'proxied' => $proxied,
            ], $auth);
            $action = 'updated';
        } else {
            $response = CloudflareAPI::createDnsRecord($zoneId, [
                'type' => 'A',
                'name' => $fqdn,
                'content' => $ip,
                'ttl' => $ttl,
                'proxied' => $proxied,
            ], $auth);
            $action = 'created';
        }

        if (!empty($response['success'])) {
            $cpuser = AccountContext::cpuserForDomain($domain);
            if ($cpuser) {
                $cfg = UserConfig::load($cpuser);
                $cfg['last_sync'] = date('c');
                $cfg['zone_id'] = $zoneId;
                UserConfig::save($cpuser, $cfg);
            }
            Logger::info('Sync ' . $action, ['domain' => $domain, 'ip' => $ip]);
            return ['ok' => true, 'message' => 'Synced A record (' . $action . ')', 'ip' => $ip];
        }

        return ['ok' => false, 'message' => CloudflareAPI::errorMessage($response)];
    }

    public static function syncUser(string $cpuser): array {
        $domain = AccountContext::primaryDomain($cpuser);
        if (!$domain) {
            return ['ok' => false, 'message' => 'Primary domain not found'];
        }
        return self::syncDomain($domain);
    }

    public static function syncAllConnected(): array {
        $results = [];
        foreach (UserConfig::listUsers() as $user) {
            $results[$user] = self::syncUser($user);
        }
        return $results;
    }
}
