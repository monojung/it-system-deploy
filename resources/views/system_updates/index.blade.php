@extends('layouts.app')

@section('title', 'อัปเดตระบบ (System Update)')
@section('page_title', 'ศูนย์ควบคุมการอัปเดตระบบ (System Update Center)')
@section('page_subtitle', 'ตรวจสอบเวอร์ชันใหม่ ดึงโค้ดล่าสุดจาก Git และสั่งอัปเดตระบบอัตโนมัติด้วยคลิกเดียว')

@push('styles')
<style>
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
        font-size: 18px;
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
        padding: 24px 28px;
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
        padding: 20px 24px;
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
        font-size: 32px;
        line-height: 1;
        flex-shrink: 0;
    }

    /* Commit Table */
    .commit-box {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 24px;
    }

    .commit-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        border-radius: 8px;
        background: #ffffff;
        border: 1px solid var(--border);
        margin-bottom: 8px;
        font-size: 13px;
        gap: 12px;
    }

    .commit-item:last-child {
        margin-bottom: 0;
    }

    .commit-badge {
        font-family: monospace;
        background: #0f172a;
        color: #38bdf8;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    /* Steps Preview Grid */
    .steps-pipeline-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 28px;
    }

    .pipeline-step {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .step-number {
        width: 28px;
        height: 28px;
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
        line-height: 1.3;
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
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(6px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .terminal-modal-box {
        background: #0f172a;
        color: #f8fafc;
        width: 100%;
        max-width: 800px;
        border-radius: 14px;
        border: 1px solid #334155;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 85vh;
    }

    .terminal-header {
        padding: 14px 20px;
        background: #1e293b;
        border-bottom: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .terminal-dots {
        display: flex;
        gap: 6px;
    }

    .terminal-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .terminal-body {
        padding: 20px;
        font-family: Consolas, Monaco, 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.6;
        color: #38bdf8;
        overflow-y: auto;
        flex: 1;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .terminal-footer {
        padding: 12px 20px;
        background: #1e293b;
        border-top: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
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
<div class="content-body" style="max-width: 1200px; margin: 0 auto;">

    <!-- Alert Messages -->
    @if(session('success'))
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <i class="bi bi-check-circle-fill" style="font-size: 20px; color: #10b981;"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; color: #ef4444;"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

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
                <div class="label">Git Branch & Commit</div>
                <div class="value">
                    <span style="font-family: monospace; font-size: 15px; color: #0284c7;">{{ $gitStatus['branch'] ?? 'main' }}</span>
                    <span class="commit-badge">{{ $gitStatus['short_local_commit'] ?? 'unknown' }}</span>
                </div>
            </div>
        </div>

        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #eff6ff; color: #2563eb;">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div class="stat-meta">
                <div class="label">สภาพแวดล้อมระบบ</div>
                <div class="value">
                    <span style="font-size: 14px;">PHP {{ $envInfo['php_version'] }}</span>
                    <span style="font-size: 11px; background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Laravel {{ $envInfo['laravel_version'] }}</span>
                </div>
            </div>
        </div>

        <div class="update-stat-card">
            <div class="stat-icon-wrapper" style="background: #fdf2f8; color: #db2777;">
                <i class="bi bi-hdd-network"></i>
            </div>
            <div class="stat-meta">
                <div class="label">พื้นที่ดิสก์ว่าง</div>
                <div class="value" style="font-size: 16px;">
                    {{ $envInfo['disk_free'] }}
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
                    การตรวจสอบและสั่งอัปเดตระบบ
                </h2>
                <div style="font-size: 13px; color: #64748b;">
                    ตรวจเช็คความเปลี่ยนแปลงจาก Remote Repository: <code style="color: #0284c7; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">https://github.com/monojung/it-system.git</code>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" id="btnCheckUpdates" onclick="checkRemoteUpdates()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; padding: 8px 16px; border-radius: var(--radius-sm);">
                    <i class="bi bi-arrow-clockwise" id="checkIcon"></i>
                    <span>ตรวจสอบเวอร์ชันใหม่</span>
                </button>
            </div>
        </div>

        <div class="update-action-body">
            <!-- Dynamic Status Banner -->
            <div id="statusBannerContainer">
                @if(!($gitStatus['is_git_repo'] ?? false))
                    <div class="status-banner git-error">
                        <div class="status-banner-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">ไม่พบ Git Repository บนเซิร์ฟเวอร์</div>
                            <div style="font-size: 13px; opacity: 0.9;">{{ $gitStatus['message'] ?? 'โปรเจกต์นี้ไม่ได้ถูกติดตั้งผ่าน Git หรือไม่มีโฟลเดอร์ .git' }}</div>
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
                            <div style="background: #ffffff; border: 1px dashed #fca5a5; border-radius: 8px; padding: 12px 16px; font-size: 12.5px; color: #450a0a;">
                                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-lightbulb-fill" style="color: #f59e0b;"></i> แนวทางตรวจสอบและแก้ไขปัญหา:
                                </div>
                                <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                                    <li><strong>ตรวจสอบ Internet/DNS:</strong> ทดสอบพิมพ์ <code>ping github.com</code> หรือ <code>nslookup github.com</code> ใน Terminal เครื่องเซิร์ฟเวอร์</li>
                                    <li><strong>ตั้งค่า DNS:</strong> หาก DNS ภายใน รพ. ไม่แปลงชื่อภายนอก ให้เพิ่ม Secondary DNS เป็น <code>8.8.8.8</code> หรือ <code>1.1.1.1</code></li>
                                    <li><strong>ตั้งค่า Proxy (ถ้ามี):</strong> หาก รพ. ต้องผ่าน Proxy ให้รัน: <code>git config --global http.proxy http://proxy-ip:port</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif($gitStatus['has_update'] ?? false)
                    <div class="status-banner has-updates">
                        <div class="status-banner-icon"><i class="bi bi-stars" style="color: #ca8a04;"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                                มีเวอร์ชันใหม่พร้อมให้ติดตั้ง! (พบ {{ $gitStatus['commits_behind'] }} รายการอัปเดตใหม่)
                                <span style="background: #eab308; color: #713f12; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700;">NEW UPDATE</span>
                            </div>
                            <div style="font-size: 13px; margin-bottom: 12px; color: #713f12;">
                                โค้ดในเครื่อง ({{ $gitStatus['short_local_commit'] }}) ตามหลัง Remote ({{ $gitStatus['short_remote_commit'] }}) คุณสามารถกดปุ่มเริ่มอัปเดตระบบได้ทันที
                            </div>
                            <!-- Commits preview -->
                            @if(!empty($gitStatus['commits']))
                            <div class="commit-box" style="margin-bottom: 0;">
                                <div style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 8px;">รายการเปลี่ยนแปลงล่าสุด (Incoming Commits):</div>
                                @foreach($gitStatus['commits'] as $c)
                                <div class="commit-item">
                                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
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
                                โค้ดในเครื่องตรงกับ Commit ล่าสุดบน Remote Repository แล้ว (Commit: <span style="font-family: monospace; font-weight: 600;">{{ $gitStatus['short_local_commit'] ?? '-' }}</span>)
                                ตรวจสอบล่าสุดเมื่อ: {{ $gitStatus['last_checked_at'] ?? now()->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 6-Step Pipeline Preview -->
            <div style="margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-gear-wide-connected" style="color: #0d9488;"></i>
                    ขั้นตอนการทำงานของกระบวนการอัปเดตอัตโนมัติ (Automated Pipeline):
                </div>
                <div class="steps-pipeline-grid">
                    <div class="pipeline-step">
                        <div class="step-number">1</div>
                        <div class="step-text">เปิดโหมดปรับปรุงระบบ<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Maintenance Mode</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">2</div>
                        <div class="step-text">สำรองฐานข้อมูลอัตโนมัติ<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Auto DB Backup</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">3</div>
                        <div class="step-text">ดึงโค้ดล่าสุดจาก Git<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">Git Pull Origin</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">4</div>
                        <div class="step-text">ปรับปรุงฐานข้อมูล<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan migrate</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">5</div>
                        <div class="step-text">ล้างและแคชระบบใหม่<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan optimize</span></div>
                    </div>
                    <div class="pipeline-step">
                        <div class="step-number">6</div>
                        <div class="step-text">เปิดระบบพร้อมใช้งาน<br><span style="font-size: 11px; color: #64748b; font-weight: normal;">artisan up</span></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; padding-top: 10px; border-top: 1px solid var(--border);">
                <div style="font-size: 12.5px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-lock-fill" style="color: #10b981;"></i>
                    ระบบจะสำรองฐานข้อมูลอัตโนมัติก่อนเริ่ม และเปิดระบบกลับคืนเสมอแม้เกิดข้อผิดพลาด
                </div>
                <div style="display: flex; gap: 10px;">
                    @if(!($gitStatus['is_git_repo'] ?? false))
                        <a href="#patchUploadForm" class="btn btn-primary" style="padding: 9px 18px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-sm); background: #0284c7; border-color: #0284c7;">
                            <i class="bi bi-file-earmark-zip-fill"></i>
                            <span>อัปเดตด้วยไฟล์ ZIP ด้านล่าง</span>
                        </a>
                    @elseif($gitStatus['has_update'] ?? false)
                        <button type="button" onclick="confirmAndExecuteUpdate(false)" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border-radius: var(--radius-sm); background: #0d9488; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);">
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
            <span style="font-size: 12px; background: #ffffff; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 20px; color: #0284c7; font-weight: 600;">
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
                            <strong>ขั้นตอนอัตโนมัติขณะติดตั้ง:</strong> ระบบจะสำรองข้อมูลฐานข้อมูลอัตโนมัติ &rarr; แตกไฟล์ทับโค้ดที่อัปเดต &rarr; รัน <code>php artisan migrate --force</code> &rarr; สั่ง <code>php artisan optimize:clear</code> &rarr; พร้อมใช้งานทันที
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
                    System Update Console &bull; Execution Log
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
            <button type="button" onclick="closeTerminalModal()" class="btn btn-secondary btn-sm" style="background: #334155; color: #f8fafc; border-color: #475569; font-size: 12px;">
                ปิดหน้าต่าง
            </button>
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

            if (data.has_update) {
                Swal.fire({
                    title: 'พบการอัปเดตใหม่!',
                    text: `พบการอัปเดตใหม่ ${data.commits_behind} รายการจาก GitHub คุณต้องการกดเริ่มอัปเดตหรือไม่?`,
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
                    text: `โค้ดในเครื่องตรงกับ Commit ล่าสุดบน Remote แล้ว (${data.short_local_commit})`,
                    icon: 'success',
                    confirmButtonColor: '#0d9488',
                    confirmButtonText: 'ตกลง'
                });
            } else {
                Swal.fire({
                    title: 'ตรวจสอบไม่สำเร็จ',
                    text: data.message || 'ไม่สามารถเชื่อมต่อกับ Git Remote ได้',
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
            ? 'ระบบจะเข้าสู่โหมดปรับปรุงชั่วคราว ดึงโค้ดล่าสุด สำรองข้อมูล และรัน Migration ซ้ำ'
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
                         `[2/6] กำลังสำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)...\n` +
                         `[3/6] กำลังดึงโค้ดล่าสุดจาก Git Repository (git pull)...\n` +
                         `[4/6] กำลังปรับปรุงโครงสร้างฐานข้อมูล (artisan migrate)...\n` +
                         `[5/6] กำลังล้างและสร้างแคชใหม่ (artisan optimize)...\n` +
                         `[6/6] เปิดระบบพร้อมใช้งาน (Bring Online)...\n\n` +
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
                let msg = 'เซิร์ฟเวอร์ตอบสนองช้าหรือเกิด Timeout เกิน 30 วินาที กรุณาใช้วิธีอัปเดตด้วยไฟล์ ZIP (Manual Patch Upload)';
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
</script>
@endsection
