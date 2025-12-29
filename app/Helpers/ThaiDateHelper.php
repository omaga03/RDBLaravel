<?php

namespace App\Helpers;

use Carbon\Carbon;

class ThaiDateHelper
{
    public static function format($date, $time = false, $short = false)
    {
        if (!$date) return '-';
        
        // If input is already a Thai formatted string, handle it specially
        $thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $thaiMonthsShort = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        
        // Check if the input is already a Thai formatted string
        foreach ($thaiMonths as $index => $month) {
            if (is_string($date) && str_contains($date, $month)) {
                // Already Thai formatted, just return it (optionally convert to short)
                if ($short) {
                    return str_replace($month, $thaiMonthsShort[$index], $date);
                }
                return $date;
            }
        }
        
        // Also check for short Thai months
        foreach ($thaiMonthsShort as $month) {
            if (is_string($date) && str_contains($date, $month)) {
                return $date;
            }
        }
        
        // Normal Carbon parsing for standard date formats
        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            return $date; // Return as-is if cannot parse
        }
        
        $thaiMonthsFull = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $thaiMonthsAbbr = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        
        // Use short month if time is requested or explicitly requested
        $useShort = $time || $short;
        $month = $useShort ? $thaiMonthsAbbr[$carbon->month] : $thaiMonthsFull[$carbon->month];
        $timeStr = $time ? ' ' . $carbon->format('H:i') : '';
        
        return $carbon->day . ' ' . $month . ' ' . ($carbon->year + 543) . $timeStr;
    }

    public static function formatDateTime($date)
    {
        return self::format($date, true);
    }
}
