<?php

namespace App\Helpers;

use Carbon\Carbon;

class ThaiDateHelper
{
    public static function format($date, $time = false, $short = false)
    {
        if (!$date) return '-';
        
        $carbon = Carbon::parse($date);
        $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $thaiMonthsShort = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        
        // Use short month if time is requested or explicitly requested
        $useShort = $time || $short;
        $month = $useShort ? $thaiMonthsShort[$carbon->month] : $thaiMonths[$carbon->month];
        $timeStr = $time ? ' ' . $carbon->format('H:i') : '';
        
        return $carbon->day . ' ' . $month . ' ' . ($carbon->year + 543) . $timeStr;
    }

    public static function formatDateTime($date)
    {
        return self::format($date, true);
    }
}
