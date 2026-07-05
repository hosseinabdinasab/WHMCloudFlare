#!/usr/local/cpanel/3rdparty/bin/php
<?php

header('Content-Type: text/html; charset=utf-8');

require_once '/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare/lib/Bootstrap.php';

$cpuser = Security::requireCpanelUser();
if (!Config::get('allow_user_cloudflare', true)) {
    echo '<div class="alert alert-warning">Cloudflare user integration is disabled by the server administrator.</div>';
    return;
}

$cfg = UserConfig::load($cpuser);
Language::init((string) (Config::get('language') ?? 'en'));

$domain = AccountContext::primaryDomain($cpuser);
$do = preg_replace('/[^a-z_]/', '', $_GET['do'] ?? $_POST['do'] ?? 'dashboard');
if (!in_array($do, ['dashboard', 'connect', 'dns', 'monitoring'], true)) {
    $do = 'dashboard';
}

$flash = null;
$flashType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'connect') {
        $existing = UserConfig::credentials($cpuser);
        $auth = Credentials::fromArray([
            'api_token' => trim((string) ($_POST['api_token'] ?? '')) ?: $existing->apiToken,
            'email' => trim((string) ($_POST['email'] ?? '')) ?: $existing->email,
            'global_api_key' => trim((string) ($_POST['global_api_key'] ?? '')) ?: $existing->globalApiKey,
        ]);
        $test = CloudflareAPI::testConnection($auth);
        if ($test['ok']) {
            $save = array_merge($cfg, [
                'connected' => true,
                'auth_mode' => ($_POST['auth_mode'] ?? 'token') === 'global' ? 'global' : 'token',
                'email' => trim((string) ($_POST['email'] ?? '')) ?: $existing->email,
                'auto_sync' => !empty($_POST['auto_sync']),
                'proxied' => !empty($_POST['proxied']),
                'ttl' => max(1, (int) ($_POST['ttl'] ?? 1)),
            ]);
            if (trim((string) ($_POST['api_token'] ?? '')) !== '') {
                $save['api_token'] = trim((string) $_POST['api_token']);
            }
            if (trim((string) ($_POST['global_api_key'] ?? '')) !== '') {
                $save['global_api_key'] = trim((string) $_POST['global_api_key']);
            }
            UserConfig::save($cpuser, $save);
            $cfg = UserConfig::load($cpuser);
            $flash = Language::get('connected_ok');
            $flashType = 'success';
            $do = 'dashboard';
        } else {
            $flash = Language::get('test_fail', ['message' => $test['message']]);
            $flashType = 'error';
            $do = 'connect';
        }
    } elseif ($action === 'disconnect') {
        UserConfig::save($cpuser, array_merge(UserConfig::load($cpuser), [
            'connected' => false,
            'api_token' => '',
            'global_api_key' => '',
        ]));
        $cfg = UserConfig::load($cpuser);
        $flash = Language::get('disconnected');
        $flashType = 'info';
        $do = 'connect';
    } elseif ($action === 'sync' && $domain) {
        $result = SyncService::syncDomain($domain);
        $flash = $result['message'];
        $flashType = $result['ok'] ? 'success' : 'error';
    } elseif ($action === 'toggle_proxy' && $domain && !empty($_POST['record_id'])) {
        $proxied = !empty($_POST['proxied']);
        $result = DnsService::toggleProxy($domain, (string) $_POST['record_id'], $proxied);
        $flash = $result['message'];
        $flashType = $result['ok'] ? 'success' : 'error';
        $do = 'dns';
    } elseif ($action === 'delete_record' && $domain && !empty($_POST['record_id'])) {
        $result = DnsService::deleteRecord($domain, (string) $_POST['record_id']);
        $flash = $result['message'];
        $flashType = $result['ok'] ? 'success' : 'error';
        $do = 'dns';
    }
}

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};

$connected = UserConfig::isConnected($cpuser);
$stats = ($connected && $domain) ? AnalyticsService::summaryForDomain($domain) : null;
$dns = ($connected && $domain) ? DnsService::listForDomain($domain) : null;

require '/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare/ui/cpanel_layout.php';
