<?php

final class ZoneManager {
    public static function upsertARecord(string $domain, string $ip): array {
        if (!Config::isEnabled()) {
            return ['ok' => false, 'message' => 'Plugin disabled'];
        }

        $zoneId = CloudflareAPI::findZoneId($domain);
        if (!$zoneId) {
            Logger::info('No Cloudflare zone for domain', ['domain' => $domain]);
            return ['ok' => false, 'message' => 'Zone not found'];
        }

        $fqdn = rtrim(strtolower($domain), '.') . '.';
        $records = CloudflareAPI::listDnsRecords($zoneId, $fqdn, 'A');
        $proxied = (bool) Config::get('proxied', true);
        $ttl = (int) Config::get('ttl', 1);

        if (!empty($records[0]['id'])) {
            $response = CloudflareAPI::updateDnsRecord($zoneId, $records[0]['id'], $fqdn, $ip, $proxied, $ttl);
            $action = 'updated';
        } else {
            $response = CloudflareAPI::createDnsRecord($zoneId, $fqdn, $ip, $proxied, $ttl);
            $action = 'created';
        }

        if (!empty($response['success'])) {
            Logger::info('DNS record ' . $action, ['domain' => $domain, 'ip' => $ip]);
            return ['ok' => true, 'message' => 'DNS ' . $action];
        }

        $msg = $response['errors'][0]['message'] ?? 'DNS update failed';
        Logger::error($msg, ['domain' => $domain, 'ip' => $ip]);
        return ['ok' => false, 'message' => $msg];
    }

    public static function deleteARecord(string $domain): array {
        if (!Config::isEnabled()) {
            return ['ok' => false, 'message' => 'Plugin disabled'];
        }

        $zoneId = CloudflareAPI::findZoneId($domain);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found'];
        }

        $fqdn = rtrim(strtolower($domain), '.') . '.';
        $records = CloudflareAPI::listDnsRecords($zoneId, $fqdn, 'A');
        if (empty($records[0]['id'])) {
            return ['ok' => true, 'message' => 'No A record to delete'];
        }

        $response = CloudflareAPI::deleteDnsRecord($zoneId, $records[0]['id']);
        if (!empty($response['success'])) {
            Logger::info('DNS record deleted', ['domain' => $domain]);
            return ['ok' => true, 'message' => 'DNS deleted'];
        }

        $msg = $response['errors'][0]['message'] ?? 'DNS delete failed';
        Logger::error($msg, ['domain' => $domain]);
        return ['ok' => false, 'message' => $msg];
    }
}
