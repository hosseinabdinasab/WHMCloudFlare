<?php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/lib/Bootstrap.php';

Security::requireRoot();

$cfg = Config::load();
Language::init((string) ($cfg['language'] ?? 'en'));

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
            $overrides = [
                'api_token' => trim((string) ($_POST['api_token'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'global_api_key' => trim((string) ($_POST['global_api_key'] ?? '')),
            ];
            if ($overrides['api_token'] === '') {
                $overrides['api_token'] = Config::apiToken();
            }
            if ($overrides['global_api_key'] === '') {
                $overrides['global_api_key'] = Config::globalApiKey();
            }
            if ($overrides['email'] === '') {
                $overrides['email'] = Config::email();
            }
            $result = CloudflareAPI::testConnection($overrides);
            if ($result['ok']) {
                $flash = Language::get('test_ok', ['message' => $result['message']]);
                $flashType = 'success';
            } else {
                $flash = Language::get('test_fail', ['message' => $result['message']]);
                $flashType = 'error';
            }
        } else {
            $new = [
                'enabled' => !empty($_POST['enabled']),
                'auth_mode' => ($_POST['auth_mode'] ?? 'token') === 'global' ? 'global' : 'token',
                'email' => trim((string) ($_POST['email'] ?? '')),
                'auto_create_dns' => !empty($_POST['auto_create_dns']),
                'auto_delete_dns' => !empty($_POST['auto_delete_dns']),
                'auto_update_ip' => !empty($_POST['auto_update_ip']),
                'proxied' => !empty($_POST['proxied']),
                'ttl' => max(1, (int) ($_POST['ttl'] ?? 1)),
                'language' => in_array($_POST['language'] ?? 'en', ['en', 'fa'], true) ? $_POST['language'] : 'en',
            ];
            $token = trim((string) ($_POST['api_token'] ?? ''));
            if ($token !== '') {
                $new['api_token'] = $token;
            }
            $key = trim((string) ($_POST['global_api_key'] ?? ''));
            if ($key !== '') {
                $new['global_api_key'] = $key;
            }
            Config::save(array_merge(Config::load(), $new));
            $cfg = Config::load();
            Language::init((string) ($cfg['language'] ?? 'en'));
            $flash = Language::get('saved');
            $flashType = 'success';
        }
    }
}

$authMode = (string) ($cfg['auth_mode'] ?? 'token');
$logFile = Paths::logs() . '/whmcloudflare.log';
$logLines = [];
if (is_file($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $logLines = array_slice($lines, -30);
}

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
?>
<div class="whmcf-panel">
<p><?php echo $h(Language::get('subtitle')); ?></p>

<?php if ($flash): ?>
<div class="alert alert-<?php echo $flashType === 'error' ? 'danger' : ($flashType === 'success' ? 'success' : 'info'); ?>">
    <?php echo $h($flash); ?>
</div>
<?php endif; ?>

<form method="post" class="form-horizontal" onsubmit="return whmcfSubmit(this);">
    <input type="hidden" name="cp_security_token" value="<?php echo $h($cpToken); ?>">

    <h3><?php echo $h(Language::get('settings')); ?></h3>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('enabled')); ?></label>
        <div class="col-sm-9">
            <input type="checkbox" name="enabled" value="1" <?php echo !empty($cfg['enabled']) ? 'checked' : ''; ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auth_mode')); ?></label>
        <div class="col-sm-9">
            <label class="radio-inline">
                <input type="radio" name="auth_mode" value="token" <?php echo $authMode !== 'global' ? 'checked' : ''; ?>>
                <?php echo $h(Language::get('auth_token')); ?>
            </label>
            <label class="radio-inline">
                <input type="radio" name="auth_mode" value="global" <?php echo $authMode === 'global' ? 'checked' : ''; ?>>
                <?php echo $h(Language::get('auth_global')); ?>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('api_token')); ?></label>
        <div class="col-sm-9">
            <input type="password" class="form-control" name="api_token" autocomplete="new-password" placeholder="<?php echo Config::apiToken() !== '' ? '********' : ''; ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('email')); ?></label>
        <div class="col-sm-9">
            <input type="email" class="form-control" name="email" value="<?php echo $h((string) ($cfg['email'] ?? '')); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('global_api_key')); ?></label>
        <div class="col-sm-9">
            <input type="password" class="form-control" name="global_api_key" autocomplete="new-password" placeholder="<?php echo Config::globalApiKey() !== '' ? '********' : ''; ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_create_dns')); ?></label>
        <div class="col-sm-9">
            <input type="checkbox" name="auto_create_dns" value="1" <?php echo !empty($cfg['auto_create_dns']) ? 'checked' : ''; ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_delete_dns')); ?></label>
        <div class="col-sm-9">
            <input type="checkbox" name="auto_delete_dns" value="1" <?php echo !empty($cfg['auto_delete_dns']) ? 'checked' : ''; ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_update_ip')); ?></label>
        <div class="col-sm-9">
            <input type="checkbox" name="auto_update_ip" value="1" <?php echo !empty($cfg['auto_update_ip']) ? 'checked' : ''; ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('proxied')); ?></label>
        <div class="col-sm-9">
            <input type="checkbox" name="proxied" value="1" <?php echo !empty($cfg['proxied']) ? 'checked' : ''; ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('ttl')); ?></label>
        <div class="col-sm-9">
            <input type="number" class="form-control" name="ttl" min="1" value="<?php echo (int) ($cfg['ttl'] ?? 1); ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('language')); ?></label>
        <div class="col-sm-9">
            <select name="language" class="form-control">
                <option value="en" <?php echo ($cfg['language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                <option value="fa" <?php echo ($cfg['language'] ?? 'en') === 'fa' ? 'selected' : ''; ?>>فارسی</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" name="action" value="save" class="btn btn-primary"><?php echo $h(Language::get('save')); ?></button>
            <button type="submit" name="action" value="test" class="btn btn-default"><?php echo $h(Language::get('test')); ?></button>
        </div>
    </div>
</form>

<h3><?php echo $h(Language::get('log_tail')); ?></h3>
<pre class="whmcf-log"><?php
if ($logLines) {
    echo $h(implode("\n", $logLines));
} else {
    echo $h(Language::get('no_logs'));
}
?></pre>
</div>
