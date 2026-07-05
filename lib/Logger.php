<?php

final class Logger {
    public static function info(string $message, array $context = []): void {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::write('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void {
        if (Config::get('log_level', 'info') !== 'debug') {
            return;
        }
        self::write('DEBUG', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void {
        $dir = Paths::logs();
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $line = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );
        file_put_contents($dir . '/whmcloudflare.log', $line, FILE_APPEND | LOCK_EX);
    }
}
