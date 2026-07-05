<?php
/** @var callable $h */
/** @var array|null $dns */
/** @var string|null $domain */
?>
<div style="margin-top:15px;">
<h4><?php echo $h(Language::get('dns_records')); ?> — <?php echo $h($domain ?? ''); ?></h4>
<?php if (!$dns || empty($dns['ok'])): ?>
<p class="text-muted"><?php echo $h($dns['message'] ?? Language::get('dns_load_fail')); ?></p>
<?php else: ?>
<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th><?php echo $h(Language::get('col_type')); ?></th>
            <th><?php echo $h(Language::get('col_name')); ?></th>
            <th><?php echo $h(Language::get('col_content')); ?></th>
            <th><?php echo $h(Language::get('col_proxy')); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($dns['records'] as $rec): ?>
        <tr>
            <td><?php echo $h((string) ($rec['type'] ?? '')); ?></td>
            <td><?php echo $h((string) ($rec['name'] ?? '')); ?></td>
            <td><code><?php echo $h((string) ($rec['content'] ?? '')); ?></code></td>
            <td>
                <?php if (!empty($rec['proxiable'])): ?>
                    <?php echo !empty($rec['proxied']) ? $h(Language::get('proxy_on')) : $h(Language::get('proxy_off')); ?>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td>
                <?php if (!empty($rec['proxiable']) && !empty($rec['id'])): ?>
                <form method="post" style="display:inline">
                    <input type="hidden" name="do" value="dns">
                    <input type="hidden" name="record_id" value="<?php echo $h($rec['id']); ?>">
                    <input type="hidden" name="proxied" value="<?php echo !empty($rec['proxied']) ? '0' : '1'; ?>">
                    <button type="submit" name="action" value="toggle_proxy" class="btn btn-xs btn-warning">
                        <?php echo $h(!empty($rec['proxied']) ? Language::get('disable_proxy') : Language::get('enable_proxy')); ?>
                    </button>
                </form>
                <?php endif; ?>
                <?php if (!empty($rec['id'])): ?>
                <form method="post" style="display:inline" onsubmit="return confirm('<?php echo $h(Language::get('delete_confirm')); ?>');">
                    <input type="hidden" name="do" value="dns">
                    <input type="hidden" name="record_id" value="<?php echo $h($rec['id']); ?>">
                    <button type="submit" name="action" value="delete_record" class="btn btn-xs btn-danger"><?php echo $h(Language::get('delete')); ?></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
