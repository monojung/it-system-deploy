<?php

namespace App\Services;

use App\Models\DeviceType;
use App\Models\Department;
use App\Models\Asset;
use Carbon\Carbon;

/**
 * Service สำหรับออกแบบและเชื่อมระบบรหัสคุมครุภัณฑ์ไอที (IT Asset Tag / IT Code)
 * เข้ากับระบบคลังคอมพิวเตอร์และครุภัณฑ์ IT
 * - ใช้รหัสย่อ (Code) ของแผนกในโรงพยาบาลเป็น Prefix (เช่น ER, OPD, LAB, IT, PHAR)
 * - ใช้ Prefix RENT สำหรับเครื่องเช่า และ DON สำหรับรับบริจาค โดยไม่มีเลขพัสดุ
 */
class ItAssetCodeGeneratorService
{
    /**
     * ค่าตั้งต้นของหน่วยงาน
     */
    public const DEFAULT_PREFIX = 'THC';

    /**
     * รายการ Preset รูปแบบรหัสที่เตรียมไว้สำหรับคลังและจำลอง
     */
    public static function getPresets(): array
    {
        return [
            'dept_standard' => [
                'id' => 'dept_standard',
                'name' => 'รูปแบบมาตรฐาน: [แผนก][ประเภท][ปีงบประมาณ][ตัวเลขลำดับอัตโนมัติ] (ไม่มีขีดคั่น)',
                'pattern' => '[DEPT][TYPE][YY][SEQ]',
                'sample' => 'PHARPC670001',
                'desc' => 'ประกอบด้วย รหัสย่อแผนก (PHAR/IT/LAB/THC) + รหัสประเภท (PC/NB/AIO) + ปีงบประมาณ 2 หลัก (67) + ลำดับอัตโนมัติ 4 หลัก (0001) ไม่มีขีดคั่น',
                'badge' => 'แนะนำ สูตรใหม่คลัง IT',
                'prefix' => 'THC',
                'year_format' => 'short', // 67
                'seq_digits' => 4,
                'separator' => '',
            ],
            'rental_fleet' => [
                'id' => 'rental_fleet',
                'name' => 'รูปแบบเครื่องเช่า: RENT[TYPE][YY][SEQ] (ไม่มีขีดคั่น)',
                'pattern' => 'RENT[TYPE][YY][SEQ]',
                'sample' => 'RENTPRN670001',
                'desc' => 'Prefix RENT สำหรับอุปกรณ์สัญญาเช่า คุมด้วยรหัสไอทีต่อเนื่องโดยไม่มีเลขพัสดุ',
                'badge' => 'เครื่องเช่า (ไม่มีเลขพัสดุ)',
                'prefix' => 'RENT',
                'year_format' => 'short',
                'seq_digits' => 4,
                'separator' => '',
            ],
            'donation_fleet' => [
                'id' => 'donation_fleet',
                'name' => 'รูปแบบรับบริจาค: DON[TYPE][YY][SEQ] (ไม่มีขีดคั่น)',
                'pattern' => 'DON[TYPE][YY][SEQ]',
                'sample' => 'DONNB670001',
                'desc' => 'Prefix DON สำหรับอุปกรณ์ที่ได้รับบริจาค คุมด้วยรหัสไอทีต่อเนื่องโดยไม่มีเลขพัสดุ',
                'badge' => 'รับบริจาค (ไม่มีเลขพัสดุ)',
                'prefix' => 'DON',
                'year_format' => 'short',
                'seq_digits' => 4,
                'separator' => '',
            ],
            'type_running' => [
                'id' => 'type_running',
                'name' => 'รูปแบบลำดับต่อเนื่องตามประเภทอุปกรณ์ (ไม่มีขีดคั่น)',
                'pattern' => '[DEPT][TYPE][SEQ]',
                'sample' => 'ITPC0142',
                'desc' => 'รหัสย่อแผนก + รหัสประเภท + ลำดับ 4 หลักต่อเนื่องตลอดชีพ ไม่ผูกกับปีงบประมาณ',
                'badge' => 'รันต่อเนื่องตลอดชีพ',
                'prefix' => 'IT',
                'year_format' => 'none', // ไม่มีปี
                'seq_digits' => 4,       // 0142
                'separator' => '',
            ],
        ];
    }

    /**
     * ดึงรหัสย่อ (Code) ของแผนก หากในตารางยังไม่มี ให้แปลงจากชื่อแผนกตามมาตรฐาน รพ.
     */
    public static function resolveDepartmentCode(?Department $department): string
    {
        if (!$department) {
            return self::DEFAULT_PREFIX;
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
            default => self::DEFAULT_PREFIX,
        };
    }

    /**
     * คำนวณปีงบประมาณราชการไทย (เดือน ต.ค. เป็นต้นไป คือปีงบประมาณถัดไป)
     *
     * @param Carbon|string|null $date
     * @param string $format 'short' (67) หรือ 'full' (2567) หรือ 'ad' (2024)
     * @return string
     */
    public static function calculateFiscalYear($date = null, string $format = 'short'): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::now('Asia/Bangkok');
        $beYear = ($d->month >= 10) ? $d->year + 544 : $d->year + 543;

        return match ($format) {
            'short' => substr((string)$beYear, -2),
            'full' => (string)$beYear,
            'ad' => (string)($d->month >= 10 ? $d->year + 1 : $d->year),
            default => substr((string)$beYear, -2),
        };
    }

    /**
     * ประกอบรหัสคุมครุภัณฑ์ไอทีตามสูตรใหม่: [แผนก] [ประเภท] [ปีงบประมาณ] [ตัวเลขลำดับอัตโนมัติ] (ไม่มีขีดคั่น)
     */
    public static function buildCode(
        string $prefix,
        string $deviceTypeCode,
        ?string $yearStr,
        string|int $sequence,
        int $seqDigits = 4,
        string $separator = ''
    ): string {
        $prefix = strtoupper(str_replace(['-', ' '], '', trim($prefix)));
        $type = strtoupper(str_replace(['-', ' '], '', trim($deviceTypeCode)));
        $year = trim((string)$yearStr);
        
        // หากเป็นตัวเลขล้วนและสั้นกว่า seqDigits ให้ pad
        if (is_numeric($sequence)) {
            $seq = str_pad((string)$sequence, $seqDigits, '0', STR_PAD_LEFT);
        } else {
            $seq = str_replace(['-', ' '], '', (string)$sequence);
        }

        $parts = [];
        if (!empty($prefix)) {
            $parts[] = $prefix;
        }
        if (!empty($type)) {
            $parts[] = $type;
        }
        if (!empty($year)) {
            $parts[] = $year;
        }
        if (!empty($seq)) {
            $parts[] = $seq;
        }

        return implode($separator, $parts);
    }

    /**
     * ดึงรหัสย่อของประเภทอุปกรณ์ โดยวิเคราะห์จากชื่ออุปกรณ์และรหัสพัสดุ
     */
    public static function resolveDeviceTypeCode(?DeviceType $deviceType, ?string $assetName = null, ?string $assetCode = null): string
    {
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

        if ($deviceType && !empty($deviceType->code)) {
            return strtoupper(trim($deviceType->code));
        }

        if (str_contains($name, 'ตั้งโต๊ะ') || str_contains($name, 'pc') || str_contains($name, 'คอมพิวเตอร์')) {
            return 'PC';
        }

        return 'DEV';
    }

    /**
     * แกะวิเคราะห์เลขพัสดุราชการ (Asset Code) เพื่อดึง [ลำดับจากเลขพัสดุ] และ [ปี]
     * รองรับทุกรูปแบบตามระเบียบพัสดุราชการไทย:
     * - "7440-001-0010/67" -> seq: "0010", year: "67"
     * - "7440-001-0008/1" -> seq: "0008/1", year: null (เครื่องที่ 1 ในชุด 0008)
     * - "7440-001/0007/17" -> seq: "0007/17", year: null
     * - "7490-016-0001/100" -> seq: "0001/100", year: null
     * - "7440-001-0005" -> seq: "0005", year: null
     */
    public static function parseOfficialAssetCode(?string $code): array
    {
        if (empty($code)) {
            return ['has_code' => false, 'seq' => null, 'year' => null, 'clean_code' => ''];
        }

        $c = strtoupper(trim($code));
        // Normalize เช่น 7440-001/0007/17 -> 7440-001-0007/17
        $c = preg_replace('/(\d{4})-(\d{3})\/(\d{4})\/(\d+)/', '$1-$2-$3/$4', $c);

        // คัดกรองรหัสจำลอง/เครื่องเช่า/บริจาค/รอเลข
        if (str_starts_with($c, 'RENT') || str_starts_with($c, 'DON') ||
            str_starts_with($c, 'PENDING') || str_starts_with($c, 'TEMP') ||
            str_starts_with($c, 'NO-ASSET') || str_starts_with($c, 'NOASSET')) {
            return ['has_code' => false, 'seq' => null, 'year' => null, 'clean_code' => $c];
        }

        // รูปแบบที่ 1: รหัสพัสดุมาตรฐาน 4 ส่วน เช่น 7440-001-0010/67 หรือ 7440-001-0008/1
        if (preg_match('/^(\d{4})-(\d{3})-(\d+)\/(\d+)$/', $c, $m)) {
            $part3 = $m[3]; // ลำดับพัสดุ/ชุดจัดซื้อ เช่น 0010, 0008, 0001, 0002
            $part4 = $m[4]; // ปีงบประมาณ หรือ ลำดับเครื่องย่อย
            $part4Int = (int)$part4;

            // ตรวจสอบว่าเป็นปีงบประมาณ พ.ศ. หรือไม่ (เช่น 60-75 หรือ 2560-2575)
            $isFiscalYear = (strlen($part4) === 2 && $part4Int >= 60 && $part4Int <= 75) ||
                            (strlen($part4) === 4 && $part4Int >= 2560 && $part4Int <= 2575);

            if ($isFiscalYear) {
                return [
                    'has_code' => true,
                    'seq' => $part3,
                    'year' => substr($part4, -2),
                    'clean_code' => $c,
                ];
            } else {
                // ตัวเลขหลัง slash คือลำดับเครื่องย่อย เช่น เครื่องที่ 1 ในชุดจัดซื้อ 0008
                return [
                    'has_code' => true,
                    'seq' => $part3 . '/' . $part4,
                    'year' => null,
                    'clean_code' => $c,
                ];
            }
        }

        // รูปแบบที่ 2: รหัสพัสดุที่มี slash ทั่วไป
        if (preg_match('/(?:^|[^\d])(\d+)\/(\d+)$/', $c, $m)) {
            $p1 = $m[1];
            $p2 = $m[2];
            $p2Int = (int)$p2;
            $isFiscalYear = (strlen($p2) === 2 && $p2Int >= 60 && $p2Int <= 75) ||
                            (strlen($p2) === 4 && $p2Int >= 2560 && $p2Int <= 2575);
            if ($isFiscalYear) {
                return [
                    'has_code' => true,
                    'seq' => $p1,
                    'year' => substr($p2, -2),
                    'clean_code' => $c,
                ];
            } else {
                return [
                    'has_code' => true,
                    'seq' => $p1 . '/' . $p2,
                    'year' => null,
                    'clean_code' => $c,
                ];
            }
        }

        // รูปแบบที่ 3: รหัสพัสดุมาตรฐาน 7440-xxx-xxxx (ไม่มี slash)
        if (preg_match('/^7440-\d+-(\d+)$/i', $c, $m)) {
            return [
                'has_code' => true,
                'seq' => $m[1],
                'year' => null,
                'clean_code' => $c,
            ];
        }

        // รูปแบบที่ 4: ลงท้ายด้วยเครื่องหมายขีดตามด้วยตัวเลข (ไม่เกิน 6 หลัก)
        if (preg_match('/-(\d{1,6})$/i', $c, $m)) {
            return [
                'has_code' => true,
                'seq' => $m[1],
                'year' => null,
                'clean_code' => $c,
            ];
        }

        return ['has_code' => true, 'seq' => null, 'year' => null, 'clean_code' => $c];
    }

    /**
     * ค้นหาและดึงลำดับถัดไป (4 หลัก) สำหรับ [แผนก][ประเภท][ปีงบประมาณ]
     * โดยตรวจสอบจากฐานข้อมูล it_assets เพื่อให้ได้เลขรันอัตโนมัติต่อเนื่องและไม่ซ้ำซ้อน
     */
    public static function getNextSequence(string $prefix, string $type, string $year, ?int $excludeAssetId = null): string
    {
        $basePrefix = strtoupper(str_replace(['-', ' '], '', trim($prefix)))
                    . strtoupper(str_replace(['-', ' '], '', trim($type)))
                    . trim($year);

        $query = Asset::where(function ($q) use ($basePrefix) {
            $q->where('it_code', 'like', "{$basePrefix}%")
              ->orWhere('it_code', 'like', "{$basePrefix}-%");
        });

        if ($excludeAssetId) {
            $query->where('id', '!=', $excludeAssetId);
        }

        $existingCodes = $query->pluck('it_code');
        $maxSeq = 0;

        foreach ($existingCodes as $code) {
            $clean = strtoupper(str_replace(['-', ' '], '', (string)$code));
            if (preg_match('/^' . preg_quote($basePrefix, '/') . '(\d+)$/', $clean, $m)) {
                $num = (int)$m[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        return str_pad((string)($maxSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * ผูกรหัสคุมครุภัณฑ์ไอที (IT Tag) ตามสูตรใหม่:
     * [แผนก] [ประเภท] [ปีงบประมาณ] [ตัวเลขลำดับที่ได้อัตโนมัติ]
     * ไม่มีขีดคั่น และไม่นำเลข Asset No มาใช้เด็ดขาด
     */
    public static function generateAutoBoundCode(
        $assetOrCode = null,
        ?string $deptCode = null,
        ?string $typeCode = null,
        string $ownershipType = 'owned',
        ?string $budgetYear = null,
        $id = null
    ): string {
        $asset = null;
        $assetName = null;
        $excludeId = null;

        if ($assetOrCode instanceof Asset) {
            $asset = $assetOrCode;
            $excludeId = $asset->id;
            $assetName = $asset->name;
            $ownershipType = $asset->ownership_type ?: 'owned';
            $deptCode = self::resolveDepartmentCode($asset->department);
            $typeCode = self::resolveDeviceTypeCode($asset->deviceType, $asset->name, $asset->asset_code);
            $assetCode = $asset->asset_code;
            $budgetYear = $asset->budget_year ?: ($asset->purchase_date ? (string)$asset->purchase_date->year : null);
        } else {
            $assetCode = $assetOrCode;
            if (is_numeric($id)) {
                $excludeId = (int)$id;
            }
        }

        // ตรวจสอบกรณีเครื่องเช่า
        $isRented = ($ownershipType === 'rented') || ($asset && $asset->is_rented) || ($assetCode && str_starts_with(strtoupper((string)$assetCode), 'RENT'));
        if ($isRented) {
            $prefix = 'RENT';
            $type = $typeCode ? strtoupper(str_replace(['-', ' '], '', $typeCode)) : 'PRN';
            $yearStr = $budgetYear ? substr((string)$budgetYear, -2) : self::calculateFiscalYear();
            $seqStr = self::getNextSequence($prefix, $type, $yearStr, $excludeId);
            return "{$prefix}{$type}{$yearStr}{$seqStr}";
        }

        // ตรวจสอบกรณีรับบริจาค
        $isDonated = ($ownershipType === 'donated') || ($asset && $asset->is_donated) || ($assetCode && str_starts_with(strtoupper((string)$assetCode), 'DON'));
        if ($isDonated) {
            $prefix = 'DON';
            $type = $typeCode ? strtoupper(str_replace(['-', ' '], '', $typeCode)) : 'NB';
            $yearStr = $budgetYear ? substr((string)$budgetYear, -2) : self::calculateFiscalYear();
            $seqStr = self::getNextSequence($prefix, $type, $yearStr, $excludeId);
            return "{$prefix}{$type}{$yearStr}{$seqStr}";
        }

        // กรณีเป็นของ รพ. / ซื้อขาด
        $prefix = !empty($deptCode) ? strtoupper(str_replace(['-', ' '], '', $deptCode)) : self::DEFAULT_PREFIX;
        $type = !empty($typeCode) ? strtoupper(str_replace(['-', ' '], '', $typeCode)) : 'PC';

        // กำหนดปีงบประมาณ [ปีงบประมาณ 2 หลัก]
        $yearStr = null;
        if (!empty($budgetYear)) {
            $yearStr = substr((string)$budgetYear, -2);
        } else {
            $parsed = self::parseOfficialAssetCode($assetCode);
            if (!empty($parsed['year'])) {
                $yearStr = $parsed['year'];
            } else {
                $yearStr = self::calculateFiscalYear();
            }
        }

        // กำหนด [ตัวเลขลำดับที่ได้อัตโนมัติ] (4 หลัก รันต่อเนื่องอัตโนมัติ)
        $seqStr = self::getNextSequence($prefix, $type, $yearStr, $excludeId);

        // ประกอบตามสูตรใหม่: [แผนก][ประเภท][ปีงบประมาณ][ตัวเลขลำดับที่ได้อัตโนมัติ] (ไม่มีขีดคั่น)
        return "{$prefix}{$type}{$yearStr}{$seqStr}";
    }

    public static function generateNextAutoCode(
        $assetOrCode = null,
        ?string $deptCode = null,
        ?string $typeCode = null,
        string $ownershipType = 'owned',
        ?string $budgetYear = null,
        ?int $excludeId = null
    ): string {
        return self::generateAutoBoundCode($assetOrCode, $deptCode, $typeCode, $ownershipType, $budgetYear, $excludeId);
    }

    /**
     * จำลองการค้นหา Running Number ถัดไปสำหรับประเภทอุปกรณ์ในปีงบประมาณที่ระบุ
     */
    public static function simulateNextSequence(
        string $deviceTypeCode,
        ?string $yearStr,
        array $existingSimulatedCodes = []
    ): int {
        $maxSeq = 0;
        $typeClean = strtoupper(str_replace(['-', ' '], '', $deviceTypeCode));
        $yearClean = $yearStr ? trim($yearStr) : "";
        $pattern = "/(?:" . preg_quote($typeClean, '/') . ")" . ($yearClean ? preg_quote($yearClean, '/') : "") . "(\d{3,5})/i";

        foreach ($existingSimulatedCodes as $code) {
            $cleanCode = str_replace(['-', ' '], '', $code);
            if (preg_match($pattern, $cleanCode, $matches)) {
                $seqVal = (int)end($matches);
                if ($seqVal > $maxSeq) {
                    $maxSeq = $seqVal;
                }
            }
        }

        return $maxSeq + 1;
    }
}

