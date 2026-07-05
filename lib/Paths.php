<?php

final class Paths {
    public const ADDON = '/var/cpanel/addons/whmcloudflare';
    public const CGI   = '/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare';

    public static function root(): string {
        return is_dir(self::ADDON) ? self::ADDON : dirname(__DIR__);
    }

    public static function lib(string $file): string {
        return self::root() . '/lib/' . $file;
    }

    public static function config(string $file = 'settings.json'): string {
        return self::root() . '/config/' . $file;
    }

    public static function logs(): string {
        return self::root() . '/logs';
    }

    public static function langFile(string $code): string {
        return self::root() . '/lang/' . $code . '.php';
    }
}
