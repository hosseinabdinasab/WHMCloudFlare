<?php
/** @var callable $h */
/** @var bool $connected */
/** @var string|null $domain */
/** @var array $cfg */
?>
<div style="margin-top:15px;">
<?php if ($connected): ?>
    <p><span class="label label-success"><?php echo $h(Language::get('connected')); ?></span></p>
    <p><?php echo $h(Language::get('last_sync')); ?>: <?php echo $h((string) ($cfg['last_sync'] ?? '-')); ?></p>
    <form method="post" style="display:inline">
        <input type="hidden" name="do" value="dashboard">
        <button type="submit" name="action" value="sync" class="btn btn-primary"><?php echo $h(Language::get('sync_now')); ?></button>
    </form>
    <a href="?do=dns" class="btn btn-default"><?php echo $h(Language::get('manage_dns')); ?></a>
    <a href="?do=monitoring" class="btn btn-default"><?php echo $h(Language::get('view_monitoring')); ?></a>
<?php else: ?>
    <p><?php echo $h(Language::get('not_connected')); ?></p>
    <a href="?do=connect" class="btn btn-primary"><?php echo $h(Language::get('connect_cloudflare')); ?></a>
<?php endif; ?>
</div>
