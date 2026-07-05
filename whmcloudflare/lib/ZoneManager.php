<?php

final class ZoneManager {
    public static function upsertARecord(string $domain, string $ip): array {
        return SyncService::syncDomain($domain, $ip);
    }

    public static function deleteARecord(string $domain): array {
        if (!Config::get('auto_delete_dns', true)) {
            return ['ok' => false, 'message' => 'Auto delete disabled'];
        }
        $auth = AccountContext::credentialsForDomain($domain);
        if (!$auth->isConfigured()) {
            return ['ok' => false, 'message' => 'Not configured'];
        }
        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found'];
        }
        $fqdn = rtrim(strtolower($domain), '.') . '.';
        $records = CloudflareAPI::listDnsRecords($zoneId, $auth, $fqdn, 'A');
        if (empty($records[0]['id'])) {
            return ['ok' => true, 'message' => 'No A record'];
        }
        $response = CloudflareAPI::deleteDnsRecord($zoneId, $records[0]['id'], $auth);
        if (!empty($response['success'])) {
            Logger::info('DNS deleted', ['domain' => $domain]);
            return ['ok' => true, 'message' => 'Deleted'];
        }
        return ['ok' => false, 'message' => CloudflareAPI::errorMessage($response)];
    }
}
