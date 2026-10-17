<?php

namespace App\Traits;

trait ExportsCsvSafely
{
    /**
     * Sanitize a single CSV cell against formula injection (CWE-1236 / OWASP).
     *
     * @param mixed $value
     * @return string|int|float
     */
    protected function sanitizeCsvCell(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $str = (string) $value;
        $trimmed = ltrim($str);

        // Safe numeric strings (e.g. "-100", "+5") should stay numeric unless they contain formula syntax
        if (is_numeric($trimmed)) {
            return $trimmed;
        }

        // Formula injection trigger characters
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Sanitize an entire CSV row.
     *
     * @param array $row
     * @return array
     */
    protected function sanitizeCsvRow(array $row): array
    {
        return array_map([$this, 'sanitizeCsvCell'], $row);
    }

    /**
     * Standard CSV response headers for UTF-8 streaming download.
     *
     * @param string $filename
     * @return array
     */
    protected function csvHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }
}
