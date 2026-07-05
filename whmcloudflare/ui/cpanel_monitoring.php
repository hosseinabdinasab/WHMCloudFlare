<?php
/** @var callable $h */
/** @var array|null $stats */
/** @var string|null $domain */
?>
<div style="margin-top:15px;">
<h4><?php echo $h(Language::get('monitoring_title')); ?> — <?php echo $h($domain ?? ''); ?></h4>
<?php if (!$stats || empty($stats['ok'])): ?>
<p class="text-muted"><?php echo $h($stats['message'] ?? Language::get('monitoring_unavailable')); ?></p>
<?php else: ?>
<table class="table">
    <tr><th><?php echo $h(Language::get('zone_status')); ?></th><td><?php echo $h((string) $stats['zone']['status']); ?></td></tr>
    <tr><th><?php echo $h(Language::get('zone_plan')); ?></th><td><?php echo $h((string) $stats['zone']['plan']); ?></td></tr>
    <tr><th><?php echo $h(Language::get('zone_paused')); ?></th><td><?php echo !empty($stats['zone']['paused']) ? $h(Language::get('yes')) : $h(Language::get('no')); ?></td></tr>
</table>
<h5><?php echo $h(Language::get('analytics_7d')); ?></h5>
<?php if (!empty($stats['analytics']['available'])): ?>
<table class="table table-bordered">
    <tr><th><?php echo $h(Language::get('requests')); ?></th><td><?php echo number_format((int) $stats['analytics']['requests_7d']); ?></td></tr>
    <tr><th><?php echo $h(Language::get('bandwidth')); ?></th><td><?php echo $h(Format::bytes((int) $stats['analytics']['bandwidth_7d'])); ?></td></tr>
    <tr><th><?php echo $h(Language::get('threats')); ?></th><td><?php echo number_format((int) $stats['analytics']['threats_7d']); ?></td></tr>
</table>
<?php else: ?>
<p class="text-muted"><?php echo $h($stats['analytics']['message'] ?: Language::get('analytics_na')); ?></p>
<?php endif; ?>
<?php endif; ?>
</div>