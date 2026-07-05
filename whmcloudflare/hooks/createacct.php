#!/usr/local/cpanel/3rdparty/bin/php
<?php

require_once dirname(__DIR__) . '/lib/Bootstrap.php';

$data = HookData::readStdin();
Logger::info('Accounts::Create hook', ['data' => $data]);
WHMCloudFlare::onAccountCreate($data);
