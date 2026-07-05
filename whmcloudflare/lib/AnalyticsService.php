<?php

final class AnalyticsService {
    public static function summaryForDomain(string $domain): array {
        $auth = AccountContext::credentialsForDomain($domain);
        if (!$auth->isConfigured()) {
            return ['ok' => false, 'message' => 'Not connected'];
        }
        $zoneId = CloudflareAPI::findZoneId($domain, $auth);
        if (!$zoneId) {
            return ['ok' => false, 'message' => 'Zone not found'];
        }

        $zone = CloudflareAPI::getZone($zoneId, $auth);
        $analytics = CloudflareAPI::getAnalyticsDashboard($zoneId, $auth);

        $requests = 0;
        $bandwidth = 0;
        $threats = 0;
        if (!empty($analytics['success']) && !empty($analytics['result']['totals'])) {
            $totals = $analytics['result']['totals'];
            $requests = (int) ($totals['requests']['all'] ?? 0);
            $bandwidth = (int) ($totals['bandwidth']['all'] ?? 0);
            $threats = (int) ($totals['threats']['all'] ?? 0);
        }

        return [
            'ok' => true,
            'zone' => [
                'name' => $zone['name'] ?? $domain,
                'status' => $zone['status'] ?? 'unknown',
                'plan' => $zone['plan']['name'] ?? 'unknown',
                'paused' => !empty($zone['paused']),
            ],
            'analytics' => [
                'available' => !empty($analytics['success']),
                'requests_7d' => $requests,
                'bandwidth_7d' => $bandwidth,
                'threats_7d' => $threats,
                'message' => empty($analytics['success']) ? CloudflareAPI::errorMessage($analytics) : '',
            ],
        ];
    }
}
