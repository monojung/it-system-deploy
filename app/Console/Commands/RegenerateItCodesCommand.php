<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Services\ItAssetCodeGeneratorService;

class RegenerateItCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:regenerate-it-codes 
                            {--all : อัปเดตรหัสครุภัณฑ์ไอทีทั้งหมดในระบบ}
                            {--legacy-only : อัปเดตเฉพาะรหัสที่เป็นสูตรเก่า เช่น PC670001}
                            {--dry-run : จำลองการทำงานโดยยังไม่บันทึกจริง}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ปรับเปลี่ยนรหัสคุมครุภัณฑ์ไอที (IT Code) สู่ระบบใหม่: [รหัสประเภท]-[สุ่ม 6 หลัก อักษรพิมพ์ใหญ่+ตัวเลข] พร้อม Barcode';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $all = $this->option('all');
        $dryRun = $this->option('dry-run');

        $this->info("=== เริ่มต้นปรับเปลี่ยนรหัส IT Code สู่โครงสร้างใหม่ ===");
        if ($dryRun) {
            $this->warn("[โหมดจำลอง DRY-RUN: ไม่มีการบันทึกลงฐานข้อมูล]");
        }

        $query = Asset::query();
        if (!$all) {
            // กรองเฉพาะรหัสที่เป็นสูตรเดิม (ไม่มีขีด หรือมีรูปแบบตัวเลข 4 หลักต่อท้าย) หรือยังไม่มีรหัส
            $query->where(function ($q) {
                $q->whereNull('it_code')
                  ->orWhere('it_code', '')
                  ->orWhere('it_code', 'not like', '%-%')
                  ->orWhere('it_code', 'regexp', '^[A-Z]+[0-9]{6}$');
            });
        }

        $assets = $query->get();
        $count = $assets->count();

        if ($count === 0) {
            $this->info("ไม่พบรายการที่ต้องปรับเปลี่ยนรหัส IT Code");
            return 0;
        }

        $this->info("พบรายการที่ต้องประมวลผล: {$count} รายการ");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updatedCount = 0;
        $samples = [];

        foreach ($assets as $asset) {
            $oldCode = $asset->it_code;
            $typeCode = ItAssetCodeGeneratorService::resolveTypeCodeString($asset->deviceType, $asset->name, $asset->asset_code);
            $newCode = ItAssetCodeGeneratorService::generateCode($typeCode, $asset->id, $asset->name, $asset->asset_code);

            if (!$dryRun) {
                $asset->it_code = $newCode;
                // อัปเดต IT-TAG ใน notes ด้วย
                $cleanNotes = preg_replace('/\[IT-TAG:\s*[^\]]+\]\s*/', '', $asset->notes ?? '');
                $asset->notes = trim("[IT-TAG: {$newCode}]\n" . $cleanNotes);
                $asset->saveQuietly();
            }

            $updatedCount++;
            if (count($samples) < 10) {
                $samples[] = [
                    'ID' => $asset->id,
                    'ชื่อครุภัณฑ์' => mb_substr($asset->name, 0, 25),
                    'รหัสพัสดุ' => $asset->asset_code ?: '-',
                    'รหัสเดิม' => $oldCode ?: '-',
                    'รหัสใหม่' => $newCode,
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['ID', 'ชื่อครุภัณฑ์', 'รหัสพัสดุ', 'รหัสเดิม', 'รหัสใหม่'], $samples);

        $this->info("สำเร็จ! ปรับปรุงรหัส IT Code ทั้งสิ้น {$updatedCount} รายการ" . ($dryRun ? " (จำลอง)" : ""));
        return 0;
    }
}
