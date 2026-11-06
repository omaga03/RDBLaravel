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

    public static function formatShort($date)
    {
        return self::format($date, false, true);
    }

    /**
     * Convert Thai date string (Buddhist Era or CE) to ISO YYYY-MM-DD
     * Supports formats:
     * - "24 ตุลาคม 2569" or "24 ต.ค. 2569"
     * - "24/10/2569" or "24-10-2569"
     * - "2026-10-24" (ISO)
     *
     * @param mixed $date
     * @return string|null
     */
    public static function toIsoDate($date): ?string
    {
        if (!$date) return null;
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        $str = trim((string)$date);
        if ($str === '') return null;

        // If already ISO Y-m-d (handling potential Buddhist Era year as well)
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $str, $matches)) {
            $year = (int)$matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
            if ($year >= 2400) {
                $year -= 543;
            }
            return sprintf('%04d-%s-%s', $year, $month, $day);
        }

        // Match dd/mm/yyyy or dd-mm-yyyy
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $str, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = (int)$matches[3];
            if ($year >= 2400) {
                $year -= 543;
            }
            return sprintf('%04d-%s-%s', $year, $month, $day);
        }

        $thaiMonthsMap = [
            'มกราคม' => '01', 'กุมภาพันธ์' => '02', 'มีนาคม' => '03', 'เมษายน' => '04',
            'พฤษภาคม' => '05', 'มิถุนายน' => '06', 'กรกฎาคม' => '07', 'สิงหาคม' => '08',
            'กันยายน' => '09', 'ตุลาคม' => '10', 'พฤศจิกายน' => '11', 'ธันวาคม' => '12',
            'ม.ค.' => '01', 'ก.พ.' => '02', 'มี.ค.' => '03', 'เม.ย.' => '04',
            'พ.ค.' => '05', 'มิ.ย.' => '06', 'ก.ค.' => '07', 'ส.ค.' => '08',
            'ก.ย.' => '09', 'ต.ค.' => '10', 'พ.ย.' => '11', 'ธ.ค.' => '12',
        ];

        $parts = preg_split('/\s+/', $str);
        if (count($parts) >= 3) {
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $mStr = $parts[1];
            $year = (int)$parts[2];

            if (isset($thaiMonthsMap[$mStr])) {
                $month = $thaiMonthsMap[$mStr];
                if ($year >= 2400) {
                    $year -= 543;
                }
                return sprintf('%04d-%s-%s', $year, $month, $day);
            }
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Exception $e) {
            return $str;
        }
    }
}
