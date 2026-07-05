<?php
/** @var callable $h */
/** @var array $cfg */
/** @var string $cpToken */
$authMode = (string) ($cfg['auth_mode'] ?? 'token');
?>
<p><?php echo $h(Language::get('subtitle')); ?></p>

<form method="post" class="form-horizontal" onsubmit="return whmcfSubmit(this);">
    <input type="hidden" name="cp_security_token" value="<?php echo $h($cpToken); ?>">
    <input type="hidden" name="do" value="settings">

    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('enabled')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="enabled" value="1" <?php echo !empty($cfg['enabled']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('allow_user_cloudflare')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="allow_user_cloudflare" value="1" <?php echo !empty($cfg['allow_user_cloudflare']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('user_auto_sync')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="user_auto_sync" value="1" <?php echo !empty($cfg['user_auto_sync']) ? 'checked' : ''; ?>></div>
    </div>

    <h4><?php echo $h(Language::get('server_credentials')); ?></h4>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auth_mode')); ?></label>
        <div class="col-sm-9">
            <label class="radio-inline"><input type="radio" name="auth_mode" value="token" <?php echo $authMode !== 'global' ? 'checked' : ''; ?>> <?php echo $h(Language::get('auth_token')); ?></label>
            <label class="radio-inline"><input type="radio" name="auth_mode" value="global" <?php echo $authMode === 'global' ? 'checked' : ''; ?>> <?php echo $h(Language::get('auth_global')); ?></label>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('api_token')); ?></label>
        <div class="col-sm-9"><input type="password" class="form-control" name="api_token" autocomplete="new-password" placeholder="<?php echo Config::apiToken() !== '' ? '********' : ''; ?>"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('email')); ?></label>
        <div class="col-sm-9"><input type="email" class="form-control" name="email" value="<?php echo $h((string) ($cfg['email'] ?? '')); ?>"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('global_api_key')); ?></label>
        <div class="col-sm-9"><input type="password" class="form-control" name="global_api_key" autocomplete="new-password" placeholder="<?php echo Config::globalApiKey() !== '' ? '********' : ''; ?>"></div>
    </div>

    <h4><?php echo $h(Language::get('automation')); ?></h4>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_create_dns')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="auto_create_dns" value="1" <?php echo !empty($cfg['auto_create_dns']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_delete_dns')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="auto_delete_dns" value="1" <?php echo !empty($cfg['auto_delete_dns']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('auto_update_ip')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="auto_update_ip" value="1" <?php echo !empty($cfg['auto_update_ip']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('proxied')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="proxied" value="1" <?php echo !empty($cfg['proxied']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label"><?php echo $h(Language::get('ttl')); ?></label>
        <div class="col-sm-9"><input type="number" class="form-control" name="ttl" min="1" value="<?php echo (int) ($cfg['ttl'] ?? 1); ?>"></div>
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
