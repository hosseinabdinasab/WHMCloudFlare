<?php

final class Paths {
    public const PLUGIN_ROOT = '/usr/local/cpanel/whostmgr/docroot/cgi/whmcloudflare';

    public static function root(): string {
        if (is_dir(self::PLUGIN_ROOT)) {
            return self::PLUGIN_ROOT;
        }
        $dev = dirname(__DIR__);
        return is_file($dev . '/index.php') ? $dev : self::PLUGIN_ROOT;
    }

    public static function lib(string $file): string {
        return self::root() . '/lib/' . $file;
    }

    public static function data(): string {
        return self::root() . '/data';
    }

    public static function config(string $file = 'settings.json'): string {
        return self::data() . '/config/' . $file;
    }

    public static function logs(): string {
        return self::data() . '/logs';
    }

    public static function langFile(string $code): string {
        return self::root() . '/lang/' . $code . '.php';
    }
}
