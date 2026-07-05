<?php
/** @var callable $h */
/** @var string $cpToken */

$connected = UserConfig::listUsers();
?>
<p><?php echo $h(Language::get('accounts_intro')); ?></p>

<form method="post" style="margin-bottom:15px;" onsubmit="return whmcfSubmit(this);">
    <input type="hidden" name="cp_security_token" value="<?php echo $h($cpToken); ?>">
    <input type="hidden" name="do" value="accounts">
    <button type="submit" name="action" value="sync_all" class="btn btn-default"><?php echo $h(Language::get('sync_all')); ?></button>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th><?php echo $h(Language::get('col_user')); ?></th>
            <th><?php echo $h(Language::get('col_domain')); ?></th>
            <th><?php echo $h(Language::get('col_status')); ?></th>
            <th><?php echo $h(Language::get('col_last_sync')); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$connected): ?>
        <tr><td colspan="5"><?php echo $h(Language::get('no_connected_users')); ?></td></tr>
    <?php else: foreach ($connected as $user):
        $ucfg = UserConfig::load($user);
        $domain = AccountContext::primaryDomain($user) ?? '-';
    ?>
        <tr>
            <td><?php echo $h($user); ?></td>
            <td><?php echo $h($domain); ?></td>
            <td><span class="label label-success"><?php echo $h(Language::get('connected')); ?></span></td>
            <td><?php echo $h((string) ($ucfg['last_sync'] ?? '-')); ?></td>
            <td>
                <form method="post" style="display:inline" onsubmit="return whmcfSubmit(this);">
                    <input type="hidden" name="cp_security_token" value="<?php echo $h($cpToken); ?>">
                    <input type="hidden" name="do" value="accounts">
                    <input type="hidden" name="cpuser" value="<?php echo $h($user); ?>">
                    <button type="submit" name="action" value="sync_user" class="btn btn-xs btn-primary"><?php echo $h(Language::get('sync_now')); ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
