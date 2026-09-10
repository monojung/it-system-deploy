@extends('layouts.app')

@section('title', 'เพิ่มผู้ใช้งานใหม่')
@section('page_title', 'สร้างบัญชีผู้ใช้งานใหม่')
@section('page_subtitle', 'เพิ่มผู้ใช้งาน เชื่อมโยงบัญชี Google, Thai ID และกำหนดสิทธิ์ Google Authenticator MFA')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-person-plus text-primary" style="font-size: 20px;"></i>
                <span>แบบฟอร์มลงทะเบียนผู้ใช้งานใหม่</span>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="card-body" style="padding: 28px;">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <!-- Section 1: ข้อมูลบัญชีผู้ใช้งาน -->
                <h4 style="font-size: 15px; font-weight: 700; color: #0f766e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-person-badge"></i> ข้อมูลบัญชีผู้ใช้งานและสิทธิ์
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="name">ชื่อ - นามสกุล</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น นายสมชาย ใจดี" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="username">ชื่อผู้ใช้งาน (Username สำหรับ Login)</label>
                        <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" placeholder="เช่น somchai_j" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="email">อีเมลผู้ใช้งาน (สำหรับส่งลิงก์และ Login)</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="เช่น user@thchospital.go.th" required>
                        <span class="form-text" style="font-size: 11px; color: #0d9488;">
                            * ระบบจะใช้ส่งลิงก์ยืนยันและตั้งรหัสผ่านครั้งแรกไปยังอีเมลนี้
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="role">ระดับสิทธิ์การใช้งาน (Role)</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>🏥 บุคลากร รพ. / ผู้ใช้งานทั่วไป (User)</option>
                            <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>🛠️ เจ้าหน้าที่ IT / ช่างซ่อม (Technician)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👑 ผู้ดูแลระบบสารสนเทศ (Admin)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="department_id">แผนก / สังกัด</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">-- ไม่ระบุ / ส่วนกลาง --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="position">ตำแหน่งงาน</label>
                        <input type="text" id="position" name="position" class="form-control" value="{{ old('position') }}" placeholder="เช่น พยาบาลวิชาชีพ, เจ้าพนักงานเวชระเบียน">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="phone">เบอร์ติดต่อ / เบอร์ภายใน</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="เช่น 081-xxxxxxx หรือ เบอร์ต่อ 112" style="max-width: 400px;">
                </div>

                <!-- Password Setup Mode Selection -->
                <div style="background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 14px; padding: 18px 20px; margin-bottom: 24px;">
                    <label class="form-label" style="color: #0f766e; font-size: 14.5px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-key-fill" style="font-size: 18px;"></i>
                        <span>วิธีการกำหนดรหัสผ่านเข้าใช้งาน (Password Setup)</span>
                    </label>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; background: white; padding: 12px 14px; border-radius: 10px; border: 1px solid #cbd5e1;">
                            <input type="radio" name="password_mode" value="email_link" {{ old('password_mode', 'email_link') === 'email_link' ? 'checked' : '' }} onchange="togglePasswordMode(this.value)" style="margin-top: 3px; accent-color: #0d9488;">
                            <div>
                                <strong style="color: #0f172a; font-size: 13.5px;">✉️ ส่งลิงก์ให้ผู้ใช้งานตั้งรหัสผ่านครั้งแรกผ่านอีเมล (แนะนำ)</strong>
                                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">
                                    แอดมินไม่ต้องคิดรหัสผ่าน ระบบจะส่งลิงก์ความปลอดภัยสูงไปยังอีเมลของผู้ใช้โดยตรง และหลังบันทึกแอดมินสามารถคัดลอกลิงก์ส่งทาง LINE ให้บุคลากรได้ทันที
                                </p>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; background: white; padding: 12px 14px; border-radius: 10px; border: 1px solid #cbd5e1;">
                            <input type="radio" name="password_mode" value="manual" {{ old('password_mode') === 'manual' ? 'checked' : '' }} onchange="togglePasswordMode(this.value)" style="margin-top: 3px; accent-color: #0d9488;">
                            <div>
                                <strong style="color: #0f172a; font-size: 13.5px;">🔑 กำหนดรหัสผ่านเริ่มต้นทันทีโดยแอดมิน</strong>
                                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">
                                    แอดมินเป็นผู้ระบุรหัสผ่านเริ่มต้นให้ผู้ใช้งานด้วยตนเอง
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Manual Password Input (Hidden by default) -->
                    <div id="manualPasswordBox" style="margin-top: 14px; display: {{ old('password_mode') === 'manual' ? 'block' : 'none' }};">
                        <label class="form-label required" for="password">ระบุรหัสผ่านเริ่มต้น</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" style="max-width: 400px;">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border); margin: 24px 0;">

                <!-- Section 2: สถาปัตยกรรมอัตลักษณ์ดิจิทัล (Digital Identity Linkage) -->
                <h4 style="font-size: 15px; font-weight: 700; color: #0284c7; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-diagram-3-fill" style="font-size: 18px;"></i> สถาปัตยกรรมอัตลักษณ์ดิจิทัล (ThaiD ➔ Google ➔ Hospital IT)
                </h4>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                    <div class="form-row">
                        <!-- Thai ID (CID) -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="cid" style="display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-person-vcard text-primary"></i>
                                <span>1. เลขประจำตัวประชาชน 13 หลัก (Thai ID)</span>
                            </label>
                            <input type="text" id="cid" name="cid" class="form-control" value="{{ old('cid') }}" placeholder="เลขบัตร ปชช. 13 หลัก" maxlength="17" style="font-family: monospace; font-size: 14px; letter-spacing: 1px;" oninput="validateCreateCid(this);">
                            <div id="createCidMsg" style="font-size: 11px; margin-top: 4px; color: #64748b;">
                                ใช้สำหรับผูกกับแอป ThaID กรมการปกครอง เพื่อเชื่อมโยงเข้าหาบัญชี Google
                            </div>
                        </div>

                        <!-- Google Email -->
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="google_email" style="display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-google text-danger"></i>
                                <span>2. อีเมล Google (Google Account)</span>
                            </label>
                            <input type="email" id="google_email" name="google_email" class="form-control" value="{{ old('google_email') }}" placeholder="เช่น somchai@thchospital.go.th">
                            <span class="form-text" style="font-size: 11px;">ใช้ล็อกอินผ่าน Google One-Tap / SSO</span>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border); margin: 24px 0;">

                <!-- Section 3: ระบบความปลอดภัย MFA (Google Authenticator) -->
                <h4 style="font-size: 15px; font-weight: 700; color: #16a34a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-shield-lock-fill"></i> ระบบความปลอดภัย Google Authenticator (MFA)
                </h4>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 22px; margin-bottom: 24px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
                        <div style="background: white; padding: 14px 18px; border-radius: 10px; border: 1px solid #dcfce7; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-weight: 600; color: #14532d; font-size: 14px;">เปิดใช้งาน MFA สำหรับบัญชีนี้</div>
                                <div style="font-size: 12px; color: #64748b;">ต้องกรอกรหัสจาก Google Authenticator เมื่อล็อกอิน</div>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
                                <input type="checkbox" name="mfa_enabled" value="1" {{ old('mfa_enabled') ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px;" class="toggle-slider"></span>
                            </label>
                        </div>

                        <div style="background: white; padding: 14px 18px; border-radius: 10px; border: 1px solid #fef3c7; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-weight: 600; color: #92400e; font-size: 14px;">บังคับใช้ MFA (Admin Enforced)</div>
                                <div style="font-size: 12px; color: #64748b;">บังคับต้องผ่าน Google Authenticator เสมอ</div>
                            </div>
                            <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
                                <input type="checkbox" name="mfa_enforced" value="1" {{ old('mfa_enforced') ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px;" class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Pre-generated Key & QR Code Preview -->
                    <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                        <div style="width: 140px; height: 140px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 6px; background: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img src="{{ $qrCodeUrl }}" alt="Google Authenticator QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>

                        <div style="flex: 1; min-width: 260px;">
                            <div style="font-size: 13px; font-weight: 600; color: #0f766e; margin-bottom: 4px;">
                                <i class="bi bi-qr-code-scan"></i> ตัวอย่าง QR Code เริ่มต้นสำหรับผู้ใช้งาน
                            </div>
                            <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 10px;">
                                ผู้ดูแลระบบสามารถให้ผู้ใช้งานสแกน QR Code นี้ล่วงหน้าด้วย Google Authenticator หรือผู้ใช้สามารถเปิดดูได้ในหน้าโปรไฟล์
                            </p>

                            <label class="form-label" style="font-size: 11px; margin-bottom: 2px;">รหัสคีย์ลับเริ่มต้น (Base32 Secret Key):</label>
                            <input type="text" name="mfa_secret" class="form-control" value="{{ $mfaSecret }}" style="font-family: monospace; font-weight: 700; font-size: 14px; letter-spacing: 2px; color: #0f766e; background: #f8fafc;" readonly>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-check-fill"></i>
                        <span>สร้างบัญชีผู้ใช้งาน</span>
                    </button>
                </div>
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
    function validateCreateCid(el) {
        const digits = el.value.replace(/[^0-9]/g, '');
        const msg = document.getElementById('createCidMsg');
        if (!msg) return;

        if (digits.length === 0) {
            msg.innerHTML = 'ใช้สำหรับผูกกับแอป ThaID กรมการปกครอง เพื่อเชื่อมโยงเข้าหาบัญชี Google';
            msg.style.color = '#64748b';
            return;
        }

        if (digits.length !== 13) {
            msg.innerHTML = `<span style="color: #dc2626;"><i class="bi bi-x-circle"></i> ต้องกรอกให้ครบ 13 หลัก (ปัจจุบัน ${digits.length} หลัก)</span>`;
            return;
        }

        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(digits[i]) * (13 - i);
        }
        const check = (11 - (sum % 11)) % 10;

        if (check === parseInt(digits[12])) {
            msg.innerHTML = '<span style="color: #16a34a;"><i class="bi bi-check-circle-fill"></i> เลขประจำตัวประชาชน 13 หลักถูกต้องตามสูตรคำนวณ DOPA</span>';
        } else {
            msg.innerHTML = '<span style="color: #dc2626;"><i class="bi bi-exclamation-triangle-fill"></i> เลขประจำตัวประชาชนไม่ถูกต้อง (Checksum ไม่ตรง)</span>';
        }
    }

    function togglePasswordMode(mode) {
        const box = document.getElementById('manualPasswordBox');
        const pwdInput = document.getElementById('password');
        if (mode === 'manual') {
            box.style.display = 'block';
            pwdInput.setAttribute('required', 'required');
            pwdInput.focus();
        } else {
            box.style.display = 'none';
            pwdInput.removeAttribute('required');
            pwdInput.value = '';
        }
    }
</script>
@endsection
