<?php

namespace App\Services;

use App\Models\DeviceType;
use App\Models\Department;
use App\Models\Asset;
use Illuminate\Support\Str;

/**
 * Service สำหรับออกแบบและสร้างรหัสคุมครุภัณฑ์ไอที (IT Code / IT Asset Tag)
 * ดีไซน์ใหม่: ไม่ใช้สูตรปีงบ/ไม่ผูกเลขพัสดุ/ไม่ผูกแผนก
 * โครงสร้าง: [รหัสย่อประเภท]-[สุ่ม 6 หลัก อักษรพิมพ์ใหญ่และตัวเลข]
 * เช่น PC-K9M2X4, NB-7A8B9C, PRN-3F2W9K, AIO-8H2K6W
 * รองรับการสร้าง Barcode (Code 128) และ QR Code แบบสากล
 */
class ItAssetCodeGeneratorService
{
    /**
     * ชุดอักขระสำหรับสุ่มรหัส 6 หลัก (ตัวอักษรภาษาอังกฤษพิมพ์ใหญ่ และ ตัวเลข)
     * คัดกรองตัวอักษรที่อาจสับสนออก เช่น O กับ 0, I กับ 1 เพื่อความชัดเจนเมื่ออ่านด้วยตาเปล่า
     * แต่ยังคงรองรับการสแกนผ่าน Barcode 1D และ QR Code ได้อย่างแม่นยำ 100%
     */
    public const CHARSET_LETTERS = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    public const CHARSET_DIGITS  = '23456789';

    /**
     * ข้อมูลคำอธิบายระบบรหัสใหม่
     */
    public static function getPresets(): array
    {
        return [
            'random_alphanumeric' => [
                'id' => 'random_alphanumeric',
                'name' => 'รูปแบบสุ่มอิสระ: [รหัสประเภท]-[สุ่ม 6 หลัก พิมพ์ใหญ่+ตัวเลข] (พร้อม Barcode)',
                'pattern' => '[TYPE]-[RANDOM6]',
                'sample' => 'PC-K9M2X4',
                'desc' => 'รหัสย่อประเภทอุปกรณ์ (เช่น PC, NB, PRN) ตามด้วยรหัสสุ่ม 6 หลักที่ไม่ซ้ำกัน สแกนได้ทันทีด้วยเครื่องอ่าน Barcode 1D และ QR Code',
                'badge' => 'ระบบใหม่ มาตรฐานสากล',
            ],
        ];
    }

    /**
     * สุ่มรหัส 6 หลัก ประกอบด้วยตัวอักษรพิมพ์ใหญ่และตัวเลข
     * รับประกันว่ามีทั้งตัวอักษรและตัวเลขผสมกันเสมอ
     */
    public static function generateRandomToken(int $length = 6): string
    {
        $letters = self::CHARSET_LETTERS;
        $digits = self::CHARSET_DIGITS;
        $all = $letters . $digits;
        $allLen = strlen($all);

        do {
            $token = '';
            $hasLetter = false;
            $hasDigit = false;

            for ($i = 0; $i < $length; $i++) {
                $char = $all[random_int(0, $allLen - 1)];
                $token .= $char;

                if (str_contains($letters, $char)) {
                    $hasLetter = true;
                }
                if (str_contains($digits, $char)) {
                    $hasDigit = true;
                }
            }
        } while (!$hasLetter || !$hasDigit);

        return $token;
    }

    /**
     * สร้างรหัสคุมครุภัณฑ์ไอที (IT Code) ตามโครงสร้างใหม่:
     * [รหัสย่อประเภท]-[สุ่ม 6 หลัก พิมพ์ใหญ่และตัวเลข]
     * พร้อมตรวจสอบความซ้ำซ้อนในฐานข้อมูล it_assets ให้ได้รหัสที่ไม่ซ้ำ 100%
     *
     * @param DeviceType|string|null $deviceTypeOrCode วัตถุประเภทอุปกรณ์ หรือรหัสย่อ
     * @param int|null $excludeAssetId รหัส asset ที่ต้องการยกเว้นการตรวจสอบ (กรณีแก้ไข)
     * @param string|null $assetName ชื่ออุปกรณ์สำหรับช่วยวิเคราะห์กรณีไม่มีประเภท
     * @param string|null $assetCode รหัสพัสดุสำหรับช่วยวิเคราะห์
     * @return string เช่น 'PC-K9M2X4'
     */
    public static function generateCode(
        $deviceTypeOrCode = null,
        ?int $excludeAssetId = null,
        ?string $assetName = null,
        ?string $assetCode = null
    ): string {
        $typeCode = self::resolveTypeCodeString($deviceTypeOrCode, $assetName, $assetCode);

        // วนลูปสุ่มจนกว่าจะได้รหัสที่ไม่ซ้ำกับในระบบ
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $attempts++;
            $token = self::generateRandomToken(6);
            $candidateCode = "{$typeCode}-{$token}";

            // ตรวจสอบความซ้ำซ้อนในฐานข้อมูล it_assets
            $query = Asset::where(function ($q) use ($candidateCode, $typeCode, $token) {
                $q->where('it_code', $candidateCode)
                  ->orWhere('it_code', "{$typeCode}{$token}");
            });

            if ($excludeAssetId) {
                $query->where('id', '!=', $excludeAssetId);
            }

            $exists = $query->exists();
        } while ($exists && $attempts < $maxAttempts);

        // กรณีฉุกเฉินหากสุ่มซ้ำเกิน 100 ครั้ง ให้ต่อท้ายด้วย microtime
        if ($exists) {
            $candidateCode = "{$typeCode}-" . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 6));
        }

        return $candidateCode;
    }

    /**
     * ดึงรหัสย่อของประเภทอุปกรณ์ (Type Code)
     */
    public static function resolveTypeCodeString($deviceTypeOrCode = null, ?string $assetName = null, ?string $assetCode = null): string
    {
        if ($deviceTypeOrCode instanceof DeviceType) {
            if (!empty($deviceTypeOrCode->code)) {
                return strtoupper(trim($deviceTypeOrCode->code));
            }
            return self::resolveDeviceTypeCode($deviceTypeOrCode, $assetName, $assetCode);
        }

        if (is_numeric($deviceTypeOrCode)) {
            $devType = DeviceType::find($deviceTypeOrCode);
            if ($devType) {
                return self::resolveTypeCodeString($devType, $assetName, $assetCode);
            }
        }

        if (is_string($deviceTypeOrCode) && !empty(trim($deviceTypeOrCode))) {
            $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim($deviceTypeOrCode)));
            if (!empty($clean)) {
                return $clean;
            }
        }

        return self::resolveDeviceTypeCode(null, $assetName, $assetCode);
    }

    /**
     * ดึงรหัสย่อของประเภทอุปกรณ์ โดยวิเคราะห์จากชื่ออุปกรณ์และรหัสพัสดุ
     */
    public static function resolveDeviceTypeCode(?DeviceType $deviceType, ?string $assetName = null, ?string $assetCode = null): string
    {
        if ($deviceType && !empty($deviceType->code)) {
            return strtoupper(trim($deviceType->code));
        }

        $name = mb_strtolower($assetName ?? $deviceType?->name ?? '');
        $code = strtoupper(trim($assetCode ?? ''));

        // ตรวจสอบชื่ออุปกรณ์และหมวดเลขพัสดุ
        if (str_contains($name, 'all in one') || str_contains($name, 'all-in-one') || str_contains($name, 'aio')) {
            return 'AIO';
        }
        if (str_contains($name, 'โน๊ตบุ๊ค') || str_contains($name, 'โน้ตบุ๊ก') || str_contains($name, 'laptop') || str_contains($name, 'nb') || str_contains($name, 'note book')) {
            return 'NB';
        }
        if (str_contains($name, 'สติกเกอร์') || str_contains($name, 'ความร้อน') || str_contains($name, 'thermal')) {
            return 'THM';
        }
        if (str_contains($name, 'หัวเข็ม') || str_contains($name, 'dot matrix')) {
            return 'DOT';
        }
        if (str_contains($name, 'เครื่องพิมพ์') || str_contains($name, 'printer') || str_contains($name, 'laser jet') || str_contains($name, 'pixma')) {
            return 'PRN';
        }
        if (str_contains($name, 'สำรองไฟ') || str_contains($name, 'ups') || str_starts_with($code, '7490-016')) {
            return 'UPS';
        }
        if (str_contains($name, 'access point') || (str_contains($name, 'กระจายสัญญาณ') && str_contains($code, '7440-013'))) {
            return 'AP';
        }
        if (str_contains($name, 'กระจายสัญญาณ') || str_contains($name, 'switch') || str_contains($name, 'router') || str_contains($code, '7440-016')) {
            return 'NET';
        }
        if (str_contains($name, 'สแกนเนอร์') || str_contains($name, 'scanner') || str_contains($code, '7440-014')) {
            return 'SCN';
        }
        if (str_contains($name, 'เซิร์ฟ') || str_contains($name, 'server') || str_contains($name, 'แม่ข่าย') || str_contains($code, '7440-011')) {
            return 'SRV';
        }
        if (str_contains($code, '7440-013') || str_starts_with($name, 'จอ') || str_contains($name, 'จอ lcd') || str_contains($name, 'จอ led') || str_contains($name, 'monitor')) {
            if (str_contains($code, '7440-001') || str_contains($name, 'เครื่องคอมพิวเตอร์') || str_contains($name, 'ไมโครคอมพิวเตอร์')) {
                return 'PC';
            }
            return 'MON';
        }

        if (str_contains($name, 'ตั้งโต๊ะ') || str_contains($name, 'pc') || str_contains($name, 'คอมพิวเตอร์')) {
            return 'PC';
        }

        return 'DEV';
    }

    /**
     * Wrapper สำหรับ backward compatibility กับส่วนต่างๆ ของระบบ
     */
    public static function generateAutoBoundCode(
        $assetOrCode = null,
        ?string $prefixOrDept = null,
        ?string $typeCode = null,
        string $ownershipType = 'owned',
        ?string $budgetYear = null,
        $id = null
    ): string {
        $excludeId = is_numeric($id) ? (int)$id : null;
        $assetName = null;
        $assetCode = null;
        $deviceType = null;

        if ($assetOrCode instanceof Asset) {
            $excludeId = $assetOrCode->id;
            $assetName = $assetOrCode->name;
            $assetCode = $assetOrCode->asset_code;
            $deviceType = $assetOrCode->deviceType;
            if (!$typeCode) {
                $typeCode = self::resolveDeviceTypeCode($deviceType, $assetName, $assetCode);
            }
        } elseif (is_string($assetOrCode)) {
            $assetCode = $assetOrCode;
        }

        if (empty($typeCode) && $deviceType) {
            $typeCode = self::resolveDeviceTypeCode($deviceType);
        }

        return self::generateCode($typeCode ?: 'PC', $excludeId, $assetName, $assetCode);
    }

    public static function generateNextAutoCode(
        $assetOrCode = null,
        ?string $prefixOrDept = null,
        ?string $typeCode = null,
        string $ownershipType = 'owned',
        ?string $budgetYear = null,
        ?int $excludeId = null
    ): string {
        return self::generateAutoBoundCode($assetOrCode, $prefixOrDept, $typeCode, $ownershipType, $budgetYear, $excludeId);
    }

    /**
     * สร้าง Barcode SVG สำหรับรหัสคุมครุภัณฑ์
     */
    public static function getBarcodeSvg(string $code, int $height = 55, bool $showText = true): string
    {
        return BarcodeService::generateSvg($code, $height, 2, $showText);
    }

    /**
     * สร้าง Barcode Data URI สำหรับรหัสคุมครุภัณฑ์
     */
    public static function getBarcodeDataUri(string $code, int $height = 55, bool $showText = true): string
    {
        return BarcodeService::generateDataUri($code, $height, 2, $showText);
    }

    /**
     * ดึงรหัสย่อ (Code) ของแผนก (Legacy helper)
     */
    public static function resolveDepartmentCode(?Department $department): string
    {
        if (!$department) {
            return '';
        }

        if (!empty($department->code)) {
            return strtoupper(trim($department->code));
        }

        $name = mb_strtolower((string)($department->name ?? ''));
        return match (true) {
            str_contains($name, 'สารสนเทศ') || str_contains($name, 'คอมพิวเตอร์') || str_contains($name, 'it') => 'IT',
            str_contains($name, 'ฉุกเฉิน') || str_contains($name, 'ems') || str_contains($name, 'er') => 'ER',
            str_contains($name, 'ผู้ป่วยนอก') || str_contains($name, 'opd') => 'OPD',
            str_contains($name, 'ผู้ป่วยใน') || str_contains($name, 'ipd') => 'IPD',
            str_contains($name, 'เภสัช') || str_contains($name, 'ยา') => 'PHAR',
            str_contains($name, 'ชันสูตร') || str_contains($name, 'แล็บ') || str_contains($name, 'lab') => 'LAB',
            str_contains($name, 'รังสี') || str_contains($name, 'x-ray') || str_contains($name, 'xray') => 'XRAY',
            str_contains($name, 'ทันต') || str_contains($name, 'dent') => 'DENT',
            str_contains($name, 'กายภาพ') || str_contains($name, 'pt') => 'PT',
            str_contains($name, 'บริหาร') || str_contains($name, 'สารบรรณ') || str_contains($name, 'ธุรการ') => 'ADM',
            str_contains($name, 'พัสดุ') || str_contains($name, 'บำรุง') => 'SUP',
            str_contains($name, 'การเงิน') || str_contains($name, 'บัญชี') || str_contains($name, 'fin') => 'FIN',
            str_contains($name, 'ประกัน') || str_contains($name, 'เวชระเบียน') => 'INS',
            str_contains($name, 'คลอด') || str_contains($name, 'lr') || str_contains($name, 'ผ่าตัด') => 'LR',
            default => '',
        };
    }

    /**
     * แกะวิเคราะห์เลขพัสดุราชการ (Legacy helper)
     */
    public static function parseOfficialAssetCode(?string $code): array
    {
        if (empty($code)) {
            return ['has_code' => false, 'seq' => null, 'year' => null, 'clean_code' => ''];
        }

        $c = strtoupper(trim($code));
        return ['has_code' => true, 'seq' => null, 'year' => null, 'clean_code' => $c];
    }
}
