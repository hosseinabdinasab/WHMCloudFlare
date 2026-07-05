#!/usr/local/cpanel/3rdparty/bin/php
<?php

require_once '/var/cpanel/addons/whmcloudflare/lib/Bootstrap.php';

$data = HookData::readStdin();
Logger::info('Accounts::SiteIP::set hook', ['data' => $data]);
WHMCloudFlare::onSiteIpSet($data);
