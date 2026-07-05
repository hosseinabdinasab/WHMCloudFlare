<?php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/lib/Bootstrap.php';

Security::requireRoot();

$cfg = Config::load();
Language::init((string) ($cfg['language'] ?? 'en'));

$do = preg_replace('/[^a-z_]/', '', $_GET['do'] ?? $_POST['do'] ?? 'settings');
if (!in_array($do, ['settings', 'accounts', 'logs'], true)) {
    $do = 'settings';
}

$flash = null;
$flashType = 'info';
$cpToken = Security::cpSecurityToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyWhmToken($_POST['cp_security_token'] ?? null)) {
        $flash = Language::get('csrf_error');
        $flashType = 'error';
    } else {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'test') {
            $auth = Credentials::fromArray([
                'api_token' => trim((string) ($_POST['api_token'] ?? '')) ?: Config::apiToken(),
                'email' => trim((string) ($_POST['email'] ?? '')) ?: Config::email(),
                'global_api_key' => trim((string) ($_POST['global_api_key'] ?? '')) ?: Config::globalApiKey(),
            ]);
            $result = CloudflareAPI::testConnection($auth);
            $flash = Language::get($result['ok'] ? 'test_ok' : 'test_fail', ['message' => $result['message']]);
            $flashType = $result['ok'] ? 'success' : 'error';
        } elseif ($action === 'sync_user' && !empty($_POST['cpuser'])) {
            $cpuser = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['cpuser']));
            $result = SyncService::syncUser($cpuser);
            $flash = $result['message'];
            $flashType = $result['ok'] ? 'success' : 'error';
            $do = 'accounts';
        } elseif ($action === 'sync_all') {
            $count = 0;
            foreach (SyncService::syncAllConnected() as $r) {
                if ($r['ok']) {
                    $count++;
                }
            }
            $flash = Language::get('sync_all_done', ['count' => $count]);
            $flashType = 'success';
            $do = 'accounts';
        } else {
            $new = [
                'enabled' => !empty($_POST['enabled']),
                'allow_user_cloudflare' => !empty($_POST['allow_user_cloudflare']),
                'user_auto_sync' => !empty($_POST['user_auto_sync']),
                'auth_mode' => ($_POST['auth_mode'] ?? 'token') === 'global' ? 'global' : 'token',
                'email' => trim((string) ($_POST['email'] ?? '')),
                'auto_create_dns' => !empty($_POST['auto_create_dns']),
                'auto_delete_dns' => !empty($_POST['auto_delete_dns']),
                'auto_update_ip' => !empty($_POST['auto_update_ip']),
                'proxied' => !empty($_POST['proxied']),
                'ttl' => max(1, (int) ($_POST['ttl'] ?? 1)),
                'language' => in_array($_POST['language'] ?? 'en', ['en', 'fa'], true) ? $_POST['language'] : 'en',
            ];
            if (trim((string) ($_POST['api_token'] ?? '')) !== '') {
                $new['api_token'] = trim((string) $_POST['api_token']);
            }
            if (trim((string) ($_POST['global_api_key'] ?? '')) !== '') {
                $new['global_api_key'] = trim((string) $_POST['global_api_key']);
            }
            Config::save(array_merge(Config::load(), $new));
            $cfg = Config::load();
            Language::init((string) ($cfg['language'] ?? 'en'));
            $flash = Language::get('saved');
            $flashType = 'success';
        }
    }
}

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};

include __DIR__ . '/ui/whm_layout.php';
