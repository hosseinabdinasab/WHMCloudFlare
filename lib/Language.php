<?php

final class Language {
    private static ?array $strings = null;
    private static string $code = 'en';

    public static function init(string $code = 'en'): void {
        self::$code = in_array($code, ['en', 'fa'], true) ? $code : 'en';
        $file = Paths::langFile(self::$code);
        self::$strings = is_file($file) ? require $file : [];
    }

    public static function get(string $key, array $vars = []): string {
        if (self::$strings === null) {
            self::init();
        }
        $text = self::$strings[$key] ?? $key;
        foreach ($vars as $k => $v) {
            $text = str_replace('{' . $k . '}', (string) $v, $text);
        }
        return $text;
    }

    public static function code(): string {
        return self::$code;
    }
}
