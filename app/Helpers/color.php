<?php

if (!function_exists('lightenColor')) {
    function lightenColor($hex, $percent = 20) {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + 255 * ($percent / 100));
        $g = min(255, $g + 255 * ($percent / 100));
        $b = min(255, $b + 255 * ($percent / 100));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
