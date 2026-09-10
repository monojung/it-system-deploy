@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งานและสิทธิ์การเข้าถึง')
@section('page_title', 'จัดการผู้ใช้งาน สิทธิ์ และอัตลักษณ์ดิจิทัล')
@section('page_subtitle', 'บริหารจัดการบัญชีผู้ใช้งาน นโยบายความปลอดภัย Google Authenticator MFA และการเชื่อมโยง ThaiD & Google')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #0d9488;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;">ผู้ใช้งานทั้งหมด</div>
                <div style="font-size: 22px; font-weight: 700; color: var(--text-main);">{{ $metrics['total'] ?? $users->total() }} <span style="font-size: 13px; font-weight: 400; color: #64748b;">บัญชี</span></div>
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #ea4335;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="bi bi-google"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;">เชื่อมต่อ Google Account</div>
                <div style="font-size: 22px; font-weight: 700; color: #dc2626;">{{ $metrics['google_linked'] ?? 0 }} <span style="font-size: 13px; font-weight: 400; color: #64748b;">บัญชี</span></div>
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #0284c7;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="bi bi-person-vcard-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;">เชื่อมต่อ Thai ID (บัตร ปชช.)</div>
                <div style="font-size: 22px; font-weight: 700; color: #0284c7;">{{ $metrics['thaid_linked'] ?? 0 }} <span style="font-size: 13px; font-weight: 400; color: #64748b;">บัญชี</span></div>
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-left: 4px solid #16a34a;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;">เปิดใช้งาน MFA 2-Factor</div>
                <div style="font-size: 22px; font-weight: 700; color: #16a34a;">{{ $metrics['mfa_active'] ?? 0 }} <span style="font-size: 13px; font-weight: 400; color: #64748b;">บัญชี</span></div>
            </div>
        </div>
    </div>

    <!-- Identity Federation Banner for Admin Context -->
    <div style="background: linear-gradient(90deg, #eff6ff 0%, #f0fdf4 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 13px; color: #1e3a8a;">
                    สถาปัตยกรรมอัตลักษณ์ดิจิทัลสัมพันธ์: [ ThaiD ➔ Google Account ➔ Hospital IT System ]
                </div>
                <div style="font-size: 12px; color: #475569;">
                    แอดมินสามารถกำหนด <strong>"บังคับใช้ MFA (Enforce)"</strong> เพื่อความปลอดภัย หรืออนุญาตให้ผู้ใช้ควบคุมเปิด/ปิดเองในหน้า Profile ได้
                </div>
            </div>
        </div>
        <div style="font-size: 12px; font-weight: 600; color: #0f766e; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-shield-shaded"></i> ระบบสัมพันธ์กับฝั่งผู้ใช้งาน 100%
        </div>
    </div>

    @if(session('setup_link'))
    <!-- Password Setup Link Highlight Banner -->
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%); border: 2px solid #86efac; border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.15);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: 10px; background: #0d9488; color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-key-fill"></i>
                </div>
                <div>
                    <strong style="color: #0f766e; font-size: 14px;">สร้างลิงก์ตั้งรหัสผ่านสำหรับคุณ {{ session('setup_user_name') }} ({{ session('setup_user_email') }}) สำเร็จ!</strong>
                    <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                        ท่านสามารถคลิกคัดลอกลิงก์ด้านล่างเพื่อส่งให้บุคลากรทาง LINE หรือช่องทางสื่อสารอื่นได้ทันที:
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; width: 100%; max-width: 520px;">
                <input type="text" id="setupLinkInput" value="{{ session('setup_link') }}" readonly style="flex: 1; padding: 8px 12px; font-size: 12px; border-radius: 8px; border: 1px solid #99f6e4; background: white; font-family: monospace; color: #0f172a;">
                <button type="button" onclick="copySetupLink()" class="btn btn-primary btn-sm" style="white-space: nowrap; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-clipboard-check"></i> คัดลอกลิงก์
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Table Card -->
    <div class="card">
        <div class="card-header" style="flex-wrap: wrap; gap: 14px;">
            <div class="card-title">
                <i class="bi bi-people text-primary" style="font-size: 20px;"></i>
                <span>รายชื่อผู้ใช้งานและระบบยืนยันตัวตน ({{ $users->total() }} บัญชี)</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>+ เพิ่มผู้ใช้งานใหม่</span>
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid var(--border);">
            <form action="{{ route('users.index') }}" method="GET">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ค้นหาผู้ใช้งาน</label>
                        <input type="text" name="search" class="form-control" placeholder="ชื่อ, Username, เลขบัตร 13 หลัก, อีเมล Google" value="{{ request('search') }}">
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ระดับสิทธิ์ (Role)</label>
                        <select name="role" class="form-select">
                            <option value="">-- ทุกสิทธิ์ --</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบ (Admin)</option>
                            <option value="technician" {{ request('role') == 'technician' ? 'selected' : '' }}>🛠️ เจ้าหน้าที่ IT / ช่าง (Technician)</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>🏥 บุคลากร รพ. (User)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">การเชื่อมโยงบัญชี</label>
                        <select name="linked" class="form-select">
                            <option value="">-- การเชื่อมต่อทั้งหมด --</option>
                            <option value="both" {{ request('linked') == 'both' ? 'selected' : '' }}>✨ เชื่อมทั้ง Google และ Thai ID</option>
                            <option value="google" {{ request('linked') == 'google' ? 'selected' : '' }}>🔴 เชื่อมต่อ Google แล้ว</option>
                            <option value="thaid" {{ request('linked') == 'thaid' ? 'selected' : '' }}>🔵 เชื่อมต่อ Thai ID แล้ว</option>
                            <option value="none" {{ request('linked') == 'none' ? 'selected' : '' }}>⚪ ยังไม่เชื่อมบัญชีใดๆ</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">สถานะ MFA (Google Auth)</label>
                        <select name="mfa" class="form-select">
                            <option value="">-- สถานะ MFA ทั้งหมด --</option>
                            <option value="enabled" {{ request('mfa') == 'enabled' ? 'selected' : '' }}>🟢 เปิดใช้งาน MFA</option>
                            <option value="enforced" {{ request('mfa') == 'enforced' ? 'selected' : '' }}>🟡 บังคับใช้โดย Admin</option>
                            <option value="disabled" {{ request('mfa') == 'disabled' ? 'selected' : '' }}>⚪ ปิดใช้งาน</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">แผนก / สังกัด</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- ทุกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="bi bi-funnel"></i> ค้นหา
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary" title="ล้างการค้นหา">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="min-width: 200px;">ผู้ใช้งาน & บทบาท</th>
                            <th style="min-width: 240px;">บัญชีที่เชื่อมโยง (Google & Thai ID)</th>
                            <th style="min-width: 200px; text-align: center;">Google Authenticator (MFA)</th>
                            <th style="min-width: 140px;">แผนก / ตำแหน่ง</th>
                            <th style="text-align: center; width: 90px;">สถานะ</th>
                            <th style="text-align: center; width: 160px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #0d9488; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; overflow: hidden; border: 2px solid #e2e8f0;">
                                        @if($u->avatar)
                                            @if(str_starts_with($u->avatar, 'http'))
                                                <img src="{{ $u->avatar }}" alt="{{ $u->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @elseif(file_exists(public_path($u->avatar)))
                                                <img src="{{ asset($u->avatar) }}" alt="{{ $u->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                {{ mb_substr($u->name, 0, 1, 'UTF-8') }}
                                            @endif
                                        @else
                                            {{ mb_substr($u->name, 0, 1, 'UTF-8') }}
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-main); line-height: 1.3;">
                                            {{ $u->name }}
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                            <span class="badge {{ $u->role_badge }}" style="font-size: 11px; padding: 2px 6px;">
                                                {{ $u->role_name }}
                                            </span>
                                            <span style="font-family: monospace; font-size: 12px; color: #64748b;">
                                                @ {{ $u->username }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Linked Accounts Column -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <!-- Thai ID Badge -->
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #e0f2fe; color: #0284c7; flex-shrink: 0;">
                                            <i class="bi bi-person-vcard"></i>
                                        </span>
                                        @if($u->cid)
                                            <span style="font-family: monospace; font-weight: 600; color: #0f766e; background: #f0fdf4; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;" title="Thai National ID 13 หลัก">
                                                {{ $u->formatted_cid }}
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-style: italic;">ยังไม่ได้เชื่อม Thai ID</span>
                                        @endif
                                    </div>

                                    <!-- Google Account Badge -->
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #fee2e2; color: #dc2626; flex-shrink: 0;">
                                            <i class="bi bi-google"></i>
                                        </span>
                                        @if($u->google_email || $u->google_id)
                                            <span style="color: #1e293b; font-weight: 500; background: #fef2f2; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecaca;" title="Google ID: {{ $u->google_id ?? '-' }}">
                                                {{ $u->google_email ?? 'เชื่อมต่อ ID แล้ว' }}
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-style: italic;">ยังไม่ได้เชื่อม Google</span>
                                        @endif
                                    </div>

                                    @if($u->cid && ($u->google_email || $u->google_id))
                                        <div style="font-size: 11px; color: #059669; display: flex; align-items: center; gap: 4px; font-weight: 600;">
                                            <i class="bi bi-patch-check-fill"></i> เชื่อมโยงคู่ Google + Thai ID สมบูรณ์
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- MFA / Google Authenticator Column -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                    @if($u->mfa_enforced)
                                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; font-size: 11px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="bi bi-lock-fill"></i> บังคับใช้โดย Admin
                                        </span>
                                    @elseif($u->mfa_enabled)
                                        <span class="badge badge-success" style="font-size: 11px; padding: 3px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="bi bi-shield-check"></i> เปิดใช้งานแล้ว
                                        </span>
                                    @else
                                        <span class="badge badge-light" style="font-size: 11px; padding: 3px 8px; color: #64748b; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #cbd5e1;">
                                            <i class="bi bi-shield-x"></i> ปิดใช้งาน
                                        </span>
                                    @endif

                                    <!-- Quick Actions for MFA -->
                                    <div style="display: flex; gap: 4px; margin-top: 2px;">
                                        <!-- Quick MFA Toggle Form -->
                                        <form action="{{ route('users.toggle-mfa', $u) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm" style="font-size: 11px; padding: 2px 7px; border-radius: 5px; {{ $u->mfa_enabled ? 'background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;' : 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' }}" title="สลับเปิด/ปิด MFA">
                                                <i class="bi {{ $u->mfa_enabled ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                                <span>{{ $u->mfa_enabled ? 'ปิด' : 'เปิด' }}</span>
                                            </button>
                                        </form>

                                        <!-- Quick Enforce Toggle Form -->
                                        <form action="{{ route('users.toggle-enforce-mfa', $u) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm" style="font-size: 11px; padding: 2px 7px; border-radius: 5px; {{ $u->mfa_enforced ? 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;' : 'background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;' }}" title="{{ $u->mfa_enforced ? 'ยกเลิกการบังคับใช้ (ให้ผู้ใช้เปิด/ปิดเองได้)' : 'บังคับใช้ MFA (ไม่ให้ผู้ใช้ปิดเอง)' }}">
                                                <i class="bi {{ $u->mfa_enforced ? 'bi-lock-fill' : 'bi-unlock' }}"></i>
                                                <span>{{ $u->mfa_enforced ? 'บังคับอยู่' : 'ไม่บังคับ' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div style="font-size: 13px; font-weight: 500; color: var(--text-main);">
                                    {{ $u->department?->name ?? 'ส่วนกลาง' }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    {{ $u->position ?? '-' }}
                                </div>
                            </td>

                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    @if($u->is_active)
                                        <span class="badge badge-success" style="font-size: 11px;">เปิดใช้งาน</span>
                                    @else
                                        <span class="badge badge-danger" style="font-size: 11px;">ระงับใช้งาน</span>
                                    @endif

                                    @if(!$u->isEmailVerified() || $u->hasPasswordSetupPending())
                                        <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 10px; padding: 2px 6px;" title="ผู้ใช้ยังไม่ได้ตั้งรหัสผ่านผ่านลิงก์ยืนยัน">
                                            <i class="bi bi-clock-history"></i> รอยืนยันรหัส
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <!-- Identity & Helpdesk Modal Trigger -->
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openIdentityModal({{ $u->id }})" title="ดูข้อมูลความปลอดภัย & QR Code ผู้ใช้งาน" style="padding: 6px 9px;">
                                        <i class="bi bi-shield-lock-fill" style="color: #0f766e;"></i>
                                    </button>

                                    @if(!empty($u->email))
                                    <form action="{{ route('users.send-setup-link', $u) }}" method="POST" style="display: inline;" onsubmit="return confirm('ต้องการสร้างและส่งลิงก์ตั้งรหัสผ่านใหม่ให้คุณ {{ $u->name }} ({{ $u->email }}) ใช่หรือไม่?')">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="สร้าง/ส่งลิงก์ตั้งรหัสผ่านให้ผู้ใช้งาน" style="padding: 6px 9px;">
                                            <i class="bi bi-key-fill" style="color: #d97706;"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('users.toggle', $u) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="{{ $u->is_active ? 'ระงับการใช้งานบัญชี' : 'เปิดใช้งานบัญชี' }}" style="padding: 6px 9px;">
                                            <i class="bi bi-power" style="color: {{ $u->is_active ? '#dc2626' : '#16a34a' }};"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-secondary btn-sm" title="แก้ไขข้อมูล & ตั้งค่า MFA/การเชื่อมโยง" style="padding: 6px 9px;">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันลบผู้ใช้งาน {{ $u->name }} ออกจากระบบ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="ลบผู้ใช้งาน" style="padding: 6px 9px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <div style="font-size: 36px; margin-bottom: 8px; color: #cbd5e1;"><i class="bi bi-person-x"></i></div>
                                <div>ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่ระบุ</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding: 18px 24px; border-top: 1px solid var(--border);">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<!-- IT Helpdesk Identity & Security Modal -->
<div id="identityModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 600px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #064e3b, #0f766e); color: white; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-shield-shaded"></i>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: white;" id="modalUserName">ข้อมูลอัตลักษณ์และความปลอดภัย</h3>
                    <div style="font-size: 12px; opacity: 0.9;" id="modalUserRole">IT Helpdesk Support View</div>
                </div>
            </div>
            <button type="button" onclick="closeIdentityModal()" style="background: transparent; border: none; color: white; font-size: 20px; cursor: pointer;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 22px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px;">
            <!-- Connected Accounts Identity Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-link-45deg"></i> บัญชีที่เชื่อมโยง (ThaiD & Google)
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b;">บัตรประชาชนดิจิทัล (ThaiD CID):</div>
                        <div style="font-family: monospace; font-weight: 700; color: #0f766e;" id="modalUserCid">-</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #64748b;">บัญชี Google (Google Email):</div>
                        <div style="font-weight: 600; color: #1e293b;" id="modalUserGoogle">-</div>
                    </div>
                </div>
            </div>

            <!-- MFA Status & Secret Key -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-shield-lock-fill"></i> สถานะ Google Authenticator
                    </div>
                    <span id="modalMfaBadge" class="badge badge-success">เปิดใช้งาน</span>
                </div>

                <div style="display: flex; gap: 18px; align-items: center; flex-wrap: wrap;">
                    <div style="width: 120px; height: 120px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img id="modalQrCodeImg" src="" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div style="flex: 1; min-width: 220px;">
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">รหัสคีย์ลับ (Base32 Secret Key):</div>
                        <div style="display: flex; gap: 6px; margin-bottom: 8px;">
                            <input type="text" id="modalSecretKey" class="form-control form-control-sm" readonly style="font-family: monospace; font-weight: 700; color: #0f766e; background: white;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('modalSecretKey').value); alert('คัดลอกคีย์เรียบร้อยแล้ว');">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div style="font-size: 11px; color: #475569;" id="modalMfaEnrolledAt">
                            <i class="bi bi-clock-history"></i> วันที่เปิดใช้งาน: -
                        </div>
                    </div>
                </div>
            </div>

            <!-- Helpdesk Fast Actions -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                <form id="modalResetMfaForm" action="" method="POST" onsubmit="return confirm('ยืนยันสร้างรหัส Google Authenticator ใหม่ให้ผู้ใช้? (ผู้ใช้ต้องสแกนใหม่)');">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> รีเซ็ต Secret Key
                    </button>
                </form>

                <form id="modalToggleMfaForm" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm" id="modalToggleMfaBtn" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;">
                        <i class="bi bi-power"></i> ปลดล็อกฉุกเฉิน (ปิด MFA)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
async function openIdentityModal(userId) {
    const backdrop = document.getElementById('identityModalBackdrop');
    backdrop.style.display = 'flex';

    try {
        const res = await fetch(`/users/${userId}/identity-details`);
        if (!res.ok) throw new Error('ไม่สามารถดึงข้อมูลได้');
        const data = await res.json();

        document.getElementById('modalUserName').innerText = `${data.name} (@${data.username})`;
        document.getElementById('modalUserRole').innerText = `${data.role_name} | แผนก: ${data.department}`;
        document.getElementById('modalUserCid').innerText = data.formatted_cid || 'ยังไม่ได้เชื่อมต่อ';
        document.getElementById('modalUserGoogle').innerText = data.google_email || 'ยังไม่ได้เชื่อมต่อ';

        const badge = document.getElementById('modalMfaBadge');
        if (data.mfa_enforced) {
            badge.className = 'badge';
            badge.style = 'background: #fef3c7; color: #b45309; border: 1px solid #fcd34d;';
            badge.innerText = '🟡 บังคับใช้โดย Admin';
        } else if (data.mfa_enabled) {
            badge.className = 'badge badge-success';
            badge.style = '';
            badge.innerText = '🟢 เปิดใช้งานแล้ว';
        } else {
            badge.className = 'badge badge-light';
            badge.style = 'border: 1px solid #cbd5e1; color: #64748b;';
            badge.innerText = '⚪ ปิดใช้งาน';
        }

        document.getElementById('modalQrCodeImg').src = data.qr_code_url;
        document.getElementById('modalSecretKey').value = data.mfa_secret;
        document.getElementById('modalMfaEnrolledAt').innerHTML = `<i class="bi bi-clock-history"></i> วันที่เปิดใช้งาน: ${data.mfa_enrolled_at || 'ยังไม่ได้เปิดใช้งาน'}`;

        // Action form targets
        document.getElementById('modalResetMfaForm').action = `/users/${userId}/reset-mfa`;
        document.getElementById('modalToggleMfaForm').action = `/users/${userId}/toggle-mfa`;

        const toggleBtn = document.getElementById('modalToggleMfaBtn');
        if (data.mfa_enabled || data.mfa_enforced) {
            toggleBtn.style = 'background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;';
            toggleBtn.innerHTML = '<i class="bi bi-shield-x"></i> ปลดล็อกฉุกเฉิน (ปิด MFA)';
        } else {
            toggleBtn.style = 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;';
            toggleBtn.innerHTML = '<i class="bi bi-shield-check"></i> สั่งเปิดใช้งาน MFA';
        }
    } catch (e) {
        alert('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + e.message);
        closeIdentityModal();
    }
}

function closeIdentityModal() {
    document.getElementById('identityModalBackdrop').style.display = 'none';
}

function copySetupLink() {
    const input = document.getElementById('setupLinkInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'คัดลอกลิงก์สำเร็จ!',
                    text: 'คุณสามารถส่งลิงก์นี้ให้บุคลากรเพื่อตั้งรหัสผ่านครั้งแรกได้ทันที',
                    timer: 2500,
                    showConfirmButton: false
                });
            } else {
                alert('คัดลอกลิงก์เรียบร้อยแล้ว');
            }
        }).catch(() => {
            document.execCommand('copy');
            alert('คัดลอกลิงก์เรียบร้อยแล้ว');
        });
    } else {
        document.execCommand('copy');
        alert('คัดลอกลิงก์เรียบร้อยแล้ว');
    }
}
</script>
@endsection
