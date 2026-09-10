<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\TotpService;

class MfaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mfa {action=status : การดำเนินการ: status, toggle, enable, disable, reset, disable-all} {user? : Username, ID หรือ Email ของผู้ใช้} {--enforce : บังคับใช้ MFA โดยแอดมิน}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'จัดการและสั่งเปิด/ปิด Google Authenticator (MFA) สำหรับผู้ใช้งาน';

    /**
     * Execute the console command.
     */
    public function handle(TotpService $totpService)
    {
        $action = strtolower($this->argument('action'));
        $userIdentifier = $this->argument('user');

        if ($action === 'status' && empty($userIdentifier)) {
            return $this->showAllStatus();
        }

        if ($action === 'disable-all') {
            return $this->disableAllMfa();
        }

        if (empty($userIdentifier)) {
            $this->error('กรุณาระบุ Username, ID หรือ Email ของผู้ใช้งาน เช่น: php artisan mfa toggle admin');
            return 1;
        }

        $user = User::where('username', $userIdentifier)
            ->orWhere('id', $userIdentifier)
            ->orWhere('email', $userIdentifier)
            ->first();

        if (!$user) {
            $this->error("ไม่พบผู้ใช้งานในระบบสำหรับ: {$userIdentifier}");
            return 1;
        }

        return match ($action) {
            'enable' => $this->enableMfa($user, $totpService),
            'disable' => $this->disableMfa($user),
            'toggle' => $this->toggleMfa($user, $totpService),
            'reset' => $this->resetMfa($user, $totpService),
            'status' => $this->showUserStatus($user, $totpService),
            default => $this->unknownAction($action),
        };
    }

    protected function showAllStatus(): int
    {
        $users = User::select('id', 'name', 'username', 'role', 'mfa_enabled', 'mfa_enforced', 'cid', 'google_email')->get();

        $rows = $users->map(function ($u) {
            $mfaText = $u->mfa_enforced ? '🟡 บังคับใช้ (Enforced)' : ($u->mfa_enabled ? '🟢 เปิดใช้งาน (Active)' : '⚪ ปิดใช้งาน (Disabled)');
            $linked = [];
            if ($u->cid) $linked[] = 'Thai ID';
            if ($u->google_email) $linked[] = 'Google';
            $linkedText = count($linked) ? implode(' + ', $linked) : '-';

            return [
                $u->id,
                $u->name,
                $u->username,
                $u->role,
                $linkedText,
                $mfaText,
            ];
        });

        $this->table(['ID', 'ชื่อ - สกุล', 'Username', 'Role', 'Connected Accounts', 'Google Authenticator MFA'], $rows);
        $this->info("ผู้ใช้งานทั้งหมด: {$users->count()} บัญชี | เปิดใช้งาน MFA: " . $users->filter->isMfaActive()->count() . " บัญชี");

        return 0;
    }

    protected function showUserStatus(User $user, TotpService $totpService): int
    {
        $status = $user->mfa_enforced ? '🟡 บังคับใช้ (Admin Enforced)' : ($user->mfa_enabled ? '🟢 เปิดใช้งาน (Active)' : '⚪ ปิดใช้งาน (Disabled)');

        $this->info("=== ข้อมูลความปลอดภัย MFA: {$user->name} (@{$user->username}) ===");
        $this->line("สถานะ MFA: {$status}");
        $this->line("Thai ID: " . ($user->formatted_cid ?? 'ยังไม่เชื่อมต่อ'));
        $this->line("Google Email: " . ($user->google_email ?? 'ยังไม่เชื่อมต่อ'));
        $this->line("Secret Key: " . ($user->mfa_secret ?? 'ยังไม่มีคีย์'));
        if ($user->mfa_enrolled_at) {
            $this->line("เปิดใช้งานเมื่อ: {$user->mfa_enrolled_at}");
        }

        if ($user->mfa_secret) {
            $label = $user->username . ($user->email ? " ({$user->email})" : '');
            $uri = $totpService->getOtpAuthUri($label, $user->mfa_secret, 'Hospital IT');
            $this->line("OTP Auth URI: {$uri}");
            $this->line("QR Code URL: " . $totpService->getQrCodeUrl($uri));
        }

        return 0;
    }

    protected function enableMfa(User $user, TotpService $totpService): int
    {
        $enforce = $this->option('enforce');
        $secret = $user->mfa_secret ?: $totpService->generateSecret();

        $user->update([
            'mfa_enabled' => true,
            'mfa_enforced' => (bool) $enforce,
            'mfa_secret' => $secret,
            'mfa_enrolled_at' => $user->mfa_enrolled_at ?: now(),
        ]);

        AuditLog::record('cli_enable_mfa', 'users', "เปิดใช้งาน MFA ผ่านคำสั่ง CLI ให้แก่ {$user->name}", $user);

        $this->info("✓ เปิดใช้งาน Google Authenticator (MFA) สำหรับ [{$user->name}] เรียบร้อยแล้ว" . ($enforce ? " (โหมดบังคับใช้)" : ""));
        $this->line("Base32 Secret Key: {$secret}");

        return 0;
    }

    protected function disableMfa(User $user): int
    {
        $user->update([
            'mfa_enabled' => false,
            'mfa_enforced' => false,
        ]);

        AuditLog::record('cli_disable_mfa', 'users', "ปิดใช้งาน MFA ผ่านคำสั่ง CLI ให้แก่ {$user->name}", $user);

        $this->warn("✓ ปิดใช้งาน Google Authenticator (MFA) สำหรับ [{$user->name}] เรียบร้อยแล้ว");

        return 0;
    }

    protected function toggleMfa(User $user, TotpService $totpService): int
    {
        if ($user->isMfaActive()) {
            return $this->disableMfa($user);
        } else {
            return $this->enableMfa($user, $totpService);
        }
    }

    protected function resetMfa(User $user, TotpService $totpService): int
    {
        $newSecret = $totpService->generateSecret();
        $user->update([
            'mfa_secret' => $newSecret,
            'mfa_enrolled_at' => now(),
        ]);

        AuditLog::record('cli_reset_mfa', 'users', "รีเซ็ตคีย์ MFA ผ่านคำสั่ง CLI ให้แก่ {$user->name}", $user);

        $label = $user->username . ($user->email ? " ({$user->email})" : '');
        $uri = $totpService->getOtpAuthUri($label, $newSecret, 'Hospital IT');
        $qrUrl = $totpService->getQrCodeUrl($uri);

        $this->info("✓ รีเซ็ต Google Authenticator Secret Key สำหรับ [{$user->name}] เรียบร้อยแล้ว");
        $this->line("รหัสคีย์ลับใหม่ (Base32): {$newSecret}");
        $this->line("ลิงก์ QR Code: {$qrUrl}");

        return 0;
    }

    protected function disableAllMfa(): int
    {
        if (!$this->confirm('คำเตือน: คุณต้องการปิดการใช้งาน Google Authenticator สำหรับผู้ใช้ทั้งหมดในระบบใช่หรือไม่? (ใช้ในกรณีฉุกเฉิน)')) {
            $this->line('ยกเลิกคำสั่ง');
            return 0;
        }

        $count = User::where('mfa_enabled', true)->orWhere('mfa_enforced', true)->count();

        User::query()->update([
            'mfa_enabled' => false,
            'mfa_enforced' => false,
        ]);

        AuditLog::record('cli_disable_all_mfa', 'security', "ปิดใช้งาน MFA ทั้งระบบผ่านคำสั่ง CLI (รวม {$count} บัญชี)", null);

        $this->warn("✓ ปิดการใช้งาน MFA ทั้งระบบเรียบร้อยแล้ว ({$count} บัญชี)");

        return 0;
    }

    protected function unknownAction(string $action): int
    {
        $this->error("ไม่รู้จักคำสั่ง: {$action}");
        $this->line("คำสั่งที่รองรับ:");
        $this->line("  php artisan mfa toggle {user}       - สลับ เปิด/ปิด MFA");
        $this->line("  php artisan mfa enable {user}       - เปิดใช้งาน MFA");
        $this->line("  php artisan mfa disable {user}      - ปิดใช้งาน MFA");
        $this->line("  php artisan mfa reset {user}        - รีเซ็ต Secret Key ใหม่");
        $this->line("  php artisan mfa status {user?}      - ตรวจสอบสถานะ MFA");
        $this->line("  php artisan mfa disable-all         - ปิดใช้งาน MFA ทุกบัญชี (ฉุกเฉิน)");

        return 1;
    }
}
