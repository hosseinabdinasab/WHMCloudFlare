<?php

final class HookData {
    public static function readStdin(): array {
        $raw = stream_get_contents(STDIN);
        if (!$raw) {
            return [];
        }
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    public static function get(array $data, string $key) {
        if (isset($data[$key]) && $data[$key] !== '') {
            return $data[$key];
        }
        if (isset($data['data']) && is_array($data['data']) && isset($data['data'][$key]) && $data['data'][$key] !== '') {
            return $data['data'][$key];
        }
        return null;
    }
}
