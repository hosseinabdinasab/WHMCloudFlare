<?php
/** @var callable $h */
/** @var string $do */
/** @var string $cpuser */
/** @var string|null $domain */
/** @var string|null $flash */
/** @var string $flashType */
/** @var array $cfg */
/** @var bool $connected */
/** @var array|null $stats */
/** @var array|null $dns */
?>
<div class="whmcf-cpanel">
<h2><?php echo $h(Language::get('cpanel_title')); ?></h2>
<p><?php echo $h(Language::get('cpanel_subtitle')); ?> <strong><?php echo $h($domain ?? '-'); ?></strong></p>

<ul class="nav nav-tabs">
    <li class="<?php echo $do === 'dashboard' ? 'active' : ''; ?>"><a href="?do=dashboard"><?php echo $h(Language::get('tab_dashboard')); ?></a></li>
    <li class="<?php echo $do === 'connect' ? 'active' : ''; ?>"><a href="?do=connect"><?php echo $h(Language::get('tab_connect')); ?></a></li>
    <?php if ($connected): ?>
    <li class="<?php echo $do === 'dns' ? 'active' : ''; ?>"><a href="?do=dns"><?php echo $h(Language::get('tab_dns')); ?></a></li>
    <li class="<?php echo $do === 'monitoring' ? 'active' : ''; ?>"><a href="?do=monitoring"><?php echo $h(Language::get('tab_monitoring')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if ($flash): ?>
<div class="alert alert-<?php echo $flashType === 'error' ? 'danger' : ($flashType === 'success' ? 'success' : 'info'); ?>">
    <?php echo $h($flash); ?>
</div>
<?php endif; ?>

<?php
if ($do === 'connect') {
    include __DIR__ . '/cpanel_connect.php';
} elseif ($do === 'dns' && $connected) {
    include __DIR__ . '/cpanel_dns.php';
} elseif ($do === 'monitoring' && $connected) {
    include __DIR__ . '/cpanel_monitoring.php';
} else {
    include __DIR__ . '/cpanel_dashboard.php';
}
?>
</div>
