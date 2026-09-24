<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบุข้อมูลและเลือกแผนกสังกัด - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
    <!-- Google Fonts: Prompt & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --primary-border: #99f6e4;
            --secondary: #0284c7;
            --radius-lg: 24px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #042f2e 0%, #0f172a 50%, #083344 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            color: #1e293b;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Highlights */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.3) 0%, transparent 70%);
            top: -150px;
            left: -150px;
            pointer-events: none;
            filter: blur(40px);
        }

        body::after {
            content: '';
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.22) 0%, transparent 70%);
            bottom: -100px;
            right: -100px;
            pointer-events: none;
            filter: blur(40px);
        }

        .onboarding-card {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .card-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 32px 28px 24px;
            text-align: center;
            position: relative;
        }

        .icon-badge {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 14px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header h1 {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .card-header p {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 300;
            line-height: 1.5;
        }

        .card-body {
            padding: 28px;
        }

        /* Linked Account Profile Bar */
        .account-preview {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .avatar-box {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            flex-shrink: 0;
        }

        .avatar-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .account-meta {
            flex-grow: 1;
            overflow: hidden;
        }

        .account-meta .name {
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .account-meta .email {
            font-size: 12.5px;
            color: #64748b;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-sso {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            color: #0f172a;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        .invalid-feedback {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
            display: block;
        }

        .helper-text {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Notice Banner */
        .info-alert {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius-md);
            padding: 12px 14px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 20px;
            font-size: 12.5px;
            color: #1e40af;
            line-height: 1.5;
        }

        .info-alert i {
            font-size: 18px;
            color: #2563eb;
            margin-top: 1px;
            flex-shrink: 0;
        }

        /* Action Buttons */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 10px 20px -5px rgba(13, 148, 136, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 25px -5px rgba(13, 148, 136, 0.5);
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .card-footer {
            padding: 16px 28px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
        }

        .logout-link {
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .logout-link:hover {
            color: #ef4444;
        }

        .security-badge {
            color: #0d9488;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="onboarding-card">
        <!-- Header -->
        <div class="card-header">
            <div class="icon-badge">
                <i class="bi bi-person-vcard"></i>
            </div>
            <h1>ลงทะเบียนยืนยันตัวตนเจ้าหน้าที่</h1>
            <p>กรุณาระบุข้อมูลส่วนบุคคลและเลือกแผนกที่ท่านสังกัด เพื่อจำแนกสิทธิและให้ผู้ดูแลระบบอนุมัติการเข้าใช้งาน</p>
        </div>

        <!-- Body Form -->
        <div class="card-body">
            <!-- Account Profile Bar -->
            <div class="account-preview">
                <div class="avatar-box">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        {{ mb_substr($user->name ?? 'U', 0, 1) }}
                    @endif
                </div>
                <div class="account-meta">
                    <div class="name">{{ $user->name }}</div>
                    <div class="email">{{ $user->email ?: ($user->username . '@hospital') }}</div>
                </div>
                @if($user->google_id)
                    <div class="badge-sso">
                        <i class="bi bi-google"></i> Google SSO
                    </div>
                @elseif($user->cid)
                    <div class="badge-sso">
                        <i class="bi bi-person-badge"></i> ThaID
                    </div>
                @else
                    <div class="badge-sso">
                        <i class="bi bi-shield-check"></i> บัญชีใหม่
                    </div>
                @endif
            </div>

            <!-- Informational Banner -->
            <div class="info-alert">
                <i class="bi bi-shield-lock-fill"></i>
                <div>
                    <strong>เพื่อความปลอดภัยของข้อมูลโรงพยาบาล:</strong><br>
                    หลังส่งข้อมูล บัญชีจะถูกส่งให้ผู้ดูแลระบบ (Admin) ตรวจสอบความถูกต้องว่าท่านเป็นเจ้าหน้าที่ของโรงพยาบาลจริง ก่อนเปิดสิทธิ์เข้าถึงระบบ
                </div>
            </div>

            <form action="{{ route('auth.complete-profile.post') }}" method="POST" id="onboardingForm">
                @csrf

                <!-- First Name & Last Name -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="first_name">
                            ชื่อจริง (ภาษาไทย) <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-control @error('first_name') is-invalid @enderror" 
                                   placeholder="เช่น สมชาย" 
                                   value="{{ old('first_name', $user->first_name ?: explode(' ', $user->name)[0] ?? '') }}" 
                                   required>
                        </div>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">
                            นามสกุล (ภาษาไทย) <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-control @error('last_name') is-invalid @enderror" 
                                   placeholder="เช่น ใจดี" 
                                   value="{{ old('last_name', $user->last_name ?: (explode(' ', $user->name, 2)[1] ?? '')) }}" 
                                   required>
                        </div>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Department Selector -->
                <div class="form-group">
                    <label class="form-label" for="department_id">
                        กลุ่มงาน / ฝ่าย / แผนกที่สังกัด <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="bi bi-building input-icon"></i>
                        <select id="department_id" 
                                name="department_id" 
                                class="form-select @error('department_id') is-invalid @enderror" 
                                required>
                            <option value="">-- กรุณาเลือกกลุ่มงาน / แผนกของท่าน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="helper-text">
                        <i class="bi bi-info-circle"></i> ระบบจะจำแนกบัญชีของท่านเข้าสังกัดแผนกนี้ทันทีหลังได้รับการอนุมัติ
                    </div>
                </div>

                <!-- Position & Phone -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="position">ตำแหน่งงาน</label>
                        <div class="input-group">
                            <i class="bi bi-briefcase input-icon"></i>
                            <input type="text" 
                                   id="position" 
                                   name="position" 
                                   class="form-control @error('position') is-invalid @enderror" 
                                   placeholder="เช่น พยาบาลวิชาชีพ, เภสัชกร" 
                                   value="{{ old('position', $user->position ?: 'บุคลากรโรงพยาบาล') }}">
                        </div>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">เบอร์โทรศัพท์ติดต่อ</label>
                        <div class="input-group">
                            <i class="bi bi-telephone input-icon"></i>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   placeholder="เบอร์ภายใน หรือมือถือ" 
                                   value="{{ old('phone', $user->phone) }}">
                        </div>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Citizen ID (Optional) -->
                @if(empty($user->cid))
                <div class="form-group">
                    <label class="form-label" for="cid">
                        เลขประจำตัวประชาชน 13 หลัก <span style="font-weight: normal; color: #64748b;">(ไม่บังคับ - ใช้ตรวจสอบคู่กับฐานข้อมูล HR)</span>
                    </label>
                    <div class="input-group">
                        <i class="bi bi-credit-card input-icon"></i>
                        <input type="text" 
                               id="cid" 
                               name="cid" 
                               maxlength="13" 
                               class="form-control @error('cid') is-invalid @enderror" 
                               placeholder="เลข 13 หลักเพื่อยืนยันตัวตนอัตโนมัติ" 
                               value="{{ old('cid') }}">
                    </div>
                    @error('cid')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-send-check"></i> บันทึกข้อมูลและส่งคำขอยืนยันตัวตน
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            <span class="security-badge">
                <i class="bi bi-shield-fill-check"></i> Hospital Security Policy
            </span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="logout-link" style="background: none; border: none; cursor: pointer; font-family: inherit;">
                    <i class="bi bi-box-arrow-right"></i> สลับบัญชี / ออกจากระบบ
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('onboardingForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> กำลังบันทึกข้อมูล...';
            btn.style.opacity = '0.75';
            btn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>
