<?php
/** @var callable $h */
/** @var string $do */
/** @var string|null $flash */
/** @var string $flashType */
/** @var string $cpToken */
/** @var array $cfg */
?>
<div class="whmcf-panel">
<ul class="nav nav-tabs whmcf-tabs">
    <li class="<?php echo $do === 'settings' ? 'active' : ''; ?>">
        <a href="?do=settings"><?php echo $h(Language::get('tab_settings')); ?></a>
    </li>
    <li class="<?php echo $do === 'accounts' ? 'active' : ''; ?>">
        <a href="?do=accounts"><?php echo $h(Language::get('tab_accounts')); ?></a>
    </li>
    <li class="<?php echo $do === 'logs' ? 'active' : ''; ?>">
        <a href="?do=logs"><?php echo $h(Language::get('tab_logs')); ?></a>
    </li>
</ul>

<?php if ($flash): ?>
<div class="alert alert-<?php echo $flashType === 'error' ? 'danger' : ($flashType === 'success' ? 'success' : 'info'); ?> whmcf-flash">
    <?php echo $h($flash); ?>
</div>
<?php endif; ?>

<?php
if ($do === 'accounts') {
    include __DIR__ . '/whm_accounts.php';
} elseif ($do === 'logs') {
    include __DIR__ . '/whm_logs.php';
} else {
    include __DIR__ . '/whm_settings.php';
}
?>
</div>
