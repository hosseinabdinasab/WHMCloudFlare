<?php

final class CloudflareAPI {
    private const BASE = 'https://api.cloudflare.com/client/v4';

    public static function testConnection(array $overrides = []): array {
        $response = self::request('GET', '/user/tokens/verify', null, null, $overrides);
        if ($response['success']) {
            return ['ok' => true, 'message' => 'API token is valid.'];
        }
        $response = self::request('GET', '/user', null, null, $overrides);
        if ($response['success']) {
            return ['ok' => true, 'message' => 'Global API key is valid.'];
        }
        return ['ok' => false, 'message' => self::errorMessage($response)];
    }

    public static function findZoneId(string $domain): ?string {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);
        while (count($parts) >= 2) {
            $candidate = implode('.', $parts);
            $response = self::request('GET', '/zones', ['name' => $candidate, 'status' => 'active']);
            if ($response['success'] && !empty($response['result'][0]['id'])) {
                return $response['result'][0]['id'];
            }
            array_shift($parts);
        }
        return null;
    }

    public static function listDnsRecords(string $zoneId, string $name, string $type = 'A'): array {
        $response = self::request('GET', '/zones/' . $zoneId . '/dns_records', [
            'name' => $name,
            'type' => $type,
        ]);
        return ($response['success'] && is_array($response['result'])) ? $response['result'] : [];
    }

    public static function createDnsRecord(string $zoneId, string $name, string $ip, bool $proxied, int $ttl): array {
        return self::request('POST', '/zones/' . $zoneId . '/dns_records', null, [
            'type' => 'A',
            'name' => $name,
            'content' => $ip,
            'ttl' => $ttl,
            'proxied' => $proxied,
        ]);
    }

    public static function updateDnsRecord(string $zoneId, string $recordId, string $name, string $ip, bool $proxied, int $ttl): array {
        return self::request('PUT', '/zones/' . $zoneId . '/dns_records/' . $recordId, null, [
            'type' => 'A',
            'name' => $name,
            'content' => $ip,
            'ttl' => $ttl,
            'proxied' => $proxied,
        ]);
    }

    public static function deleteDnsRecord(string $zoneId, string $recordId): array {
        return self::request('DELETE', '/zones/' . $zoneId . '/dns_records/' . $recordId);
    }

    private static function request(string $method, string $path, ?array $query = null, ?array $body = null, array $authOverrides = []): array {
        $url = self::BASE . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $headers = self::authHeaders($authOverrides);
        $headers[] = 'Content-Type: application/json';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            Logger::error('Cloudflare cURL error', ['error' => $error]);
            return ['success' => false, 'errors' => [['message' => $error]]];
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'errors' => [['message' => 'Invalid JSON from Cloudflare API']]];
        }
        return $decoded;
    }

    private static function authHeaders(array $overrides = []): array {
        $token = (string) ($overrides['api_token'] ?? Config::apiToken());
        if ($token !== '') {
            return ['Authorization: Bearer ' . $token];
        }
        $email = (string) ($overrides['email'] ?? Config::email());
        $key = (string) ($overrides['global_api_key'] ?? Config::globalApiKey());
        if ($email !== '' && $key !== '') {
            return [
                'X-Auth-Email: ' . $email,
                'X-Auth-Key: ' . $key,
            ];
        }
        return [];
    }

    private static function errorMessage(array $response): string {
        if (!empty($response['errors'][0]['message'])) {
            return (string) $response['errors'][0]['message'];
        }
        return 'Cloudflare API request failed.';
    }
}
