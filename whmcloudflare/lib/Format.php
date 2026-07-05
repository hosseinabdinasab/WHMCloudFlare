<?php

final class Format {
    public static function bytes(int $b): string {
        if ($b < 1024) {
            return $b . ' B';
        }
        if ($b < 1048576) {
            return round($b / 1024, 1) . ' KB';
        }
        if ($b < 1073741824) {
            return round($b / 1048576, 1) . ' MB';
        }
        return round($b / 1073741824, 2) . ' GB';
    }
}
