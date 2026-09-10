<?php

use App\Models\SystemSetting;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return SystemSetting::get($key, $default);
    }
}

if (!function_exists('validate_thai_id')) {
    /**
     * Checksum validation for Thai National ID (13 digits) using Modulo 11
     */
    function validate_thai_id(?string $id): bool
    {
        if (empty($id)) {
            return false;
        }

        // Strip non-digits
        $id = preg_replace('/[^0-9]/', '', $id);

        if (strlen($id) !== 13) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$id[$i] * (13 - $i);
        }

        $checkDigit = (11 - ($sum % 11)) % 10;

        return $checkDigit === (int)$id[12];
    }
}

if (!function_exists('format_thai_id')) {
    /**
     * Format Thai Citizen ID into X-XXXX-XXXXX-XX-X
     */
    function format_thai_id(?string $id): string
    {
        if (empty($id)) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $id);
        if (strlen($digits) !== 13) {
            return $id;
        }

        return substr($digits, 0, 1) . '-' .
               substr($digits, 1, 4) . '-' .
               substr($digits, 5, 5) . '-' .
               substr($digits, 10, 2) . '-' .
               substr($digits, 12, 1);
    }
}

if (!function_exists('thai_date')) {
    /**
     * Format a date into Thai Buddhist Era string
     * Formats:
     * - 'short': 04/09/2569
     * - 'short_time': 04/09/2569 16:20 น.
     * - 'medium': 4 ก.ย. 2569
     * - 'full': 4 กันยายน 2569 เวลา 16:20 น.
     */
    function thai_date(mixed $date, string $format = 'medium'): string
    {
        if (empty($date)) {
            return '-';
        }

        if (!$date instanceof \Carbon\Carbon) {
            try {
                $date = \Carbon\Carbon::parse($date);
            } catch (\Exception $e) {
                return (string) $date;
            }
        }

        $thaiMonthsShort = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $thaiMonthsFull = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        $day = $date->format('j');
        $month = (int) $date->format('n');
        $year = (int) $date->format('Y') + 543;
        $time = $date->format('H:i');

        return match ($format) {
            'short' => $date->format('d/m/') . $year,
            'short_time' => $date->format('d/m/') . $year . " {$time} น.",
            'full' => "{$day} {$thaiMonthsFull[$month]} {$year} เวลา {$time} น.",
            default => "{$day} {$thaiMonthsShort[$month]} {$year}",
        };
    }
}

if (!function_exists('app_version')) {
    /**
     * Get the current application version string
     */
    function app_version(): string
    {
        return (string) setting('app_version', config('version.version', '2.2.2'));
    }
}

if (!function_exists('app_version_info')) {
    /**
     * Get detailed version metadata and changelog
     */
    function app_version_info(): array
    {
        return [
            'version' => app_version(),
            'release_name' => config('version.release_name', 'Thung Hua Chang IT Service Platform'),
            'release_date' => config('version.release_date', '2026-09-10'),
            'build' => config('version.build', '20260910.1'),
            'environment' => config('app.env', 'production'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'changelog' => config('version.changelog', []),
        ];
    }
}


