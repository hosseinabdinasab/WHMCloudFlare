#!/usr/local/cpanel/3rdparty/bin/php
<?php

require_once dirname(__DIR__) . '/lib/Bootstrap.php';

$data = HookData::readStdin();
Logger::info('Accounts::Remove hook', ['data' => $data]);
WHMCloudFlare::onAccountRemove($data);
