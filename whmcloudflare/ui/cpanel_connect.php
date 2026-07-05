<?php
/** @var callable $h */
/** @var array $cfg */
/** @var bool $connected */
$authMode = (string) ($cfg['auth_mode'] ?? 'token');
?>
<div style="margin-top:15px;">
<h4><?php echo $h(Language::get('connect_cloudflare')); ?></h4>
<form method="post" class="form-horizontal">
    <input type="hidden" name="do" value="connect">
    <div class="form-group">
        <label class="col-sm-3"><?php echo $h(Language::get('api_token')); ?></label>
        <div class="col-sm-9"><input type="password" class="form-control" name="api_token" placeholder="<?php echo $connected ? '********' : ''; ?>"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3"><?php echo $h(Language::get('email')); ?></label>
        <div class="col-sm-9"><input type="email" class="form-control" name="email" value="<?php echo $h((string) ($cfg['email'] ?? '')); ?>"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3"><?php echo $h(Language::get('global_api_key')); ?></label>
        <div class="col-sm-9"><input type="password" class="form-control" name="global_api_key" placeholder="<?php echo $connected ? '********' : ''; ?>"></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3"><?php echo $h(Language::get('auto_sync')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="auto_sync" value="1" <?php echo !empty($cfg['auto_sync']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <label class="col-sm-3"><?php echo $h(Language::get('proxied')); ?></label>
        <div class="col-sm-9"><input type="checkbox" name="proxied" value="1" <?php echo !empty($cfg['proxied']) ? 'checked' : ''; ?>></div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" name="action" value="connect" class="btn btn-primary"><?php echo $h(Language::get('save_connect')); ?></button>
            <?php if ($connected): ?>
            <button type="submit" name="action" value="disconnect" class="btn btn-danger" onclick="return confirm('<?php echo $h(Language::get('disconnect_confirm')); ?>');"><?php echo $h(Language::get('disconnect')); ?></button>
            <?php endif; ?>
        </div>
    </div>
</form>
</div>
