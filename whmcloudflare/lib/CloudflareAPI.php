<?php

final class CloudflareAPI {
    private const BASE = 'https://api.cloudflare.com/client/v4';

    public static function testConnection(Credentials $auth): array {
        $response = self::request('GET', '/user/tokens/verify', null, null, $auth);
        if ($response['success']) {
            return ['ok' => true, 'message' => 'API token is valid.'];
        }
        $response = self::request('GET', '/user', null, null, $auth);
        if ($response['success']) {
            return ['ok' => true, 'message' => 'Global API key is valid.'];
        }
        return ['ok' => false, 'message' => self::errorMessage($response)];
    }

    public static function findZoneId(string $domain, Credentials $auth): ?string {
        $domain = strtolower(trim($domain));
        $parts = explode('.', $domain);
        while (count($parts) >= 2) {
            $candidate = implode('.', $parts);
            $response = self::request('GET', '/zones', ['name' => $candidate, 'status' => 'active'], null, $auth);
            if ($response['success'] && !empty($response['result'][0]['id'])) {
                return $response['result'][0]['id'];
            }
            array_shift($parts);
        }
        return null;
    }

    public static function getZone(string $zoneId, Credentials $auth): ?array {
        $response = self::request('GET', '/zones/' . $zoneId, null, null, $auth);
        return ($response['success'] && !empty($response['result'])) ? $response['result'] : null;
    }

    public static function listDnsRecords(string $zoneId, Credentials $auth, ?string $name = null, ?string $type = null): array {
        $query = ['per_page' => 100];
        if ($name !== null) {
            $query['name'] = $name;
        }
        if ($type !== null) {
            $query['type'] = $type;
        }
        $all = [];
        $page = 1;
        do {
            $query['page'] = $page;
            $response = self::request('GET', '/zones/' . $zoneId . '/dns_records', $query, null, $auth);
            if (!$response['success'] || !is_array($response['result'])) {
                break;
            }
            $all = array_merge($all, $response['result']);
            $total = (int) ($response['result_info']['total_pages'] ?? 1);
            $page++;
        } while ($page <= $total && $page <= 20);
        return $all;
    }

    public static function createDnsRecord(string $zoneId, array $record, Credentials $auth): array {
        return self::request('POST', '/zones/' . $zoneId . '/dns_records', null, $record, $auth);
    }

    public static function updateDnsRecord(string $zoneId, string $recordId, array $record, Credentials $auth): array {
        return self::request('PUT', '/zones/' . $zoneId . '/dns_records/' . $recordId, null, $record, $auth);
    }

    public static function patchDnsRecord(string $zoneId, string $recordId, array $fields, Credentials $auth): array {
        return self::request('PATCH', '/zones/' . $zoneId . '/dns_records/' . $recordId, null, $fields, $auth);
    }

    public static function deleteDnsRecord(string $zoneId, string $recordId, Credentials $auth): array {
        return self::request('DELETE', '/zones/' . $zoneId . '/dns_records/' . $recordId, null, null, $auth);
    }

    public static function getAnalyticsDashboard(string $zoneId, Credentials $auth, int $sinceMinutes = 10080): array {
        $until = time();
        $since = $until - ($sinceMinutes * 60);
        return self::request('GET', '/zones/' . $zoneId . '/analytics/dashboard', [
            'since' => $since,
            'until' => $until,
            'continuous' => 'true',
        ], null, $auth);
    }

    private static function request(string $method, string $path, ?array $query = null, ?array $body = null, ?Credentials $auth = null): array {
        $url = self::BASE . $path;
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $headers = self::authHeaders($auth ?? Credentials::fromEncryptedConfig(Config::load()));
        $headers[] = 'Content-Type: application/json';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
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

    private static function authHeaders(Credentials $auth): array {
        if ($auth->apiToken !== '') {
            return ['Authorization: Bearer ' . $auth->apiToken];
        }
        if ($auth->email !== '' && $auth->globalApiKey !== '') {
            return [
                'X-Auth-Email: ' . $auth->email,
                'X-Auth-Key: ' . $auth->globalApiKey,
            ];
        }
        return [];
    }

    public static function errorMessage(array $response): string {
        if (!empty($response['errors'][0]['message'])) {
            return (string) $response['errors'][0]['message'];
        }
        return 'Cloudflare API request failed.';
    }
}
