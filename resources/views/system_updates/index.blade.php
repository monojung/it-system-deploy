@extends('layouts.app')

@section('title', 'อัปเดตระบบ (System Update)')
@section('page_title', 'ศูนย์ควบคุมการอัปเดตระบบ (System Update Center)')
@section('page_subtitle', 'ตรวจสอบเวอร์ชันใหม่ ดึงโค้ดล่าสุดจาก Git และสั่งอัปเดตระบบอัตโนมัติด้วยคลิกเดียว')

@push('styles')
<style>
    /* Hero Repository Target Card */
    .repo-hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: var(--radius-lg);
        border: 1px solid #334155;
        color: #f8fafc;
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .repo-hero-main {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .repo-icon-box {
        width: 54px;
        height: 54px;
        background: rgba(13, 148, 136, 0.2);
        border: 1.5px solid #0d9488;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #2dd4bf;
        flex-shrink: 0;
    }

    .repo-hero-info {
        min-width: 0;
    }

    .repo-hero-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .repo-hero-url {
        font-size: 16px;
        font-family: 'Consolas', 'Monaco', monospace;
        font-weight: 700;
        color: #38bdf8;
        display: flex;
        align-items: center;
        gap: 8px;
        word-break: break-all;
    }

    .repo-hero-badges {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
        flex-wrap: wrap;
    }

    .repo-badge {
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .repo-badge.branch {
        background: rgba(56, 189, 248, 0.15);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.3);
    }

    .repo-badge.remote {
        background: rgba(168, 85, 247, 0.15);
        color: #c084fc;
        border: 1px solid rgba(168, 85, 247, 0.3);
    }

    .repo-badge.sync-ok {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .repo-badge.sync-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Stats Card Grid */
    .update-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .update-stat-card {
        background: #ffffff;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .update-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-meta {
        flex: 1;
        min-width: 0;
    }

    .stat-meta .label {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 2px;
    }

    .stat-meta .value {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Main Action Card */
    .update-action-card {
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        margin-bottom: 28px;
        overflow: hidden;
    }

    .update-action-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .update-action-body {
        padding: 28px;
    }

    .status-banner {
        border-radius: var(--radius-md);
        padding: 22px 26px;
        display: flex;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
        border: 1px solid;
    }

    .status-banner.up-to-date {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .status-banner.has-updates {
        background: #fefce8;
        border-color: #fef08a;
        color: #854d0e;
    }

    .status-banner.git-error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .status-banner-icon {
        font-size: 34px;
        line-height: 1;
        flex-shrink: 0;
    }

    /* Commit Table */
    .commit-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 16px;
        margin-top: 14px;
    }

    .commit-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 11px 14px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        margin-bottom: 8px;
        font-size: 13px;
        gap: 14px;
    }

    .commit-item:last-child {
        margin-bottom: 0;
    }

    .commit-badge {
        font-family: 'Consolas', monospace;
        background: #0f172a;
        color: #38bdf8;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    /* Steps Pipeline Grid */
    .steps-pipeline-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }

    .pipeline-step {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }

    .pipeline-step:hover {
        background: #f0fdfa;
        border-color: #99f6e4;
    }

    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .step-text {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        line-height: 1.35;
    }

    /* History Table */
    .history-card {
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .history-table th {
        background: #f8fafc;
        padding: 14px 18px;
        font-weight: 600;
        color: #475569;
        border-bottom: 1.5px solid var(--border);
        white-space: nowrap;
    }

    .history-table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .history-table tbody tr:hover td {
        background: #f0fdfa;
    }

    /* Terminal Console Modal */
    .terminal-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(8px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .terminal-modal-box {
        background: #0b1120;
        color: #f8fafc;
        width: 100%;
        max-width: 860px;
        border-radius: 16px;
        border: 1px solid #334155;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 85vh;
    }

    .terminal-header {
        padding: 14px 22px;
        background: #1e293b;
        border-bottom: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .terminal-dots {
        display: flex;
        gap: 7px;
    }

    .terminal-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .terminal-body {
        padding: 22px;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.65;
        color: #38bdf8;
        overflow-y: auto;
        flex: 1;
        white-space: pre-wrap;
        word-break: break-all;
        background: #090e1a;
    }

    .terminal-footer {
        padding: 14px 22px;
        background: #1e293b;
        border-top: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="content-body" style="max-width: 1240px; margin: 0 auto; padding-bottom: 40px;">

    <!-- Alert Messages -->
    @if(session('success'))
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <i class="bi bi-check-circle-fill" style="font-size: 20px; color: #10b981;"></i>
        <div style="font-weight: 500;">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #ef4444;"></i>
        <div style="font-weight: 500;">{{ session('error') }}</div>
    </div>
    @endif

    <!-- Hero: Target Deploy Remote Repository -->
    @php
        $targetRepoUrl = $gitStatus['target_repo'] ?? config('version.update_repo_url', 'https://github.com/monojung/it-system-deploy.git');
        $targetBranch = $gitStatus['target_branch'] ?? config('version.update_branch', 'main');
        $remoteName = $gitStatus['remote_name'] ?? config('version.update_remote_name', 'deploy');
    @endphp

    <div class="repo-hero-card">
        <div class="repo-hero-main">
            <div class="repo-icon-box">
                <i class="bi bi-github"></i>
            </div>
            <div class="repo-hero-info">
                <div class="repo-hero-title">
                    <span class="pulse-dot"></span>
                    <span>Target Deployment Repository &bull; Remote Source</span>
                </div>
                <div class="repo-hero-url">
                    <span>{{ $targetRepoUrl }}</span>
                    <a href="{{ rtrim($targetRepoUrl, '.git') }}" target="_blank" title="เปิดดู Repository บน GitHub" style="color: #94a3b8; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
                <div class="repo-hero-badges">
                    <span class="repo-badge branch"><i class="bi bi-git"></i> Branch: <strong>{{ $targetBranch }}</strong></span>
                    <span class="repo-badge remote"><i class="bi bi-hdd-network"></i> Remote: <strong>{{ $remoteName }}</strong></span>
                    @if($gitStatus['has_update'] ?? false)
                        <span class="repo-badge sync-warning"><i class="bi bi-arrow-down-circle-fill"></i> มีการอัปเดตใหม่ ({{ $gitStatus['commits_behind'] }} commits)</span>
                    @elseif($gitStatus['success'] ?? false)
                        <span class="repo-badge sync-ok"><i class="bi bi-check-circle-fill"></i> อัปเดตล่าสุดแล้ว (Up-to-date)</span>
                    @else
                        <span class="repo-badge sync-warning"><i class="bi bi-exclamation-circle-fill"></i> การเชื่อมต่อขัดข้อง</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <button type="button" id="btnCheckUpdates" onclick="checkRemoteUpdates()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; padding: 10px 20px; border-radius: var(--radius-sm); background: #0d9488; border-color: #0d9488; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.4);">
                <i class="bi bi-arrow-clockwise" id="checkIcon"></i>
                <span>ตรวจสอบเวอร์ชันใหม่</span>
            </button>
        </div>
    </div>

    <!-- Environment & System Stats -->
    <div class="update-stats-grid">
        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #f0fdfa; color: #0d9488;">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div class="stat-meta">
                <div class="label">เวอร์ชันระบบปัจจุบัน</div>
                <div class="value">
                    <span>v{{ $envInfo['app_version'] }}</span>
                    <span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Build {{ config('version.build', 'Latest') }}</span>
                </div>
            </div>
        </div>

        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #f8fafc; color: #334155;">
                <i class="bi bi-git"></i>
            </div>
            <div class="stat-meta">
                <div class="label">Commit ในเครื่อง (Local)</div>
                <div class="value">
                    <span class="commit-badge" id="statLocalCommit">{{ $gitStatus['short_local_commit'] ?? 'unknown' }}</span>
                    <span style="font-size: 12px; color: #64748b; font-weight: normal;">Branch: {{ $gitStatus['branch'] ?? 'main' }}</span>
                </div>
            </div>
        </div>

        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-cloud-check-fill"></i>
            </div>
            <div class="stat-meta">
                <div class="label">Commit ล่าสุด (Deploy Remote)</div>
                <div class="value">
                    <span class="commit-badge" style="background: #0284c7; color: #ffffff;" id="statRemoteCommit">{{ $gitStatus['short_remote_commit'] ?? 'unknown' }}</span>
                    @if($gitStatus['has_update'] ?? false)
                        <span style="font-size: 11px; background: #fef08a; color: #854d0e; padding: 2px 6px; border-radius: 4px; font-weight: 700;">+{{ $gitStatus['commits_behind'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #fdf2f8; color: #db2777;">
                <i class="bi bi-hdd-network"></i>
            </div>
            <div class="stat-meta">
                <div class="label">สภาพแวดล้อม & พื้นที่ว่าง</div>
                <div class="value" style="font-size: 15px;">
                    <span>PHP {{ $envInfo['php_version'] }}</span> &bull; <span>ว่าง {{ $envInfo['disk_free'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Update Control Card -->
    <div class="update-action-card">
        <div class="update-action-header">
            <div>
                <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-cloud-arrow-down-fill" style="color: #0d9488;"></i>
                    การตรวจสอบและสั่งอัปเดตระบบอัตโนมัติ (Online Git Pipeline)
                </h2>
                <div style="font-size: 13px; color: #64748b;">
                    ดึงความเปลี่ยนแปลงล่าสุดจาก Remote Repository: <code style="color: #0284c7; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $targetRepoUrl }}</code>
                </div>
            </div>
            <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-clock"></i>
                <span id="lastCheckedText">ตรวจสอบล่าสุด: {{ $gitStatus['last_checked_at'] ?? now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>

        <div class="update-action-body">
            <!-- Dynamic Status Banner -->
            <div id="statusBannerContainer">
                @if(!($gitStatus['is_git_repo'] ?? false))
                    <div class="status-banner git-error" style="background: #f8fafc; border-color: #cbd5e1; color: #1e293b;">
                        <div class="status-banner-icon" style="color: #64748b;"><i class="bi bi-git"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                ยังไม่ได้เชื่อมต่อ Git Repository บนเซิร์ฟเวอร์นี้
                                <span style="background: #e2e8f0; color: #475569; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700;">NO .GIT FOLDER</span>
                            </div>
                            <div style="font-size: 13px; color: #475569; margin-bottom: 12px; line-height: 1.5;">
                                โฟลเดอร์ของโปรเจกต์นี้ไม่ได้ถูก clone ผ่าน Git หรือถูกแตกไฟล์มาจาก ZIP โดยตรง ทำให้ยังไม่มีโฟลเดอร์ <code>.git</code> สำหรับดึงอัปเดตอัตโนมัติ
                            </div>

                            @if($gitStatus['git_cli_available'] ?? true)
                            <div style="background: #ffffff; border: 1px solid #99f6e4; border-radius: 8px; padding: 14px 18px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                <div>
                                    <div style="font-weight: 700; color: #0f766e; font-size: 13.5px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-check-circle-fill" style="color: #0d9488;"></i> ตรวจพบโปรแกรม Git พร้อมทำงานบนเซิร์ฟเวอร์!
                                    </div>
                                    <div style="font-size: 12.5px; color: #334155; margin-top: 2px;">
                                        คุณสามารถกดปุ่มเชื่อมต่อ Git กับ <code>{{ $targetRepoUrl }}</code> เพื่อเริ่มใช้งานระบบอัปเดตอัตโนมัติได้ทันที
                                    </div>
                                </div>
                                <button type="button" onclick="connectGitRepository()" class="btn btn-primary" style="background: #0d9488; border-color: #0d9488; font-size: 13px; font-weight: 700; padding: 8px 18px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                    <span>เชื่อมต่อ Git Repository ทันที</span>
                                </button>
                            </div>
                            @else
                            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px; font-size: 12.5px; color: #92400e;">
                                <i class="bi bi-exclamation-triangle-fill" style="color: #d97706;"></i> <strong>เซิร์ฟเวอร์นี้ไม่มีโปรแกรม Git (Git CLI is not installed):</strong> แนะนำให้ใช้ฟังก์ชัน <strong>"อัปเดตระบบด้วยไฟล์แพตช์ ZIP"</strong> ด้านล่างแทน
                            </div>
                            @endif

                            <!-- Manual SSH command instructions collapsible -->
                            <details style="background: #0f172a; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #94a3b8;">
                                <summary style="cursor: pointer; font-weight: 600; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-terminal"></i> คลิกดูคำสั่ง Terminal / SSH สำหรับติดตั้ง Git Repository ด้วยตนเอง
                                </summary>
                                <div style="margin-top: 10px;">
                                    <p style="margin: 0 0 6px 0; color: #cbd5e1;">เปิด Terminal / SSH บนเซิร์ฟเวอร์ เข้าไปยังโฟลเดอร์โปรเจกต์ แล้วรันคำสั่งต่อไปนี้:</p>
                                    <pre style="background: #020617; border: 1px solid #334155; color: #38bdf8; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; line-height: 1.6; margin: 0; white-space: pre-wrap;">git init
git config --global --add safe.directory "*"
git remote add origin {{ $targetRepoUrl }}
git fetch origin {{ $targetBranch }}
git checkout -B {{ $targetBranch }}
git reset --mixed origin/{{ $targetBranch }}
git branch --set-upstream-to=origin/{{ $targetBranch }} {{ $targetBranch }}</pre>
                                </div>
                            </details>
                        </div>
                    </div>
                @elseif(!($gitStatus['success'] ?? true))
                    <div class="status-banner git-error" style="background: #fef2f2; border-color: #fca5a5; color: #991b1b;">
                        <div class="status-banner-icon"><i class="bi bi-wifi-off" style="color: #ef4444;"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                ไม่สามารถเชื่อมต่อกับ Git Remote ได้ ({{ $gitStatus['error_type'] ?? 'การเชื่อมต่อขัดข้อง' }})
                                <span style="background: #fee2e2; color: #b91c1c; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700;">CONNECT ERROR</span>
                            </div>
                            <div style="font-size: 13px; margin-bottom: 12px; color: #7f1d1d; line-height: 1.5;">
                                {{ $gitStatus['hint'] ?? $gitStatus['message'] }}
                            </div>
                            <div style="background: #ffffff; border: 1px dashed #fca5a5; border-radius: 8px; padding: 14px 18px; font-size: 12.5px; color: #450a0a;">
                                <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-lightbulb-fill" style="color: #f59e0b;"></i> แนวทางตรวจสอบและแก้ไขปัญหาการเชื่อมต่อไปยัง Remote:
                                </div>
                                <ul style="margin: 0; padding-left: 20px; line-height: 1.65;">
                                    <li><strong>เป้าหมาย:</strong> <code>{{ $targetRepoUrl }}</code></li>
                                    <li><strong>การทดสอบเครือข่าย:</strong> ทดสอบคำสั่ง <code>ping github.com</code> หรือ <code>curl.exe -I https://github.com</code> ใน Terminal ของเครื่องเซิร์ฟเวอร์</li>
                                    <li><strong>สิทธิ์การเข้าถึง Private Repo:</strong> หาก Repository เป็น Private ตรวจสอบว่าเซิร์ฟเวอร์ได้ตั้งค่า Git Credentials Helper, SSH Key หรือ Personal Access Token (PAT) ไว้แล้ว</li>
                                    <li><strong>การตั้งค่า Proxy:</strong> หาก รพ. ต้องผ่าน Proxy ให้รัน: <code>git config --global http.proxy http://proxy-ip:port</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif($gitStatus['has_update'] ?? false)
                    <div class="status-banner has-updates">
                        <div class="status-banner-icon"><i class="bi bi-stars" style="color: #ca8a04;"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                มีเวอร์ชันใหม่พร้อมให้ติดตั้ง! (พบ {{ $gitStatus['commits_behind'] }} รายการอัปเดตจาก Deploy Repository)
                                <span style="background: #eab308; color: #713f12; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700;">NEW UPDATE</span>
                            </div>
                            <div style="font-size: 13px; margin-bottom: 12px; color: #713f12;">
                                โค้ดในเครื่อง (<span style="font-family: monospace; font-weight: 700;">{{ $gitStatus['short_local_commit'] }}</span>) ตามหลัง Remote (<span style="font-family: monospace; font-weight: 700; color: #0284c7;">{{ $gitStatus['short_remote_commit'] }}</span>) คุณสามารถกดปุ่มเริ่มอัปเดตระบบได้ทันที
                            </div>
                            <!-- Commits preview -->
                            @if(!empty($gitStatus['commits']))
                            <div class="commit-box">
                                <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-journal-text" style="color: #0d9488;"></i> รายการเปลี่ยนแปลงล่าสุดบน Remote (Incoming Commits):
                                </div>
                                @foreach($gitStatus['commits'] as $c)
                                <div class="commit-item">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <span class="commit-badge">{{ $c['hash'] }}</span>
                                        <span style="font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $c['message'] }}</span>
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; white-space: nowrap;">
                                        <span>{{ $c['author'] }}</span> &bull; <span>{{ $c['date'] }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="status-banner up-to-date">
                        <div class="status-banner-icon"><i class="bi bi-shield-check" style="color: #10b981;"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                ระบบของคุณเป็นเวอร์ชันล่าสุดแล้ว (Up to date)
                                <span style="background: #dcfce7; color: #15803d; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700;">LATEST</span>
                            </div>
                            <div style="font-size: 13px; opacity: 0.9;">
                                โค้ดในเครื่องตรงกับ Commit ล่าสุดบน Remote Repository <code>{{ $targetRepoUrl }}</code> แล้ว (Commit: <span style="font-family: monospace; font-weight: 700;">{{ $gitStatus['short_local_commit'] ?? '-' }}</span>)
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 6-Step Pipeline Preview -->
            <div style="margin-bottom: 22px;">
                <div style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-gear-wide-connected" style="color: #0d9488;"></i>
                    ขั้นตอนการทำงานของกระบวนการอัปเดตอัตโนมัติ (Automated Pipeline):
                </div>
                <div class="steps-pipeline-grid">
                    <div class="pipeline-step">
                        <div class="step-number">1</div>
                        <div class="step-text">เปิดโหมดปรับปรุง<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Maintenance Mode</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">2</div>
                        <div class="step-text">สำรองฐานข้อมูล<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Auto DB Backup</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">3</div>
                        <div class="step-text">ดึงโค้ดล่าสุด<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Git Sync Deploy Repo</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">4</div>
                        <div class="step-text">ปรับปรุงฐานข้อมูล<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan migrate</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">5</div>
                        <div class="step-text">ล้างและแคชระบบ<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan optimize</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">6</div>
                        <div class="step-text">เปิดระบบพร้อมใช้งาน<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan up</span></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding-top: 14px; border-top: 1px solid var(--border);">
                <div style="font-size: 12.5px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-lock-fill" style="color: #10b981;"></i>
                    <span>ระบบจะสำรองฐานข้อมูลอัตโนมัติก่อนเริ่ม และเปิดระบบกลับคืนเสมอแม้เกิดข้อผิดพลาด</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    @if(!($gitStatus['is_git_repo'] ?? false))
                        @if($gitStatus['git_cli_available'] ?? true)
                            <button type="button" onclick="connectGitRepository()" class="btn btn-primary" style="padding: 9px 18px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); background: #0d9488; border-color: #0d9488;">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <span>เชื่อมต่อ Git Repository</span>
                            </button>
                        @endif
                        <a href="#patchUploadForm" class="btn btn-secondary" style="padding: 9px 18px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm);">
                            <i class="bi bi-file-earmark-zip-fill"></i>
                            <span>อัปเดตด้วยไฟล์ ZIP ด้านล่าง</span>
                        </a>
                    @elseif($gitStatus['has_update'] ?? false)
                        <button type="button" onclick="confirmAndExecuteUpdate(false)" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border-radius: var(--radius-sm); background: #0d9488; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                            <span>เริ่มการอัปเดตระบบทันที (Update Now)</span>
                        </button>
                    @else
                        <button type="button" onclick="confirmAndExecuteUpdate(true)" class="btn btn-secondary" style="padding: 9px 18px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm);">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>บังคับอัปเดตซ้ำ (Force Re-deploy)</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Patch ZIP Upload Card -->
    <div class="update-action-card" style="margin-bottom: 28px;">
        <div class="update-action-header" style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);">
            <div>
                <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-file-earmark-zip-fill" style="color: #0284c7;"></i>
                    อัปเดตระบบด้วยไฟล์แพตช์ ZIP (Manual Patch Upload)
                </h2>
                <div style="font-size: 13px; color: #475569;">
                    สำหรับเซิร์ฟเวอร์ที่ไม่ได้ติดตั้งผ่าน Git หรือใช้งานบน Web Hosting ทั่วไป สามารถอัปโหลดไฟล์ <code>update-patch.zip</code> เพื่ออัปเดตได้ทันที
                </div>
            </div>
            <span style="font-size: 12px; background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 12px; border-radius: 20px; color: #0284c7; font-weight: 600;">
                <i class="bi bi-shield-check"></i> ปลอดภัย ไม่ทับ .env และ storage
            </span>
        </div>

        <div class="update-action-body">
            <form action="{{ route('system-updates.upload-patch') }}" method="POST" enctype="multipart/form-data" id="patchUploadForm" onsubmit="return confirmPatchUpload(event);">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 260px;">
                            <label for="patch_file" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                เลือกไฟล์แพตช์อัปเดต (.zip):
                            </label>
                            <input type="file" name="patch_file" id="patch_file" accept=".zip" required class="form-control" style="font-size: 13px; padding: 8px 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); width: 100%; background: #f8fafc;">
                        </div>
                        <div style="padding-top: 24px;">
                            <button type="submit" id="btnSubmitPatch" class="btn btn-primary" style="padding: 10px 22px; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border-radius: var(--radius-sm); background: #0284c7; border-color: #0284c7; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);">
                                <i class="bi bi-upload"></i>
                                <span>เริ่มติดตั้งไฟล์แพตช์ (Install Patch)</span>
                            </button>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; font-size: 12.5px; color: #475569; display: flex; align-items: flex-start; gap: 10px;">
                        <i class="bi bi-info-circle-fill" style="color: #0284c7; font-size: 16px; margin-top: 1px; flex-shrink: 0;"></i>
                        <div style="line-height: 1.5;">
                            <strong>ขั้นตอนอัตโนมัติขณะติดตั้ง:</strong> สำรองฐานข้อมูลอัตโนมัติ &rarr; แตกไฟล์ทับโค้ดที่อัปเดต &rarr; รัน <code>php artisan migrate --force</code> &rarr; สั่ง <code>php artisan optimize:clear</code> &rarr; พร้อมใช้งานทันที
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Update History -->
    <div class="history-card">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-clock-history" style="font-size: 18px; color: #0d9488;"></i>
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0;">ประวัติการอัปเดตระบบ (Update History)</h3>
            </div>
            <span style="font-size: 12px; color: #64748b;">ทั้งหมด {{ $updates->total() }} รายการ</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="history-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>เวอร์ชัน / Commit</th>
                        <th>สถานะ</th>
                        <th>ไฟล์ Backup อัตโนมัติ</th>
                        <th>ระยะเวลา</th>
                        <th>ผู้ดำเนินการ</th>
                        <th>วันเวลาที่ดำเนินการ</th>
                        <th style="text-align: right;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($updates as $update)
                    <tr>
                        <td><span style="font-weight: 600; color: #64748b;">#{{ $update->id }}</span></td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a;">v{{ $update->version ?? '-' }}</div>
                            <div style="font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                <span class="commit-badge" style="font-size: 11px; padding: 1px 6px;">{{ $update->short_commit }}</span>
                                @if($update->previous_commit && $update->previous_commit !== 'unknown')
                                <span style="font-size: 11px;">(จาก {{ $update->short_previous_commit }})</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php $badge = $update->status_badge; @endphp
                            <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                <i class="bi {{ $badge['icon'] }}"></i>
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td>
                            @if($update->backup_file)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-file-earmark-lock-fill" style="color: #0d9488;"></i>
                                <span style="font-family: monospace; font-size: 12px; color: #334155;">{{ $update->backup_file }}</span>
                            </div>
                            @else
                            <span style="color: #94a3b8; font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>{{ $update->formatted_duration }}</td>
                        <td>
                            <div style="font-weight: 500; color: #1e293b;">
                                {{ $update->triggeredBy ? ($update->triggeredBy->name ?? $update->triggeredBy->username) : 'ระบบ / CLI' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12.5px; color: #334155;">
                                {{ $update->started_at ? $update->started_at->format('d/m/Y H:i:s') : '-' }}
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" onclick="viewLog({{ $update->id }})" class="btn btn-secondary btn-sm" style="font-size: 12px; padding: 5px 12px; border-radius: 6px;">
                                <i class="bi bi-terminal"></i> ดู Log
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 36px; color: #64748b;">
                            <i class="bi bi-inbox" style="font-size: 32px; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                            ยังไม่มีประวัติการอัปเดตระบบในฐานข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($updates->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid var(--border);">
            {{ $updates->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Realtime Terminal Log Modal -->
<div id="terminalModal" class="terminal-modal-backdrop">
    <div class="terminal-modal-box">
        <div class="terminal-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="terminal-dots">
                    <div class="terminal-dot" style="background: #ef4444;"></div>
                    <div class="terminal-dot" style="background: #f59e0b;"></div>
                    <div class="terminal-dot" style="background: #10b981;"></div>
                </div>
                <div id="terminalTitle" style="font-size: 13px; font-weight: 600; color: #94a3b8; font-family: monospace;">
                    System Update Console &bull; Execution Log Stream
                </div>
            </div>
            <button type="button" onclick="closeTerminalModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div id="terminalBody" class="terminal-body">กำลังโหลดข้อความบันทึก...</div>
        <div class="terminal-footer">
            <div id="terminalStatus" style="font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-terminal-fill" style="color: #38bdf8;"></i>
                <span>Console Ready</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" onclick="copyTerminalLog()" class="btn btn-secondary btn-sm" style="background: #334155; color: #f8fafc; border-color: #475569; font-size: 12px;">
                    <i class="bi bi-clipboard"></i> คัดลอก Log
                </button>
                <button type="button" onclick="closeTerminalModal()" class="btn btn-secondary btn-sm" style="background: #1e293b; color: #f8fafc; border-color: #475569; font-size: 12px;">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Check Remote Updates via AJAX
    function checkRemoteUpdates() {
        const btn = document.getElementById('btnCheckUpdates');
        const icon = document.getElementById('checkIcon');
        btn.disabled = true;
        icon.classList.add('spin');

        fetch("{{ route('system-updates.check') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            icon.classList.remove('spin');

            if (data.last_checked_at) {
                const checkedElem = document.getElementById('lastCheckedText');
                if (checkedElem) checkedElem.innerText = 'ตรวจสอบล่าสุด: ' + data.last_checked_at;
            }

            if (data.short_local_commit) {
                const localElem = document.getElementById('statLocalCommit');
                if (localElem) localElem.innerText = data.short_local_commit;
            }

            if (data.short_remote_commit) {
                const remoteElem = document.getElementById('statRemoteCommit');
                if (remoteElem) remoteElem.innerText = data.short_remote_commit;
            }

            if (data.has_update) {
                Swal.fire({
                    title: 'พบการอัปเดตใหม่!',
                    html: `พบการอัปเดตใหม่ <b>${data.commits_behind}</b> รายการจาก Remote Repository:<br>` +
                          `<code style="font-size:12px; color:#0284c7;">${data.target_repo || 'https://github.com/monojung/it-system-deploy.git'}</code><br><br>` +
                          `คุณต้องการรีเฟรชหน้าจอเพื่อเริ่มอัปเดตหรือไม่?`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#0d9488',
                    confirmButtonText: 'รีเฟรชหน้าเพื่อเริ่มอัปเดต',
                    cancelButtonText: 'ปิด'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else if (data.success) {
                Swal.fire({
                    title: 'ระบบเป็นเวอร์ชันล่าสุดแล้ว',
                    html: `โค้ดในเครื่องตรงกับ Commit ล่าสุดบน Remote Repository แล้ว<br>` +
                          `Commit: <code style="color:#0d9488; font-weight:bold;">${data.short_local_commit}</code>`,
                    icon: 'success',
                    confirmButtonColor: '#0d9488',
                    confirmButtonText: 'ตกลง'
                });
            } else {
                Swal.fire({
                    title: 'ตรวจสอบไม่สำเร็จ',
                    html: `<div style="text-align:left; font-size:13px;">${data.message || 'ไม่สามารถเชื่อมต่อกับ Git Remote ได้'}</div>`,
                    icon: 'warning',
                    confirmButtonColor: '#0d9488',
                    confirmButtonText: 'ตกลง'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            icon.classList.remove('spin');
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + err.message,
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    // Confirm and Execute Update
    function confirmAndExecuteUpdate(isForce) {
        const title = isForce ? 'ยืนยันการบังคับอัปเดตซ้ำ?' : 'ยืนยันการเริ่มอัปเดตระบบ?';
        const text = isForce
            ? 'ระบบจะเข้าสู่โหมดปรับปรุงชั่วคราว ดึงโค้ดล่าสุดจาก https://github.com/monojung/it-system-deploy.git สำรองข้อมูล และรัน Migration ซ้ำ'
            : 'ระบบจะเข้าสู่โหมดปรับปรุงชั่วคราวประมาณ 30-60 วินาที และสำรองฐานข้อมูลอัตโนมัติก่อนเริ่ม';

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'ยืนยัน เริ่มอัปเดตทันที',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                executeSystemUpdate();
            }
        });
    }

    // Execute System Update via AJAX with Live Terminal Display
    function executeSystemUpdate() {
        const modal = document.getElementById('terminalModal');
        const body = document.getElementById('terminalBody');
        const status = document.getElementById('terminalStatus');

        modal.style.display = 'flex';
        body.innerHTML = `> [${new Date().toLocaleTimeString()}] เริ่มต้นกระบวนการอัปเดตระบบ One-Click Update...\n` +
                         `> [${new Date().toLocaleTimeString()}] กำลังส่งคำสั่งไปยังเซิร์ฟเวอร์ กรุณารอสักครู่ ห้ามปิดหน้าต่างนี้...\n\n` +
                         `[1/6] เปิดโหมดปรับปรุงระบบ (Maintenance Mode)...\n` +
                         `[2/6] กำลังสำรองฐานข้อมูลอัตโนมัติ (Auto-Backup it_* tables)...\n` +
                         `[3/6] กำลังดึงโค้ดล่าสุดจาก Remote Repository (git pull deploy main)...\n` +
                         `[4/6] กำลังปรับปรุงโครงสร้างฐานข้อมูล (artisan migrate --force)...\n` +
                         `[5/6] กำลังล้างและสร้างแคชใหม่ (artisan optimize:clear)...\n` +
                         `[6/6] เปิดระบบพร้อมใช้งาน (artisan up)...\n\n` +
                         `>>> กรุณารอผลการดำเนินการจากเซิร์ฟเวอร์...`;

        status.innerHTML = `<i class="bi bi-arrow-repeat spin" style="color: #38bdf8;"></i> <span>กำลังดำเนินการอัปเดตระบบ...</span>`;

        fetch("{{ route('system-updates.apply') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (response.status === 419) {
                throw new Error('เซสชันหมดอายุ (CSRF Mismatch) กรุณารีเฟรชหน้าเว็บ หรือใช้วิธีอัปเดตด้วยไฟล์ ZIP ด้านล่าง');
            }
            if (response.status === 504 || response.status === 500) {
                let msg = 'เซิร์ฟเวอร์ตอบสนองช้าหรือเกิด Timeout เกินกำหนด กรุณาตรวจสอบ Log หรือใช้วิธีอัปเดตด้วยไฟล์ ZIP (Manual Patch Upload)';
                try {
                    const errData = await response.json();
                    if (errData.message) msg = errData.message;
                } catch(e) {}
                throw new Error(msg);
            }
            const data = await response.json();
            if (response.ok && data.success) {
                body.innerHTML = data.record.log || 'การอัปเดตระบบเสร็จสิ้นแล้ว!';
                status.innerHTML = `<i class="bi bi-check-circle-fill" style="color: #10b981;"></i> <span style="color: #10b981; font-weight: bold;">อัปเดตสำเร็จเรียบร้อยแล้ว (${data.record.duration})</span>`;

                Swal.fire({
                    title: 'อัปเดตระบบสำเร็จเรียบร้อยแล้ว!',
                    html: `เวอร์ชันใหม่: <b>v${data.record.version}</b> (Commit: <code>${data.record.commit}</code>)<br>` +
                          `สำรองฐานข้อมูลไว้ที่: <code>${data.record.backup_file || '-'}</code><br>` +
                          `ระยะเวลาดำเนินการ: <b>${data.record.duration}</b>`,
                    icon: 'success',
                    confirmButtonColor: '#0d9488',
                    confirmButtonText: 'โหลดหน้าใหม่'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดระหว่างดำเนินการ');
            }
        })
        .catch(err => {
            body.innerHTML += `\n\n[ERROR]: ${err.message}\n>>> หมายเหตุ: ระบบได้พยายามเปิดโหมดออนไลน์ (php artisan up) กลับคืนให้โดยอัตโนมัติแล้ว`;
            status.innerHTML = `<i class="bi bi-x-circle-fill" style="color: #ef4444;"></i> <span style="color: #ef4444; font-weight: bold;">การอัปเดตล้มเหลว</span>`;

            Swal.fire({
                title: 'การอัปเดตระบบล้มเหลว',
                text: err.message,
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'ปิด'
            });
        });
    }

    // View past update log
    function viewLog(id) {
        const modal = document.getElementById('terminalModal');
        const body = document.getElementById('terminalBody');
        const status = document.getElementById('terminalStatus');
        const title = document.getElementById('terminalTitle');

        modal.style.display = 'flex';
        body.innerHTML = 'กำลังโหลด Log...';
        status.innerHTML = 'กำลังดึงข้อมูล...';

        fetch(`{{ url('/system-updates') }}/${id}/log`)
        .then(res => res.json())
        .then(data => {
            title.innerHTML = `System Update #${data.id} &bull; v${data.version} (${data.commit})`;
            body.innerHTML = data.output_log || 'ไม่มีบันทึกข้อความ';
            status.innerHTML = `<i class="bi bi-info-circle-fill" style="color: #38bdf8;"></i> <span>สถานะ: ${data.status_badge.label} &bull; ใช้เวลา: ${data.duration}</span>`;
        })
        .catch(err => {
            body.innerHTML = 'เกิดข้อผิดพลาดในการโหลด Log: ' + err.message;
        });
    }

    function copyTerminalLog() {
        const body = document.getElementById('terminalBody');
        if (body && body.innerText) {
            navigator.clipboard.writeText(body.innerText).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'คัดลอก Log เรียบร้อยแล้ว',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        }
    }

    function confirmPatchUpload(event) {
        const fileInput = document.getElementById('patch_file');
        if (!fileInput.files.length) {
            Swal.fire({
                title: 'กรุณาเลือกไฟล์',
                text: 'กรุณาเลือกไฟล์แพตช์อัปเดต (.zip) ก่อนดำเนินการ',
                icon: 'warning',
                confirmButtonColor: '#0d9488'
            });
            event.preventDefault();
            return false;
        }

        const btn = document.getElementById('btnSubmitPatch');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังติดตั้งแพตช์ กรุณารอสักครู่...';
        return true;
    }

    function closeTerminalModal() {
        document.getElementById('terminalModal').style.display = 'none';
    }

    function connectGitRepository() {
        Swal.fire({
            title: 'เชื่อมต่อ Git Repository?',
            text: 'ระบบจะทำการรันคำสั่ง git init และเชื่อมโยงกับ {{ $targetRepoUrl }} (branch: {{ $targetBranch }}) โดยไม่กระทบไฟล์ตั้งค่า .env หรือข้อมูลในระบบ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '⚡ ยืนยันเชื่อมต่อ Git ทันที',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังเชื่อมต่อ Git Repository...',
                    html: 'กำลังดึงประวัติ Commit จาก GitHub กรุณารอสักครู่ (ประมาณ 5-30 วินาที)...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("system-updates.init-git") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'เชื่อมต่อ Git สำเร็จ!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0d9488',
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'ไม่สามารถเชื่อมต่อ Git ได้',
                            html: `<div style="text-align:left;font-size:13px;color:#ef4444;background:#fef2f2;padding:12px;border-radius:6px;margin-bottom:10px;">${data.message}</div><p style="font-size:12px;color:#64748b;">หากเซิร์ฟเวอร์ไม่มี Git กรุณาใช้วิธี Manual Patch ZIP ด้านล่างแทน</p>`,
                            icon: 'error',
                            confirmButtonColor: '#64748b'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาดในการส่งคำสั่ง',
                        text: err.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                });
            }
        });
    }
</script>
@endsection
