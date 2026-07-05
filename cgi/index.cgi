#!/usr/local/cpanel/3rdparty/bin/php -q
<?php
/**
 * WHM CGI entry — must be PHP with shebang (not shell+exec php CLI).
 * CLI mode does not emit Content-Type; browser shows raw HTML as text.
 */
require '/var/cpanel/addons/whmcloudflare/ui/index.php';
