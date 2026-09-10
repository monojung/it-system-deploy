@extends('layouts.app')

@section('title', 'แก้ไขผู้ใช้งาน ' . $user->name)
@section('page_title', 'แก้ไขข้อมูลและสิทธิ์ผู้ใช้งาน: ' . $user->name)
@section('page_subtitle', 'กำหนดสิทธิ์ บริหารจัดการอัตลักษณ์ดิจิทัล ThaiD & Google และควบคุมนโยบายความปลอดภัย Google Authenticator MFA')

@section('content')
<div style="max-width: 920px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    <!-- User Identity Hero Header Banner -->
    <div class="card" style="background: linear-gradient(135deg, #064e3b 0%, #0f766e 60%, #0284c7 100%); color: white; border: none; overflow: hidden; position: relative;">
        <div class="card-body" style="padding: 24px 28px; position: relative; z-index: 1;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 18px;">
                    <div style="width: 72px; height: 72px; border-radius: 50%; background: white; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; overflow: hidden; border: 3px solid rgba(255, 255, 255, 0.4); flex-shrink: 0;">
                        @if($user->avatar)
                            @if(str_starts_with($user->avatar, 'http'))
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @elseif(file_exists(public_path($user->avatar)))
                                <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                            @endif
                        @else
                            {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                        @endif
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h2 style="font-size: 20px; font-weight: 700; margin: 0; color: white;">{{ $user->name }}</h2>
                            <span style="font-size: 12px; background: rgba(255, 255, 255, 0.2); padding: 3px 8px; border-radius: 9999px;">
                                @ {{ $user->username }}
                            </span>
                            <span class="badge {{ $user->role_badge }}" style="font-size: 11px;">
                                {{ $user->role_name }}
                            </span>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">
                            <i class="bi bi-hospital"></i> {{ $user->department?->name ?? 'ไม่ระบุแผนก' }}
                            @if($user->position) | <i class="bi bi-briefcase"></i> {{ $user->position }} @endif
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-arrow-left"></i> กลับหน้ารายชื่อ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Edit Form Card -->
    <div class="card">
        <div class="card-body" style="padding: 28px;">
            <form action="{{ route('users.update', $user) }}" method="POST" id="mainUserForm">
                @csrf
                @method('PUT')

                <!-- Section 1: ข้อมูลบัญชีผู้ใช้งานและสิทธิ์ -->
                <h4 style="font-size: 15px; font-weight: 700; color: #0f766e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-person-badge"></i> ข้อมูลบัญชีผู้ใช้งานและสิทธิ์การเข้าถึง
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="name">ชื่อ - นามสกุล</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="username">ชื่อผู้ใช้งาน (Username สำหรับ Login)</label>
                        <input type="text" id="username" name="username" class="form-control" value="{{ old('username', $user->username) }}" required style="font-family: monospace; font-weight: 600;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="role">ระดับสิทธิ์การใช้งาน (Role)</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>🏥 บุคลากร รพ. / ผู้ใช้งานทั่วไป (User)</option>
                            <option value="technician" {{ old('role', $user->role) == 'technician' ? 'selected' : '' }}>🛠️ เจ้าหน้าที่ IT / ช่างซ่อม (Technician)</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบสารสนเทศ (Admin)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">รหัสผ่านใหม่ (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="•••••••• (เว้นว่างเพื่อคงรหัสเดิม)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="department_id">แผนก / สังกัด</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">-- ไม่ระบุ / ส่วนกลาง --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="position">ตำแหน่งงาน</label>
                        <input type="text" id="position" name="position" class="form-control" value="{{ old('position', $user->position) }}" placeholder="เช่น พยาบาลวิชาชีพชำนาญการ, เจ้าพนักงานเวชสถิติ">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="email">อีเมลระบบ</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">เบอร์ติดต่อ / เบอร์ภายใน</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="เช่น ต่อ 101 หรือ 081-xxxxxxx">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="is_active">สถานะการเข้าใช้งานระบบ</label>
                    <select name="is_active" id="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>🟢 เปิดใช้งานปกติ (Active)</option>
                        <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>🔴 ระงับการใช้งาน (Suspended)</option>
                    </select>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border); margin: 24px 0;">

                <!-- Section 2: สถาปัตยกรรมอัตลักษณ์ดิจิทัล (Digital Identity Linkage) -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: #0284c7; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-diagram-3-fill" style="font-size: 18px;"></i> สถาปัตยกรรมอัตลักษณ์ดิจิทัล (ThaiD ➔ Google ➔ IT System)
                    </h4>
                    @if($user->hasGoogleLinked() && $user->hasThaidLinked())
                        <span class="badge badge-success" style="font-size: 11px;">✨ เชื่อมโยงคู่สมบูรณ์</span>
                    @endif
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                        <!-- Thai ID Sub-Card -->
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <label class="form-label" for="cid" style="display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 700; color: #1e3a8a;">
                                    <i class="bi bi-person-vcard text-primary"></i> 1. บัตรประชาชนดิจิทัล (ThaiD)
                                </label>
                                @if($user->hasThaidLinked())
                                    <span class="badge badge-success" style="font-size: 10px;">ยืนยันแล้ว</span>
                                @else
                                    <span class="badge badge-warning" style="font-size: 10px;">ยังไม่ระบุ</span>
                                @endif
                            </div>

                            <div class="form-group" style="margin-bottom: 8px;">
                                <input type="text" id="cid" name="cid" class="form-control" value="{{ old('cid', $user->cid) }}" placeholder="เลขบัตร ปชช. 13 หลัก" maxlength="17" style="font-family: monospace; font-size: 14px; font-weight: 600; letter-spacing: 1px;" oninput="validateCidInput(this);">
                                <div id="cidValidationMsg" style="font-size: 11px; margin-top: 4px; color: #64748b;">
                                    ใช้สำหรับยืนยันตัวตนระดับชาติตามมาตรฐาน DOPA
                                </div>
                            </div>

                            @if($user->hasThaidLinked())
                                <button type="button" class="btn btn-outline-danger btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="if(confirm('ยืนยันล้างเลขบัตรประชาชนสำหรับผู้ใช้นี้?')) { document.getElementById('unlinkThaidForm').submit(); }">
                                    <i class="bi bi-x-circle"></i> ล้างการเชื่อมโยง ThaiD
                                </button>
                            @endif
                        </div>

                        <!-- Google Account Sub-Card -->
                        <div style="background: white; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <label class="form-label" for="google_email" style="display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 700; color: #991b1b;">
                                    <i class="bi bi-google text-danger"></i> 2. บัญชี Google (Google Account)
                                </label>
                                @if($user->hasGoogleLinked())
                                    <span class="badge badge-success" style="font-size: 10px;">เชื่อมต่อแล้ว</span>
                                @else
                                    <span class="badge badge-light" style="font-size: 10px; color: #64748b;">ยังไม่เชื่อมต่อ</span>
                                @endif
                            </div>

                            <div class="form-group" style="margin-bottom: 8px;">
                                <input type="email" id="google_email" name="google_email" class="form-control" value="{{ old('google_email', $user->google_email) }}" placeholder="เช่น user@thchospital.go.th">
                                <span class="form-text" style="font-size: 11px;">ใช้สำหรับเข้าสู่ระบบด้วย Google One-Tap / SSO</span>
                            </div>

                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="form-label" for="google_id" style="font-size: 11px; color: #64748b; margin-bottom: 2px;">Google User ID (Sub Key):</label>
                                <input type="text" id="google_id" name="google_id" class="form-control form-control-sm" value="{{ old('google_id', $user->google_id) }}" placeholder="สร้างอัตโนมัติเมื่อล็อกอิน Google" style="font-family: monospace; font-size: 11px;">
                            </div>

                            @if($user->hasGoogleLinked())
                                <button type="button" class="btn btn-outline-danger btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="if(confirm('ยืนยันตัดการเชื่อมต่อบัญชี Google ของผู้ใช้นี้?')) { document.getElementById('unlinkGoogleForm').submit(); }">
                                    <i class="bi bi-link-45deg"></i> ตัดการเชื่อมโยง Google
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border); margin: 24px 0;">

                <!-- Section 3: ระบบความปลอดภัย MFA (Google Authenticator) -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: #16a34a; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-shield-lock-fill"></i> 3. ศูนย์ควบคุมความปลอดภัย Google Authenticator (MFA)
                    </h4>
                    @if($user->mfa_enforced)
                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fcd34d;">🟡 บังคับใช้โดย Admin</span>
                    @elseif($user->mfa_enabled)
                        <span class="badge badge-success">🟢 เปิดใช้งานแล้ว</span>
                    @else
                        <span class="badge badge-light" style="border: 1px solid #cbd5e1; color: #64748b;">⚪ ปิดใช้งาน</span>
                    @endif
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 22px; margin-bottom: 24px;">
                    <!-- MFA Switches (Enforce vs Self-Service relation) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
                        <!-- Switch 1: MFA Enabled -->
                        <div style="background: white; padding: 14px 18px; border-radius: 10px; border: 1px solid #dcfce7; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-weight: 600; color: #14532d; font-size: 14px;">เปิดใช้งาน MFA 2-Factor</div>
                                <div style="font-size: 11px; color: #64748b;">ต้องกรอกรหัสจาก Google Authenticator เมื่อล็อกอิน</div>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
                                <input type="checkbox" name="mfa_enabled" value="1" {{ old('mfa_enabled', $user->mfa_enabled) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" id="mfaToggleCheck">
                                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px;" class="toggle-slider"></span>
                            </label>
                        </div>

                        <!-- Switch 2: MFA Enforced (Admin Policy) -->
                        <div style="background: white; padding: 14px 18px; border-radius: 10px; border: 1px solid #fef3c7; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-weight: 600; color: #92400e; font-size: 14px;">บังคับใช้ MFA (Admin Enforced)</div>
                                <div style="font-size: 11px; color: #64748b;">
                                    <strong>หากเปิด:</strong> ผู้ใช้จะไม่สามารถกดปิดในหน้า Profile ของตนเองได้
                                </div>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
                                <input type="checkbox" name="mfa_enforced" value="1" {{ old('mfa_enforced', $user->mfa_enforced) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" id="mfaEnforceCheck">
                                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px;" class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Secret Key & QR Code Card -->
                    <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                        <div style="width: 140px; height: 140px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 6px; background: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img src="{{ $qrCodeUrl }}" alt="Google Authenticator QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>

                        <div style="flex: 1; min-width: 260px;">
                            <div style="font-size: 13px; font-weight: 600; color: #0f766e; margin-bottom: 4px;">
                                <i class="bi bi-qr-code-scan"></i> QR Code และ Secret Key สำหรับตั้งค่า Google Authenticator
                            </div>
                            <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 12px;">
                                แอดมินสามารถช่วยบอกรหัสคีย์ลับนี้ให้ผู้ใช้ทางโทรศัพท์ หรือแสดง QR Code ให้ผู้ใช้สแกนผ่านแอปในกรณีติดตั้งใหม่
                            </p>

                            <div style="margin-bottom: 8px;">
                                <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">รหัสคีย์ลับ (Base32 Secret Key):</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" name="mfa_secret" id="mfaSecretInput" class="form-control" value="{{ $user->mfa_secret }}" style="font-family: monospace; font-weight: 700; font-size: 14px; letter-spacing: 2px; color: #0f766e; background: #f8fafc;" readonly>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="copySecretKey()" title="คัดลอกคีย์ลับ">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            @if($user->mfa_enrolled_at)
                                <div style="font-size: 11px; color: #059669;">
                                    <i class="bi bi-check-circle-fill"></i> ผูกรหัสคีย์ลับเมื่อ: {{ thai_date($user->mfa_enrolled_at, 'full') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm text-danger" onclick="if(confirm('ยืนยันสร้างรหัส Google Authenticator ใหม่? (ผู้ใช้จะต้องสแกน QR Code ใหม่เพื่อเข้าสู่ระบบ)')) { document.getElementById('resetMfaForm').submit(); }">
                            <i class="bi bi-arrow-repeat"></i> รีเซ็ต MFA Secret Key
                        </button>

                        @if($user->isMfaActive())
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('ยืนยันปลดล็อกฉุกเฉิน (ปิดการใช้งาน MFA ทันที)?')) { document.getElementById('emergencyUnlockForm').submit(); }">
                                <i class="bi bi-shield-x"></i> ปลดล็อกฉุกเฉิน (ปิด MFA)
                            </button>
                        @endif
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check2-circle"></i>
                            <span>บันทึกการเปลี่ยนแปลง</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Separate Form for Reset MFA -->
            <form id="resetMfaForm" action="{{ route('users.reset-mfa', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Separate Form for Emergency Unlock (Toggle Off) -->
            <form id="emergencyUnlockForm" action="{{ route('users.toggle-mfa', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Separate Form for Unlink Google -->
            <form id="unlinkGoogleForm" action="{{ route('users.unlink-google', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Separate Form for Unlink ThaiD -->
            <form id="unlinkThaidForm" action="{{ route('users.unlink-thaid', $user) }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</div>

<style>
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #10b981 !important;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }
</style>

<script>
    function copySecretKey() {
        const input = document.getElementById('mfaSecretInput');
        input.select();
        navigator.clipboard.writeText(input.value);
        alert('คัดลอก Base32 Secret Key เรียบร้อยแล้ว: ' + input.value);
    }

    function validateCidInput(el) {
        const digits = el.value.replace(/[^0-9]/g, '');
        const msg = document.getElementById('cidValidationMsg');
        if (!msg) return;

        if (digits.length === 0) {
            msg.innerHTML = 'ใช้สำหรับยืนยันตัวตนระดับชาติตามมาตรฐาน DOPA';
            msg.style.color = '#64748b';
            return;
        }

        if (digits.length !== 13) {
            msg.innerHTML = `<span style="color: #dc2626;"><i class="bi bi-x-circle"></i> ต้องกรอกให้ครบ 13 หลัก (ปัจจุบัน ${digits.length} หลัก)</span>`;
            return;
        }

        // Checksum Modulo 11
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(digits[i]) * (13 - i);
        }
        const check = (11 - (sum % 11)) % 10;

        if (check === parseInt(digits[12])) {
            msg.innerHTML = '<span style="color: #16a34a;"><i class="bi bi-check-circle-fill"></i> เลขประจำตัวประชาชน 13 หลักถูกต้องตามสูตรคำนวณ Checksum DOPA</span>';
        } else {
            msg.innerHTML = '<span style="color: #dc2626;"><i class="bi bi-exclamation-triangle-fill"></i> เลขประจำตัวประชาชนไม่ถูกต้อง (Checksum ไม่ตรง)</span>';
        }
    }
</script>
@endsection
