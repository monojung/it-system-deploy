<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DataRequestController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SystemResetController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\ServerInitController;
use App\Http\Controllers\HardwareAuditController;
use App\Http\Controllers\AssetBorrowController;

// Initial Server Setup Route (Storage link, cache clear, migrations)
Route::get('/server-init', [ServerInitController::class, 'init'])
    ->withoutMiddleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->name('server.init');

// Authentication Routes (Email, Registration, Google & ThaID)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/register', function () {
    return redirect()->route('login')->with('info', 'ระบบไม่อนุญาตให้ลงทะเบียนด้วยตนเอง กรุณาติดต่อผู้ดูแลระบบสารสนเทศ (Admin) เพื่อเปิดบัญชีผู้ใช้งาน');
})->name('register');
Route::get('/auth/verify-notice', [AuthController::class, 'showVerifyNotice'])->name('auth.verify-notice');
Route::post('/auth/resend-setup-link', [AuthController::class, 'resendSetupLink'])->name('auth.resend-setup-link');
Route::get('/auth/setup-password/{token}', [AuthController::class, 'showSetupPassword'])->name('auth.setup-password');
Route::post('/auth/setup-password/{token}', [AuthController::class, 'setupPassword'])->name('auth.setup-password.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('/auth/google/token', [AuthController::class, 'handleGoogleToken'])->name('auth.google.token');
Route::get('/auth/thaid', [AuthController::class, 'redirectToThaID'])->name('auth.thaid');
Route::get('/auth/thaid/callback', [AuthController::class, 'handleThaIDCallback'])->name('auth.thaid.callback');
Route::get('/auth/mfa-challenge', [AuthController::class, 'showMfaChallenge'])->name('auth.mfa-challenge');
Route::post('/auth/mfa-verify', [AuthController::class, 'verifyMfaChallenge'])->name('auth.mfa-verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Hardware Audit API Endpoint (for embedded client PowerShell Agent)
Route::post('/api/hardware-audit/submit', [HardwareAuditController::class, 'submit'])->name('hardware-audit.submit');
Route::post('/hardware-audit/submit', [HardwareAuditController::class, 'submit']);

// Public Client Scripts & Agents Endpoints (Serve PowerShell & Batch files with text/plain for irm / download)
Route::get('/agent/{filename}', function ($filename) {
    $filename = basename($filename);
    $candidates = [
        public_path('agent/' . $filename),
        base_path('agent/' . $filename),
        base_path('public/agent/' . $filename),
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $mime = str_ends_with($filename, '.ps1') ? 'text/plain; charset=utf-8' : (str_ends_with($filename, '.bat') ? 'application/x-bat' : 'text/plain; charset=utf-8');
            return response(file_get_contents($path), 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }
    }
    abort(404, 'Agent script not found');
})->where('filename', '[A-Za-z0-9_\-\.]+')->name('agent.download');

Route::get('/scripts/{filename}', function ($filename) {
    $filename = basename($filename);
    $candidates = [
        public_path('scripts/' . $filename),
        base_path('scripts/' . $filename),
        base_path('public/scripts/' . $filename),
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $mime = str_ends_with($filename, '.ps1') ? 'text/plain; charset=utf-8' : (str_ends_with($filename, '.bat') ? 'application/x-bat' : 'text/plain; charset=utf-8');
            return response(file_get_contents($path), 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }
    }
    abort(404, 'Script not found');
})->where('filename', '[A-Za-z0-9_\-\.]+')->name('scripts.download');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard & Profile
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('', [DashboardController::class, 'index']);
    Route::get('/it-system', [DashboardController::class, 'index']);
    Route::get('/it-system/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Profile MFA Self-Management
    Route::post('/profile/mfa/enable', [AuthController::class, 'enableMfa'])->name('profile.mfa.enable');
    Route::post('/profile/mfa/disable', [AuthController::class, 'disableMfa'])->name('profile.mfa.disable');
    Route::post('/profile/mfa/reset', [AuthController::class, 'resetMfaSecret'])->name('profile.mfa.reset');

    // Profile Account Linking (Google & ThaiD)
    Route::get('/profile/link-google', [AuthController::class, 'redirectToGoogleLink'])->name('profile.link-google');
    Route::post('/profile/unlink-google', [AuthController::class, 'unlinkGoogle'])->name('profile.unlink-google');
    Route::post('/profile/link-cid', [AuthController::class, 'linkCid'])->name('profile.link-cid');

    // Repair Tickets (Accessible by all users with varied permissions)
    Route::get('/repairs', [RepairController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [RepairController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [RepairController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{repair}', [RepairController::class, 'show'])->name('repairs.show');
    Route::get('/repairs/{repair}/edit', [RepairController::class, 'edit'])->name('repairs.edit');
    Route::put('/repairs/{repair}', [RepairController::class, 'update'])->name('repairs.update');
    Route::get('/repairs/{repair}/print', [RepairController::class, 'print'])->name('repairs.print');
    Route::post('/repairs/{repair}/rate', [RepairController::class, 'rate'])->name('repairs.rate');

    // IT Staff / Admin actions for repairs
    Route::middleware(['role:admin,technician'])->group(function () {
        Route::post('/repairs/{repair}/status', [RepairController::class, 'updateStatus'])->name('repairs.update-status');
        Route::post('/repairs/{repair}/add-part', [RepairController::class, 'addPart'])->name('repairs.add-part');
        Route::delete('/repairs/{repair}/parts/{part}', [RepairController::class, 'removePart'])->name('repairs.remove-part');
    });

    // Data Requests (ขอข้อมูลสารสนเทศทางการแพทย์ & สถิติ)
    Route::get('/data-requests/export/csv', [DataRequestController::class, 'exportCsv'])->name('data-requests.export');
    Route::get('/data-requests', [DataRequestController::class, 'index'])->name('data-requests.index');
    Route::get('/data-requests/create', [DataRequestController::class, 'create'])->name('data-requests.create');
    Route::post('/data-requests', [DataRequestController::class, 'store'])->name('data-requests.store');
    Route::get('/data-requests/{id}', [DataRequestController::class, 'show'])->name('data-requests.show');
    Route::get('/data-requests/{id}/edit', [DataRequestController::class, 'edit'])->name('data-requests.edit');
    Route::put('/data-requests/{id}', [DataRequestController::class, 'update'])->name('data-requests.update');
    Route::delete('/data-requests/{id}', [DataRequestController::class, 'destroy'])->name('data-requests.destroy');
    Route::get('/data-requests/{id}/print', [DataRequestController::class, 'print'])->name('data-requests.print');
    Route::get('/data-requests/{id}/download', [DataRequestController::class, 'downloadResult'])->name('data-requests.download');
    Route::get('/data-requests/{id}/download-sample', [DataRequestController::class, 'downloadSample'])->name('data-requests.download-sample');
    Route::middleware(['role:admin,technician'])->group(function () {
        Route::post('/data-requests/{id}/status', [DataRequestController::class, 'updateStatus'])->name('data-requests.status');
        Route::post('/data-requests/{id}/complete', [DataRequestController::class, 'complete'])->name('data-requests.complete');
        Route::post('/data-requests/{id}/execute-query', [DataRequestController::class, 'executeQuery'])->name('data-requests.execute-query');
        Route::post('/data-requests/{id}/generate-file', [DataRequestController::class, 'generateFileFromQuery'])->name('data-requests.generate-file');
    });

    // IT Assets (คลังคอมพิวเตอร์)
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');

    // IT Staff / Admin asset management
    Route::middleware(['role:admin,technician'])->group(function () {
        Route::get('/assets/create/new', [AssetController::class, 'create'])->name('assets.create');
        Route::get('/assets/create', [AssetController::class, 'create']);
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

        // Spare Parts & Stock (คลังอะไหล่)
        Route::get('/spare-parts', [SparePartController::class, 'index'])->name('spare-parts.index');
        Route::get('/spare-parts/create', [SparePartController::class, 'create'])->name('spare-parts.create');
        Route::post('/spare-parts', [SparePartController::class, 'store'])->name('spare-parts.store');
        Route::get('/spare-parts/{sparePart}/edit', [SparePartController::class, 'edit'])->name('spare-parts.edit');
        Route::put('/spare-parts/{sparePart}', [SparePartController::class, 'update'])->name('spare-parts.update');
        Route::post('/spare-parts/{sparePart}/adjust-stock', [SparePartController::class, 'adjustStock'])->name('spare-parts.adjust-stock');
        Route::delete('/spare-parts/{sparePart}', [SparePartController::class, 'destroy'])->name('spare-parts.destroy');
    });

    Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('/assets/{asset}/label', [AssetController::class, 'label'])->name('assets.label');

    // IT Equipment Borrowing (ระบบขอยืม-คืนอุปกรณ์ไอที)
    Route::get('/asset-borrows', [AssetBorrowController::class, 'index'])->name('asset-borrows.index');
    Route::get('/asset-borrows/create', [AssetBorrowController::class, 'create'])->name('asset-borrows.create');
    Route::post('/asset-borrows', [AssetBorrowController::class, 'store'])->name('asset-borrows.store');
    Route::get('/asset-borrows/{assetBorrow}', [AssetBorrowController::class, 'show'])->name('asset-borrows.show');
    Route::get('/asset-borrows/{assetBorrow}/edit', [AssetBorrowController::class, 'edit'])->name('asset-borrows.edit');
    Route::put('/asset-borrows/{assetBorrow}', [AssetBorrowController::class, 'update'])->name('asset-borrows.update');
    Route::delete('/asset-borrows/{assetBorrow}', [AssetBorrowController::class, 'destroy'])->name('asset-borrows.destroy');
    Route::get('/asset-borrows/{assetBorrow}/print', [AssetBorrowController::class, 'print'])->name('asset-borrows.print');

    // Admin & Technician only actions for asset borrows
    Route::middleware(['role:admin,technician'])->group(function () {
        Route::post('/asset-borrows/{assetBorrow}/approve', [AssetBorrowController::class, 'approve'])->name('asset-borrows.approve');
        Route::post('/asset-borrows/{assetBorrow}/reject', [AssetBorrowController::class, 'reject'])->name('asset-borrows.reject');
        Route::post('/asset-borrows/{assetBorrow}/dispatch', [AssetBorrowController::class, 'dispatch'])->name('asset-borrows.dispatch');
        Route::post('/asset-borrows/{assetBorrow}/return', [AssetBorrowController::class, 'receiveReturn'])->name('asset-borrows.return');
        Route::post('/asset-borrows/{assetBorrow}/receive-return', [AssetBorrowController::class, 'receiveReturn'])->name('asset-borrows.receive-return');
    });

    // Reports (รายงานสรุป ไตรมาส / เดือน / ครุภัณฑ์)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/assets', [ReportController::class, 'assets'])->name('reports.assets');
    Route::get('/reports/export/{type}', [ReportController::class, 'exportCsv'])->name('reports.export');

    // Admin & Technician only for overview reports & hardware audits
    Route::middleware(['role:admin,technician'])->group(function () {
        Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
        Route::get('/reports/quarterly', [ReportController::class, 'quarterly'])->name('reports.quarterly');

        // Hardware Audit & Monitor Dashboard (สำรวจสเปคคอมพิวเตอร์ประจำปีงบประมาณ)
        Route::get('/hardware-audits', [HardwareAuditController::class, 'index'])->name('hardware-audits.index');
        Route::get('/hardware-audits/{id}/inspect', [HardwareAuditController::class, 'inspect'])->name('hardware-audits.inspect');
        Route::post('/hardware-audits/{id}/approve', [HardwareAuditController::class, 'approve'])->name('hardware-audits.approve');
        Route::post('/hardware-audits/batch-approve', [HardwareAuditController::class, 'batchApprove'])->name('hardware-audits.batch-approve');
        Route::post('/hardware-audits/{id}/reject', [HardwareAuditController::class, 'reject'])->name('hardware-audits.reject');
        Route::get('/hardware-audits/print-report', [HardwareAuditController::class, 'printAnnualReport'])->name('hardware-audits.print-report');
        Route::get('/hardware-audits/export-csv', [HardwareAuditController::class, 'exportCsv'])->name('hardware-audits.export-csv');
    });

    // Admin Only: Backup & Restore, User Management, Department Management
    Route::middleware(['role:admin'])->group(function () {
        // Backup & Restore
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::post('/backups/toggle-auto', [BackupController::class, 'toggleAutoBackup'])->name('backups.toggle-auto');
        Route::post('/backups/clean-orphans', [BackupController::class, 'cleanOrphans'])->name('backups.clean-orphans');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // Assets CSV Import (Admin Only)
        Route::get('/assets/import/template', [AssetController::class, 'downloadTemplate'])->name('assets.import.template');
        Route::post('/assets/import', [AssetController::class, 'importCsv'])->name('assets.import');

        // Users
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/send-setup-link', [UserController::class, 'sendSetupLink'])->name('users.send-setup-link');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('/users/{user}/toggle-mfa', [UserController::class, 'toggleMfa'])->name('users.toggle-mfa');
        Route::post('/users/{user}/toggle-enforce-mfa', [UserController::class, 'toggleEnforceMfa'])->name('users.toggle-enforce-mfa');
        Route::post('/users/{user}/reset-mfa', [UserController::class, 'resetMfa'])->name('users.reset-mfa');
        Route::post('/users/{user}/unlink-google', [UserController::class, 'unlinkGoogle'])->name('users.unlink-google');
        Route::post('/users/{user}/unlink-thaid', [UserController::class, 'unlinkThaid'])->name('users.unlink-thaid');
        Route::get('/users/{user}/identity-details', [UserController::class, 'getIdentityDetails'])->name('users.identity-details');

        // Departments
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // System Settings & Data Management
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-line', [SettingController::class, 'testLineNotify'])->name('settings.test-line');
        Route::post('/settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
        Route::post('/settings/clear-data', [SystemResetController::class, 'clearData'])->name('settings.clear-data');

        // Audit Logs (บันทึกกิจกรรมและความปลอดภัยระบบ)
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::post('/audit-logs/clear-old', [AuditLogController::class, 'clearOld'])->name('audit-logs.clear-old');

        // System Updates (ระบบอัปเดตระบบอัตโนมัติแบบ One-Click และผ่านไฟล์ ZIP)
        Route::get('/system-updates', [SystemUpdateController::class, 'index'])->name('system-updates.index');
        Route::post('/system-updates/check', [SystemUpdateController::class, 'check'])->name('system-updates.check');
        Route::post('/system-updates/apply', [SystemUpdateController::class, 'apply'])->name('system-updates.apply');
        Route::post('/system-updates/upload-patch', [SystemUpdateController::class, 'uploadPatch'])->name('system-updates.upload-patch');
        Route::get('/system-updates/{id}/log', [SystemUpdateController::class, 'log'])->name('system-updates.log');
    });
});
