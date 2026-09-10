<?php

namespace App\Console\Commands;

use App\Services\SystemUpdateService;
use Illuminate\Console\Command;
use Throwable;

class SystemUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:update
                            {--check : ตรวจสอบรายการอัปเดตล่าสุดจาก Git โดยไม่ติดตั้ง}
                            {--init-git : ติดตั้งและเชื่อมต่อ Git Repository เข้ากับ GitHub Deploy Repository อัตโนมัติ}
                            {--force : บังคับรันอัปเดตทันทีโดยไม่ต้องถามยืนยัน}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ดำเนินการอัปเดตระบบแบบอัตโนมัติ (Git pull, Auto DB backup, Migration & Cache optimize)';

    /**
     * Execute the console command.
     */
    public function handle(SystemUpdateService $updateService): int
    {
        $this->info('====================================================');
        $this->info('  Thung Hua Chang Hospital - System Auto-Updater   ');
        $this->info('====================================================');

        if ($this->option('init-git')) {
            $this->info('กำลังเริ่มต้นสร้างและเชื่อมต่อ Git Repository กับ GitHub...');
            try {
                $res = $updateService->initializeGitRepository();
                $this->info('✅ ' . $res['message']);
                if (!empty($res['log'])) {
                    $this->line($res['log']);
                }
                return 0;
            } catch (Throwable $e) {
                $this->error('❌ ล้มเหลว: ' . $e->getMessage());
                return 1;
            }
        }

        $isCheckOnly = $this->option('check');
        $isForce = $this->option('force');

        $this->line('กำลังตรวจสอบสถานะ Git Repository...');
        $check = $updateService->checkRemoteUpdates();

        if (!$check['is_git_repo']) {
            $this->error('ข้อผิดพลาด: ' . ($check['message'] ?? 'โปรเจกต์นี้ไม่ได้อยู่ใน Git repository'));
            $this->comment('คำแนะนำ: รันคำสั่ง [php artisan system:update --init-git] เพื่อสร้างและเชื่อมต่อ Git Repository อัตโนมัติ');
            return 1;
        }

        $this->table(
            ['รายการ', 'รายละเอียด'],
            [
                ['เวอร์ชันปัจจุบัน', $check['current_version'] ?? '-'],
                ['Remote Repository ปลายทาง', $check['target_repo'] ?? config('version.update_repo_url', 'https://github.com/monojung/it-system-deploy.git')],
                ['Branch ปลายทาง / Remote', ($check['target_branch'] ?? 'main') . ' (' . ($check['remote_name'] ?? 'deploy') . ')'],
                ['Branch ปัจจุบันในเครื่อง', $check['branch'] ?? '-'],
                ['Commit ในเครื่อง (Local)', $check['short_local_commit'] ?? '-'],
                ['Commit ล่าสุด (Deploy Remote)', $check['short_remote_commit'] ?? '-'],
                ['จำนวน Commit ที่ตามหลัง', $check['commits_behind'] ?? 0],
                ['สถานะการอัปเดต', ($check['has_update'] ?? false) ? '🟡 มีเวอร์ชันใหม่พร้อมอัปเดต' : '🟢 ระบบเป็นเวอร์ชันล่าสุดแล้ว'],
            ]
        );

        if (!empty($check['commits'])) {
            $this->newLine();
            $this->info('รายการ Commit ใหม่ที่รอการติดตั้ง:');
            $commitRows = array_map(function ($c) {
                return [$c['hash'], $c['date'], $c['author'], $c['message']];
            }, $check['commits']);
            $this->table(['Commit', 'วันที่', 'ผู้พัฒนา', 'รายละเอียด'], $commitRows);
        }

        if ($isCheckOnly) {
            return 0;
        }

        if (!($check['has_update'] ?? false) && !$isForce) {
            if (!$this->confirm('ระบบเป็นเวอร์ชันล่าสุดแล้ว คุณต้องการบังคับรันกระบวนการอัปเดตซ้ำหรือไม่?', false)) {
                $this->info('ยกเลิกการทำงาน');
                return 0;
            }
        } elseif (!$isForce) {
            if (!$this->confirm('ยืนยันการเริ่มกระบวนการอัปเดตระบบทันทีหรือไม่? (ระบบจะเข้าสู่โหมดปรับปรุงชั่วคราว)', true)) {
                $this->info('ยกเลิกการทำงาน');
                return 0;
            }
        }

        $this->newLine();
        $this->info('เริ่มกระบวนการอัปเดตระบบ...');

        try {
            $record = $updateService->executeUpdate(
                userId: null,
                logCallback: function (int $step, int $totalSteps, string $title, string $details) {
                    $this->line(" [{$step}/{$totalSteps}] {$title} " . ($details ? "<fg=gray>({$details})</>" : ''));
                }
            );

            $this->newLine();
            $this->info('====================================================');
            $this->info('   การอัปเดตระบบเสร็จสิ้นเรียบร้อยแล้ว (Success)    ');
            $this->info('====================================================');
            $this->line("เวอร์ชันใหม่: <info>{$record->version}</info>");
            $this->line("Commit ล่าสุด: <info>{$record->short_commit}</info>");
            $this->line("ไฟล์สำรองฐานข้อมูล: <info>{$record->backup_file}</info>");
            $this->line("ระยะเวลาดำเนินการ: <info>{$record->formatted_duration}</info>");
            $this->newLine();

            return 0;

        } catch (Throwable $e) {
            $this->newLine();
            $this->error('====================================================');
            $this->error('       การอัปเดตระบบล้มเหลว (Update Failed)         ');
            $this->error('====================================================');
            $this->error($e->getMessage());
            $this->comment('หมายเหตุ: ระบบได้พยายามเปิดโหมดออนไลน์ (php artisan up) กลับคืนให้เรียบร้อยแล้ว');
            $this->newLine();

            return 1;
        }
    }
}
