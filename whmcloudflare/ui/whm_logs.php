<?php
/** @var callable $h */

$logFile = Paths::logs() . '/whmcloudflare.log';
$logLines = [];
if (is_file($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $logLines = array_slice($lines, -50);
}
?>
<h4><?php echo $h(Language::get('log_tail')); ?></h4>
<pre class="whmcf-log"><?php
echo $logLines ? $h(implode("\n", $logLines)) : $h(Language::get('no_logs'));
?></pre>
