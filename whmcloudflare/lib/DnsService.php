<?php

final class DnsService {
    public static function listForDomain(string $domain): array {
        $auth = AccountContext::credentialsForDomain($domain);
        if (!$auth->isConfigured()) {
            return ['ok' => false, 'message' => 'Cloudflare not configured', 'records' => []];
        }
        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found', 'records' => []];
        }
        $records = CloudflareAPI::listDnsRecords($zoneId, $auth);
        return ['ok' => true, 'zone_id' => $zoneId, 'records' => $records];
    }

    public static function toggleProxy(string $domain, string $recordId, bool $proxied): array {
        $auth = AccountContext::credentialsForDomain($domain);
        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found'];
        }
        $response = CloudflareAPI::patchDnsRecord($zoneId, $recordId, ['proxied' => $proxied], $auth);
        if (!empty($response['success'])) {
            Logger::info('Proxy toggled', ['domain' => $domain, 'record' => $recordId, 'proxied' => $proxied]);
            return ['ok' => true, 'message' => $proxied ? 'Proxy enabled' : 'Proxy disabled'];
        }
        return ['ok' => false, 'message' => CloudflareAPI::errorMessage($response)];
    }

    public static function deleteRecord(string $domain, string $recordId): array {
        $auth = AccountContext::credentialsForDomain($domain);
        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found'];
        }
        $response = CloudflareAPI::deleteDnsRecord($zoneId, $recordId, $auth);
        if (!empty($response['success'])) {
            return ['ok' => true, 'message' => 'Record deleted'];
        }
        return ['ok' => false, 'message' => CloudflareAPI::errorMessage($response)];
    }
}
