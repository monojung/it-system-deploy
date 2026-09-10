@extends('layouts.app')

@section('title', 'สำรองและกู้คืนฐานข้อมูล')
@section('page_title', 'ระบบสำรองและกู้คืนฐานข้อมูล (Backup & Restore)')
@section('page_subtitle', 'จัดการสำรองฐานข้อมูล MariaDB 192.168.2.10 (c3thchospital) อย่างปลอดภัย')

@push('styles')
<style>
    /* Table & Scroll Enhancements */
    .backup-table-wrap {
        overflow-x: auto;
        border-radius: 0 0 14px 14px;
    }

    .backup-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        margin-bottom: 0;
    }

    .backup-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        padding: 14px 18px;
        border-bottom: 1.5px solid var(--border);
        white-space: nowrap;
    }

    .backup-table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        transition: background 0.15s;
    }

    .backup-table tbody tr:hover td {
        background: #f0fdfa;
    }

    .action-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Modern Delete Confirmation Modal */
    .custom-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(5px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .custom-modal-backdrop.active {
        display: flex;
    }

    .custom-modal-dialog {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        border: 1px solid var(--border);
        animation: modalScaleIn 0.22s ease-out;
    }

    @keyframes modalScaleIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(8px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>
@endpush

@section('content')
<!-- Database Status & Storage Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 18px 22px; border-radius: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="bi bi-database-check"></i>
            </div>
            <div>
                <div style="font-size: 12.5px; color: var(--text-muted);">สถานะฐานข้อมูล</div>
                <div style="font-size: 16px; font-weight: 700; color: #065f46;">เชื่อมต่อปกติ (Online)</div>
                <div style="font-size: 11.5px; color: var(--text-muted); font-family: monospace;">{{ $dbHost }}:3306 &bull; {{ $dbName }}</div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px 22px; border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="bi bi-hdd-fill"></i>
                </div>
                <div>
                    <div style="font-size: 12.5px; color: var(--text-muted);">พื้นที่จัดเก็บไฟล์สำรอง</div>
                    <div style="font-size: 18px; font-weight: 700; color: #0369a1;">{{ $formattedStorage }}</div>
                    <div style="font-size: 11.5px; color: var(--text-muted);">storage/app/backups/</div>
                </div>
            </div>
            @if(isset($orphanCount) && $orphanCount > 0)
                <form action="{{ route('backups.clean-orphans') }}" method="POST" onsubmit="return confirm('ยืนยันล้างไฟล์สำรองตกค้างที่ไม่มีในระบบ {{ $orphanCount }} ไฟล์ ({{ $orphanSizeFormatted }}) เพื่อคืนพื้นที่จัดเก็บหรือไม่?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 11.5px; padding: 5px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;" title="ล้างไฟล์ตกค้างบนเซิร์ฟเวอร์ที่ไม่มีประวัติในฐานข้อมูล">
                        <i class="bi bi-trash3"></i> ล้างไฟล์ตกค้าง ({{ $orphanCount }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px 22px; border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $autoBackupEnabled ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $autoBackupEnabled ? '#059669' : '#64748b' }}; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="bi {{ $autoBackupEnabled ? 'bi-shield-check' : 'bi-shield-slash' }}"></i>
                </div>
                <div>
                    <div style="font-size: 12.5px; color: var(--text-muted);">ความปลอดภัยของข้อมูล</div>
                    <div style="font-size: 16px; font-weight: 700; color: {{ $autoBackupEnabled ? '#065f46' : '#334155' }}; display: flex; align-items: center; gap: 6px;">
                        <span>ระบบ Auto-Backup:</span>
                        @if($autoBackupEnabled)
                            <span class="badge" style="background: #10b981; color: white; font-size: 11.5px; padding: 2px 8px; border-radius: 6px;">เปิดใช้งาน</span>
                        @else
                            <span class="badge" style="background: #64748b; color: white; font-size: 11.5px; padding: 2px 8px; border-radius: 6px;">ปิดใช้งาน</span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                        {{ $autoBackupEnabled ? 'สำรองอัตโนมัติก่อน Restore ทุกครั้ง' : 'ไม่มีการสำรองอัตโนมัติ (ประหยัดพื้นที่)' }}
                    </div>
                </div>
            </div>

            <!-- Auto-Backup Toggle Button -->
            <form action="{{ route('backups.toggle-auto') }}" method="POST" style="margin: 0;">
                @csrf
                @if($autoBackupEnabled)
                    <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 12.5px; font-weight: 600; padding: 7px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;" title="คลิกเพื่อปิดการทำงานระบบ Auto-Backup">
                        <i class="bi bi-power"></i>
                        <span>ปิดใช้งาน Auto-Backup</span>
                    </button>
                @else
                    <button type="submit" class="btn btn-success btn-sm" style="font-size: 12.5px; font-weight: 600; padding: 7px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);" title="คลิกเพื่อเปิดการทำงานระบบ Auto-Backup">
                        <i class="bi bi-power"></i>
                        <span>เปิดใช้งาน Auto-Backup</span>
                    </button>
                @endif
            </form>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- 1. One-Click Backup Card -->
    <div class="card" style="margin-bottom: 0; border-radius: 14px; border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
        <div class="card-header" style="padding: 18px 24px;">
            <div class="card-title" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 20px;"></i>
                <span>สั่งสำรองฐานข้อมูลทันที (Create New Backup)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form action="{{ route('backups.create') }}" method="POST" id="backupForm">
                @csrf
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label required" style="font-size: 13.5px; font-weight: 600;">ขอบเขตข้อมูลที่ต้องการสำรอง</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1.5px solid #0d9488; background: #f0fdfa; border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="scope" value="it_tables" checked style="margin-top: 3px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 600; font-size: 13.5px; color: #0f766e;">
                                    เฉพาะตารางระบบสารสนเทศ (ตาราง it_*) [แนะนำ]
                                </div>
                                <div style="font-size: 12px; color: #115e59; margin-top: 2px;">
                                    สำรองเฉพาะตารางแจ้งซ่อม คลังครุภัณฑ์ คลังอะไหล่ และผู้ใช้งานของระบบ IT ปลอดภัย รวดเร็ว และไม่กระทบตารางอื่น
                                </div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer;">
                            <input type="radio" name="scope" value="full_db" style="margin-top: 3px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text-main);">
                                    ฐานข้อมูลทั้งหมดใน c3thchospital (Full Database)
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    สำรองทุกตารางทั้งหมดในฐานข้อมูล
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="notes" style="font-size: 13.5px; font-weight: 600;">บันทึกช่วยจำ (Notes)</label>
                    <input type="text" id="notes" name="notes" class="form-control" placeholder="เช่น สำรองข้อมูลประจำสัปดาห์, ก่อนปรับปรุงระบบ..." style="height: 40px; font-size: 13.5px; border-radius: 8px;">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; font-weight: 600; border-radius: 8px;" id="btnRunBackup">
                    <i class="bi bi-database-down"></i>
                    <span>เริ่มกระบวนการสำรองข้อมูล (Run Backup)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 2. Restore Database Card -->
    <div class="card" style="margin-bottom: 0; border-radius: 14px; border: 1px solid var(--border); border-top: 4px solid #f59e0b; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
        <div class="card-header" style="padding: 18px 24px;">
            <div class="card-title" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="bi bi-arrow-counterclockwise text-warning" style="font-size: 20px;"></i>
                <span>กู้คืนฐานข้อมูล (Restore Database)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 24px;">
            <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data" id="restoreForm">
                @csrf

                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #92400e; display: flex; align-items: flex-start; gap: 10px;">
                    <i class="bi bi-shield-exclamation" style="font-size: 18px; color: #d97706; margin-top: 1px;"></i>
                    <div>
                        <strong>คำเตือน:</strong> การกู้คืนฐานข้อมูลจะเขียนทับข้อมูลเดิมในตารางที่ถูกระบุในไฟล์
                        @if($autoBackupEnabled)
                            (ระบบเปิด Auto-Backup ไว้ จะทำการสำรอง Snapshot ให้อัตโนมัติก่อนเริ่มกู้คืน)
                        @else
                            (ระบบ Auto-Backup ปิดอยู่ หากต้องการทำ Snapshot สำรองฉุกเฉินก่อนกู้คืน สามารถติ๊กเลือกด้านล่างได้)
                        @endif
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label required" style="font-size: 13.5px; font-weight: 600;">เลือกแหล่งไฟล์ที่ต้องการกู้คืน</label>
                    <div style="display: flex; gap: 20px; margin-bottom: 10px; font-size: 13.5px;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="restore_mode" value="select" id="mode_select" checked onchange="toggleRestoreMode()" style="accent-color: #0d9488;">
                            <span>เลือกจากประวัติสำรองในระบบ</span>
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <input type="radio" name="restore_mode" value="upload" id="mode_upload" onchange="toggleRestoreMode()" style="accent-color: #0d9488;">
                            <span>อัปโหลดไฟล์ .sql จากเครื่อง</span>
                        </label>
                    </div>

                    <!-- Select Backup File with Enhanced Scroll Dropdown -->
                    <div id="select_backup_box">
                        <select name="backup_id" id="backup_id" class="form-select" style="font-size: 13px; border-radius: 8px; padding: 9px 12px;">
                            <option value="">-- เลือกไฟล์สำรองข้อมูล ({{ count($allBackups ?? $backups) }} รายการ) --</option>
                            @foreach(($allBackups ?? $backups) as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->filename }} ({{ $b->formatted_size }}) &bull; {{ $b->created_at->format('d/m/Y H:i') }} {{ $b->type === 'pre_restore' ? ' [Snapshot]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Upload File -->
                    <div id="upload_backup_box" style="display: none;">
                        <input type="file" name="backup_file" id="backup_file" class="form-control" accept=".sql,.txt" style="border-radius: 8px; font-size: 13px;">
                        <span class="form-text" style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block;">
                            รองรับไฟล์ .sql ขนาดสูงสุด 100MB
                        </span>
                    </div>
                </div>

                <!-- Optional Pre-restore Auto-Backup Toggle -->
                <div style="margin-bottom: 16px;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border);">
                        <input type="checkbox" name="auto_backup" value="1" {{ $autoBackupEnabled ? 'checked' : '' }} style="accent-color: #0d9488; width: 16px; height: 16px;">
                        <span>สำรองข้อมูลฉุกเฉินอัตโนมัติก่อนกู้คืน (Pre-restore Snapshot)</span>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label required" for="confirm_text" style="color: #b91c1c; font-size: 13px; font-weight: 600;">
                        พิมพ์คำว่า <span style="font-family: monospace; background: #fee2e2; padding: 2px 8px; border-radius: 4px; font-weight: 700;">RESTORE</span> เพื่อยืนยันความปลอดภัย:
                    </label>
                    <input type="text" id="confirm_text" name="confirm_text" class="form-control" placeholder="พิมพ์ RESTORE เป็นตัวพิมพ์ใหญ่" required style="height: 40px; font-weight: 600; font-size: 13.5px; border-radius: 8px; letter-spacing: 0.5px;">
                </div>

                <button type="button" class="btn btn-danger btn-lg" style="width: 100%; font-weight: 600; border-radius: 8px;" onclick="confirmRestoreAction()">
                    <i class="bi bi-cloud-arrow-down"></i>
                    <span>ยืนยันกู้คืนฐานข้อมูล (Restore Now)</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 3. Backup History Table -->
<div class="card" style="border-radius: 14px; border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.03); overflow: hidden;">
    <!-- Modern Card Header with Count & Per Page Quick Switcher -->
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 18px 24px; background: #ffffff; border-bottom: 1px solid var(--border);">
        <div class="card-title" style="margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-clock-history text-secondary" style="font-size: 20px;"></i>
            <span>ประวัติไฟล์สำรองข้อมูล (Backup History)</span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 12px;">
                {{ number_format($backups->total()) }} รายการ
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 12.5px; color: var(--text-muted);">แสดงต่อหน้า:</span>
            <select class="form-select form-select-sm" style="width: auto; min-width: 100px; padding: 5px 12px; font-size: 13px; font-weight: 500; border-radius: 6px; border: 1px solid var(--border); cursor: pointer;" onchange="changePerPage(this.value)">
                <option value="10" {{ request('per_page', $backups->perPage()) == 10 ? 'selected' : '' }}>10 รายการ</option>
                <option value="20" {{ request('per_page', $backups->perPage()) == 20 ? 'selected' : '' }}>20 รายการ</option>
                <option value="50" {{ request('per_page', $backups->perPage()) == 50 ? 'selected' : '' }}>50 รายการ</option>
                <option value="100" {{ request('per_page', $backups->perPage()) == 100 ? 'selected' : '' }}>100 รายการ</option>
            </select>
        </div>
    </div>

    <!-- Table Body -->
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive backup-table-wrap">
            <table class="table backup-table">
                <thead>
                    <tr>
                        <th style="padding-left: 24px;">ชื่อไฟล์สำรอง (.sql)</th>
                        <th>ขนาดไฟล์</th>
                        <th>ประเภท</th>
                        <th>ผู้ดำเนินการ</th>
                        <th>บันทึกช่วยจำ</th>
                        <th>วันเวลาที่สำรอง</th>
                        <th style="text-align: center; padding-right: 24px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                    <tr>
                        <td style="padding-left: 24px; font-family: monospace; font-weight: 600; color: #0f766e;">
                            <i class="bi bi-filetype-sql text-primary" style="font-size: 16px; margin-right: 4px;"></i> {{ $backup->filename }}
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 12px; font-weight: 600;">
                                {{ $backup->formatted_size }}
                            </span>
                        </td>
                        <td>
                            @if($backup->type === 'pre_restore')
                                <span class="badge badge-warning" style="font-size: 11px;">Auto Snapshot</span>
                            @else
                                <span class="badge badge-primary" style="font-size: 11px;">Manual Backup</span>
                            @endif
                        </td>
                        <td style="font-size: 13px;">{{ $backup->creator?->name ?? 'ระบบ' }}</td>
                        <td style="font-size: 12.5px; color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $backup->notes }}">
                            {{ $backup->notes ?? '-' }}
                        </td>
                        <td style="font-size: 12.5px; color: var(--text-muted); white-space: nowrap;">
                            {{ $backup->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="text-align: center; white-space: nowrap; padding-right: 24px;">
                            <div class="action-btn-group">
                                <a href="{{ route('backups.download', $backup) }}" class="btn btn-secondary btn-sm" style="font-size: 12.5px; padding: 5px 10px; border-radius: 6px;" title="ดาวน์โหลดไฟล์ .sql">
                                    <i class="bi bi-download"></i> ดาวน์โหลด
                                </a>

                                <button type="button" class="btn btn-warning btn-sm" style="font-size: 12.5px; padding: 5px 10px; border-radius: 6px;" onclick="triggerRestoreFromList('{{ $backup->id }}', '{{ $backup->filename }}')" title="กู้คืนจากไฟล์นี้">
                                    <i class="bi bi-arrow-repeat"></i> กู้คืน
                                </button>

                                <button type="button" class="btn btn-outline-danger btn-sm" style="font-size: 12.5px; padding: 5px 10px; border-radius: 6px;" onclick="openDeleteBackupModal('{{ $backup->id }}', '{{ $backup->filename }}', '{{ $backup->formatted_size }}', '{{ $backup->created_at->format('d/m/Y H:i:s') }}', '{{ addslashes($backup->creator?->name ?? 'ระบบ') }}', '{{ addslashes($backup->notes ?? '-') }}')" title="ลบไฟล์สำรองนี้">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                            <i class="bi bi-database" style="font-size: 38px; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                            <div style="font-weight: 500; font-size: 14px;">ยังไม่มีประวัติการสำรองฐานข้อมูล</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Thai Pagination & Per Page Navigation Bar -->
        <div style="padding: 16px 24px; border-top: 1px solid var(--border); background: #ffffff;">
            {{ $backups->links() }}
        </div>
    </div>
</div>

<!-- Modern Delete Backup Confirmation Modal -->
<div id="deleteBackupModal" class="custom-modal-backdrop" onclick="handleDeleteModalBackdrop(event)">
    <div class="custom-modal-dialog">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: white;">ยืนยันการลบไฟล์สำรองฐานข้อมูล</h3>
                    <div style="font-size: 12px; color: rgba(255, 255, 255, 0.85); margin-top: 2px;">โปรดยืนยันการลบไฟล์ .sql ออกจากระบบจัดเก็บถาวร</div>
                </div>
            </div>
            <button type="button" onclick="closeDeleteBackupModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1; opacity: 0.8;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">&times;</button>
        </div>

        <!-- Modal Body & Form -->
        <form id="deleteBackupForm" method="POST" action="" style="padding: 24px;">
            @csrf
            @method('DELETE')

            <!-- Backup Details Card -->
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">ไฟล์สำรองที่ต้องการลบ</span>
                    <span id="modal_del_size" class="badge" style="background: #e2e8f0; color: #334155; font-size: 11.5px; font-weight: 600; padding: 2px 8px; border-radius: 6px;"></span>
                </div>

                <div style="font-family: monospace; font-size: 14px; font-weight: 700; color: #b91c1c; word-break: break-all; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-filetype-sql" style="font-size: 18px; color: #dc2626;"></i>
                    <span id="modal_del_filename"></span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12.5px; color: #64748b; padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                    <div><strong>วันที่สำรอง:</strong> <span id="modal_del_date"></span></div>
                    <div><strong>ผู้ดำเนินการ:</strong> <span id="modal_del_creator"></span></div>
                </div>
                <div id="modal_del_notes_wrap" style="font-size: 12px; color: #64748b; margin-top: 6px;">
                    <strong>บันทึกช่วยจำ:</strong> <span id="modal_del_notes"></span>
                </div>
            </div>

            <!-- Caution Warning Box -->
            <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626; font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                <div style="font-size: 12.5px; color: #991b1b; line-height: 1.6;">
                    <strong>คำเตือนความปลอดภัย:</strong> การลบไฟล์นี้จะเป็นการลบไฟล์ <code>.sql</code> ออกจากพื้นที่จัดเก็บของเซิร์ฟเวอร์อย่างถาวร และจะไม่สามารถกู้คืน (Restore) จากจุดนี้ได้อีก
                    <div style="margin-top: 6px; font-size: 11.5px; color: #047857; display: flex; align-items: center; gap: 4px;">
                        <i class="bi bi-shield-check"></i> กิจกรรมการลบนี้จะถูกบันทึกประวัติลงใน Audit Log โดยอัตโนมัติ
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteBackupModal()" style="padding: 9px 20px; font-size: 13.5px; font-weight: 500;">
                    ยกเลิก
                </button>
                <button type="submit" id="btnConfirmDeleteBackup" class="btn btn-danger" style="padding: 9px 22px; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
                    <i class="bi bi-trash3-fill"></i>
                    <span>ยืนยันลบไฟล์ถาวร</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleRestoreMode() {
        const isSelect = document.getElementById('mode_select').checked;
        document.getElementById('select_backup_box').style.display = isSelect ? 'block' : 'none';
        document.getElementById('upload_backup_box').style.display = isSelect ? 'none' : 'block';
    }

    function triggerRestoreFromList(id, filename) {
        document.getElementById('mode_select').checked = true;
        toggleRestoreMode();
        document.getElementById('backup_id').value = id;
        document.getElementById('confirm_text').focus();

        Swal.fire({
            title: 'เลือกไฟล์กู้คืนแล้ว',
            text: 'ระบบได้เลือกไฟล์ ' + filename + ' เรียบร้อยแล้ว กรุณาพิมพ์คำว่า RESTORE ในช่องยืนยันด้านบน แล้วกดปุ่มยืนยันกู้คืนฐานข้อมูล',
            icon: 'info',
            confirmButtonColor: '#0d9488',
        });
    }

    function confirmRestoreAction() {
        const confirmVal = document.getElementById('confirm_text').value.trim();
        if (confirmVal !== 'RESTORE') {
            Swal.fire({
                title: 'ข้อผิดพลาด',
                text: 'กรุณาพิมพ์คำว่า RESTORE เป็นตัวพิมพ์ใหญ่เพื่อยืนยันความปลอดภัย',
                icon: 'error',
                confirmButtonColor: '#ef4444',
            });
            return;
        }

        Swal.fire({
            title: 'ยืนยันการกู้คืนฐานข้อมูล?',
            text: 'ข้อมูลเดิมจะถูกเขียนทับด้วยข้อมูลในไฟล์สำรอง (ระบบจะทำ Auto-Snapshot ก่อนเริ่มกู้คืน)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'ดำเนินการกู้คืนทันที',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restoreForm').submit();
            }
        });
    }

    // Modern Delete Confirmation Modal Handlers
    function openDeleteBackupModal(id, filename, size, date, creator, notes) {
        const form = document.getElementById('deleteBackupForm');
        form.action = `{{ url('/backups') }}/${id}`;

        document.getElementById('modal_del_filename').textContent = filename;
        document.getElementById('modal_del_size').textContent = size;
        document.getElementById('modal_del_date').textContent = date;
        document.getElementById('modal_del_creator').textContent = creator;
        document.getElementById('modal_del_notes').textContent = notes;

        const modal = document.getElementById('deleteBackupModal');
        modal.classList.add('active');
    }

    function closeDeleteBackupModal() {
        const modal = document.getElementById('deleteBackupModal');
        modal.classList.remove('active');
    }

    function handleDeleteModalBackdrop(event) {
        if (event.target.id === 'deleteBackupModal') {
            closeDeleteBackupModal();
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteBackupModal();
        }
    });
</script>
@endpush
