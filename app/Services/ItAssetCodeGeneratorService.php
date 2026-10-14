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
                'name' => 'รูปแบบที่ 1: รหัสย่อแผนก รพ. (มาตรฐานคลัง - ไม่มีขีดคั่น)',
                'pattern' => '[DEPT][TYPE][YY][SEQ]',
                'sample' => 'ERPC67001',
                'desc' => 'ประกอบด้วย รหัสย่อแผนก (ER/OPD/LAB/IT/PHAR) + รหัสประเภท (PC/NB/PRN) + ปีงบประมาณ 2 หลัก (67) + ลำดับ 3-4 หลัก (001)',
                'badge' => 'แนะนำ แยกตามแผนกชัดเจน',
                'prefix' => 'ER',
                'year_format' => 'short', // 67
                'seq_digits' => 3,        // 001
                'separator' => '',
            ],
            'rental_fleet' => [
                'id' => 'rental_fleet',
                'name' => 'รูปแบบที่ 2: เครื่องเช่า (RENT - ไม่มีเลขพัสดุ)',
                'pattern' => 'RENT[TYPE][YY][SEQ]',
                'sample' => 'RENTPC67001',
                'desc' => 'Prefix RENT สำหรับอุปกรณ์สัญญาเช่า/เครื่องพิมพ์เช่ารายปี คุมด้วยรหัสไอทีโดยไม่ต้องมีเลขพัสดุ',
                'badge' => 'เครื่องเช่า (ไม่มีเลขพัสดุ)',
                'prefix' => 'RENT',
                'year_format' => 'short',
                'seq_digits' => 3,
                'separator' => '',
            ],
            'donation_fleet' => [
                'id' => 'donation_fleet',
                'name' => 'รูปแบบที่ 3: รับบริจาค (DON - ไม่มีเลขพัสดุ)',
                'pattern' => 'DON[TYPE][YY][SEQ]',
                'sample' => 'DONNB67001',
                'desc' => 'Prefix DON สำหรับอุปกรณ์ที่ได้รับบริจาค คุมประวัติและส่งซ่อมได้ทันทีโดยไม่ต้องรอเลขพัสดุ',
                'badge' => 'รับบริจาค (ไม่มีเลขพัสดุ)',
                'prefix' => 'DON',
                'year_format' => 'short',
                'seq_digits' => 3,
                'separator' => '',
            ],
            'type_running' => [
                'id' => 'type_running',
                'name' => 'รูปแบบที่ 4: ลำดับต่อเนื่องตามประเภทอุปกรณ์',
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
     * ประกอบรหัสคุมครุภัณฑ์ไอทีตามพารามิเตอร์ที่กำหนด (ไม่มีเครื่องหมายขีดคั่น)
     */
    public static function buildCode(
        string $prefix,
        string $deviceTypeCode,
        ?string $yearStr,
        int $sequence,
        int $seqDigits = 3,
        string $separator = ''
    ): string {
        $prefix = strtoupper(trim($prefix));
        $type = strtoupper(trim($deviceTypeCode));
        $seq = str_pad((string)$sequence, $seqDigits, '0', STR_PAD_LEFT);

        $parts = [];
        if (!empty($prefix)) {
            $parts[] = $prefix;
        }
        if (!empty($type)) {
            $parts[] = $type;
        }
        if (!empty($yearStr)) {
            $parts[] = $yearStr;
        }
        $parts[] = $seq;

        return implode($separator, $parts);
    }

    /**
     * ดึงรหัสย่อของประเภทอุปกรณ์ หากในตารางยังไม่มี ให้สร้างรหัสมาตรฐานชั่วคราว
     */
    public static function resolveDeviceTypeCode(?DeviceType $deviceType): string
    {
        if (!$deviceType) {
            return 'DEV';
        }

        if (!empty($deviceType->code)) {
            return strtoupper(trim($deviceType->code));
        }

        $name = mb_strtolower((string)($deviceType?->name ?? ''));
        return match (true) {
            str_contains($name, 'ตั้งโต๊ะ') || str_contains($name, 'pc') => 'PC',
            str_contains($name, 'โน้ต') || str_contains($name, 'laptop') || str_contains($name, 'nb') => 'NB',
            str_contains($name, 'all-in-one') || str_contains($name, 'aio') => 'AIO',
            str_contains($name, 'สติกเกอร์') || str_contains($name, 'ความร้อน') || str_contains($name, 'thermal') => 'THM',
            str_contains($name, 'หัวเข็ม') || str_contains($name, 'dot') => 'DOT',
            str_contains($name, 'พิมพ์') || str_contains($name, 'printer') => 'PRN',
            str_contains($name, 'สำรองไฟ') || str_contains($name, 'ups') => 'UPS',
            str_contains($name, 'เครือข่าย') || str_contains($name, 'switch') || str_contains($name, 'router') => 'NET',
            str_contains($name, 'สแกน') || str_contains($name, 'scanner') || str_contains($name, 'บัตร') => 'SCN',
            str_contains($name, 'เซิร์ฟ') || str_contains($name, 'server') => 'SRV',
            str_contains($name, 'จอ') || str_contains($name, 'monitor') => 'MON',
            default => 'DEV' . ($deviceType->id ?? '1'),
        };
    }

    /**
     * แกะวิเคราะห์เลขพัสดุราชการ (Asset Code) เพื่อดึงลำดับและปีงบประมาณ
     * เช่น:
     * - "7440-001-0005/66" -> seq: "0005", year: "66"
     * - "7440-001-0005/2566" -> seq: "0005", year: "66"
     * - "7440-001-0005" -> seq: "0005", year: null
     */
    public static function parseOfficialAssetCode(?string $code): array
    {
        if (empty($code)) {
            return ['has_code' => false, 'seq' => null, 'year' => null, 'clean_code' => ''];
        }

        $c = strtoupper(trim($code));

        // คัดกรองรหัสจำลอง/เครื่องเช่า/บริจาค/รอเลข
        if (str_starts_with($c, 'RENT') || str_starts_with($c, 'DON') ||
            str_starts_with($c, 'PENDING') || str_starts_with($c, 'TEMP') ||
            str_starts_with($c, 'NO-ASSET') || str_starts_with($c, 'NOASSET')) {
            return ['has_code' => false, 'seq' => null, 'year' => null, 'clean_code' => $c];
        }

        // รูปแบบที่ 1: มี Slash ระบุปีงบประมาณ เช่น 7440-001-0005/66 หรือ .../2566
        if (preg_match('/(?:^|[^\d])(\d+)\/(\d{2,4})$/i', $c, $m)) {
            return [
                'has_code' => true,
                'seq' => $m[1],
                'year' => substr($m[2], -2),
                'clean_code' => $c,
            ];
        }

        // รูปแบบที่ 2: รหัสพัสดุมาตรฐาน 7440-xxx-xxxx (ไม่มี slash)
        if (preg_match('/^7440-\d+-(\d+)$/i', $c, $m)) {
            return [
                'has_code' => true,
                'seq' => $m[1],
                'year' => null,
                'clean_code' => $c,
            ];
        }

        // รูปแบบที่ 3: ลงท้ายด้วยเครื่องหมายขีดตามด้วยตัวเลข (ไม่เกิน 6 หลัก เพื่อกัน timestamp จาก mock)
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
     * ผูกรหัสคุมครุภัณฑ์ไอทีเข้ากับรหัสครุภัณฑ์โดยตรง (Smart Binding - ไม่มีขีดคั่น)
     * รองรับทั้งการส่ง Object Model Asset หรือค่าพารามิเตอร์แต่ละตัว
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
        if ($assetOrCode instanceof Asset) {
            $asset = $assetOrCode;
            $ownershipType = $asset->ownership_type ?: 'owned';
            $deptCode = self::resolveDepartmentCode($asset->department);
            $typeCode = $asset->deviceType?->code ?: self::resolveDeviceTypeCode($asset->deviceType ?: new DeviceType(['name' => $asset->name]));
            $assetCode = $asset->asset_code;
            $budgetYear = $asset->budget_year ?: ($asset->purchase_date ? (string)$asset->purchase_date->year : null);
            $id = $asset->id;
        } else {
            $assetCode = $assetOrCode;
        }

        // ตรวจสอบกรณีเครื่องเช่า
        $isRented = ($ownershipType === 'rented') || ($asset && $asset->is_rented) || ($assetCode && str_starts_with(strtoupper($assetCode), 'RENT'));
        if ($isRented) {
            $type = $typeCode ?: 'DEV';
            $yearStr = $budgetYear ? substr((string)$budgetYear, -2) : self::calculateFiscalYear();
            $seqStr = $id ? str_pad((string)$id, 3, '0', STR_PAD_LEFT) : str_pad((string)((Asset::max('id') ?? 0) + 1), 3, '0', STR_PAD_LEFT);
            return "RENT{$type}{$yearStr}{$seqStr}";
        }

        // ตรวจสอบกรณีรับบริจาค
        $isDonated = ($ownershipType === 'donated') || ($asset && $asset->is_donated) || ($assetCode && str_starts_with(strtoupper($assetCode), 'DON'));
        if ($isDonated) {
            $type = $typeCode ?: 'DEV';
            $yearStr = $budgetYear ? substr((string)$budgetYear, -2) : self::calculateFiscalYear();
            $seqStr = $id ? str_pad((string)$id, 3, '0', STR_PAD_LEFT) : str_pad((string)((Asset::max('id') ?? 0) + 1), 3, '0', STR_PAD_LEFT);
            return "DON{$type}{$yearStr}{$seqStr}";
        }

        // กรณีเป็นของ รพ. / ซื้อขาด
        $prefix = !empty($deptCode) ? strtoupper(trim($deptCode)) : self::DEFAULT_PREFIX;
        $type = !empty($typeCode) ? strtoupper(trim($typeCode)) : 'DEV';

        $parsed = self::parseOfficialAssetCode($assetCode);

        // กำหนดปีงบประมาณ
        $yearStr = null;
        if (!empty($parsed['year'])) {
            $yearStr = $parsed['year'];
        } elseif (!empty($budgetYear)) {
            $yearStr = substr((string)$budgetYear, -2);
        } else {
            $yearStr = self::calculateFiscalYear();
        }

        // กำหนดเลขลำดับ (ถ้ามีจากเลขพัสดุ ให้นำมาผูกตรงกันทันที)
        if (!empty($parsed['seq'])) {
            $rawSeq = $parsed['seq'];
            $seqStr = (strlen($rawSeq) < 3) ? str_pad($rawSeq, 3, '0', STR_PAD_LEFT) : $rawSeq;
        } elseif (!empty($id)) {
            // ยังไม่มีเลขพัสดุทางการ ใช้รหัสชั่วคราวที่มี T นำหน้า เช่น ERPC67T001
            $seqStr = 'T' . str_pad((string)$id, 3, '0', STR_PAD_LEFT);
        } else {
            $nextId = (Asset::max('id') ?? 0) + 1;
            $seqStr = 'T' . str_pad((string)$nextId, 3, '0', STR_PAD_LEFT);
        }

        return "{$prefix}{$type}{$yearStr}{$seqStr}";
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
        $pattern = "/-(" . preg_quote($deviceTypeCode, '/') . ")-" . ($yearStr ? preg_quote($yearStr, '/') . "-" : "") . "(\d+)/i";

        foreach ($existingSimulatedCodes as $code) {
            if (preg_match($pattern, $code, $matches)) {
                $seqVal = (int)end($matches);
                if ($seqVal > $maxSeq) {
                    $maxSeq = $seqVal;
                }
            }
        }

        return $maxSeq + 1;
    }
}
