<?php

namespace App\Support;

class TimeFormat
{
    public static function minutes(int $minutes): string
    {
        if ($minutes < 1) {
            return '0m';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) {
            return $h.'h '.$m.'m';
        }
        if ($h > 0) {
            return $h.'h';
        }

        return $m.'m';
    }

    public static function decimalHours(int $minutes): string
    {
        return number_format($minutes / 60, 1);
    }
}
