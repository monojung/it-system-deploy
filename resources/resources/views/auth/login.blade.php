<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
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
            --primary-glow: rgba(13, 148, 136, 0.35);
            --secondary: #0284c7;
            --accent: #38bdf8;
            --dark-bg: #042f2e;
            --dark-card: #0f172a;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-lg: 24px;
            --radius-md: 14px;
            --radius-sm: 10px;
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
            padding: 24px 16px;
            color: var(--text-main);
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Highlights */
        body::before {
            content: '';
            position: absolute;
            width: 650px;
            height: 650px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.32) 0%, transparent 70%);
            top: -180px;
            left: -180px;
            pointer-events: none;
            filter: blur(40px);
        }

        body::after {
            content: '';
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, transparent 70%);
            bottom: -120px;
            right: -120px;
            pointer-events: none;
            filter: blur(40px);
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* Header */
        .auth-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 36px 28px 28px;
            text-align: center;
            position: relative;
        }

        .hospital-icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .auth-title {
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .auth-subtitle {
            font-size: 13.5px;
            color: #ccfbf1;
            margin-top: 4px;
            font-weight: 400;
        }

        /* Card Body */
        .auth-body {
            padding: 30px 28px;
        }

        /* Alert Styling */
        .alert-box {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.5;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 17px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control-custom {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 44px;
            font-family: inherit;
            font-size: 14.5px;
            color: #1e293b;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            outline: none;
            background: #ffffff;
            border-color: #0d9488;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
        }

        .form-control-custom:focus + .input-icon,
        .input-group-custom:focus-within .input-icon {
            color: #0d9488;
        }

        .btn-toggle-pwd {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 18px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .btn-toggle-pwd:hover {
            color: #0d9488;
        }

        /* Checkbox & Options */
        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 13.5px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: #0d9488;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .link-forgot {
            color: #0284c7;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link-forgot:hover {
            color: #0369a1;
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-submit-main {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
            transition: all 0.25s ease;
        }

        .btn-submit-main:hover {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(13, 148, 136, 0.45);
        }

        .btn-submit-main:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0 18px;
            color: #94a3b8;
            font-size: 12px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px dashed #cbd5e1;
        }

        .divider span {
            padding: 0 12px;
        }

        /* SSO Action Buttons */
        .sso-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn-sso {
            width: 100%;
            padding: 12px 18px;
            border-radius: 12px;
            border: 1.5px solid transparent;
            font-size: 14.5px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
        }

        .btn-sso:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .btn-google {
            background: #ffffff;
            color: #1f2937;
            border-color: #e2e8f0;
        }

        .btn-google:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-thaid {
            background: linear-gradient(135deg, #0b1f3a 0%, #031326 100%);
            color: #ffffff;
            border: 1.5px solid #0284c7;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            padding: 10px 16px;
        }

        .btn-thaid:hover {
            background: linear-gradient(135deg, #0f2b52 0%, #061e3d 100%);
            border-color: #38bdf8;
            box-shadow: 0 6px 18px rgba(56, 189, 248, 0.3);
        }

        .thaid-logo-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid rgba(56, 189, 248, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #04101e;
        }

        .thaid-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-text-block {
            text-align: left;
            flex: 1;
        }

        .btn-main-text {
            font-size: 14.5px;
            font-weight: 600;
            line-height: 1.2;
        }

        .btn-sub-text {
            font-size: 11.5px;
            opacity: 0.75;
            margin-top: 2px;
        }


        /* New Staff Notice */
        .new-staff-notice {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-top: 20px;
            text-align: center;
            font-size: 12.5px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Footer */
        .auth-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        /* Modals */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            padding: 26px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 18px;
            background: none;
            border: none;
            font-size: 22px;
            color: #94a3b8;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #1e293b;
        }

        .qr-frame {
            width: 190px;
            height: 190px;
            margin: 16px auto;
            padding: 10px;
            border: 2px dashed #0d9488;
            border-radius: 16px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-frame img {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Header -->
        <div class="auth-header">
            <div class="hospital-icon-wrapper">
                <i class="bi bi-hospital"></i>
            </div>
            <h1 class="auth-title">ระบบบริหารจัดการสารสนเทศ</h1>
            <p class="auth-subtitle">กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            @if(session('success'))
                <div class="alert-box alert-success">
                    <i class="bi bi-check-circle-fill" style="font-size: 18px; color: #16a34a; flex-shrink: 0;"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-box alert-info">
                    <i class="bi bi-info-circle-fill" style="font-size: 18px; color: #2563eb; flex-shrink: 0;"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-error">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px; color: #dc2626; flex-shrink: 0;"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Sign In Form -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="form-group">
                    <label class="form-label" for="login_email">อีเมลเข้าสู่ระบบ <span class="required">*</span></label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="text" id="login_email" name="email" class="form-control-custom" placeholder="เช่น yourname@thchospital.go.th" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label class="form-label" for="login_password">รหัสผ่าน <span class="required">*</span></label>
                    <div class="input-group-custom">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="login_password" name="password" class="form-control-custom" placeholder="กรอกรหัสผ่านของคุณ" required autocomplete="current-password">
                        <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('login_password', 'login_eye_icon')" title="เปิด/ปิดดูรหัสผ่าน">
                            <i id="login_eye_icon" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="auth-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>จดจำการเข้าสู่ระบบ</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="link-forgot">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit-main">
                    <i class="bi bi-box-arrow-in-right" style="font-size: 18px;"></i>
                    เข้าสู่ระบบด้วยอีเมล
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>หรือ เข้าสู่ระบบด้วย Digital ID</span>
            </div>

            <!-- SSO Buttons -->
            <div class="sso-group">
                <!-- Google SSO -->
                @if($hasGoogleConfig)
                    <a href="{{ route('auth.google') }}" class="btn-sso btn-google">
                        <div style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                            <svg width="22" height="22" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.35 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                            </svg>
                        </div>
                        <div class="btn-text-block">
                            <div class="btn-main-text">ลงชื่อเข้าใช้ด้วย Google</div>
                            <div class="btn-sub-text">บัญชีอีเมลโรงพยาบาล หรือ Google Workspace</div>
                        </div>
                        <i class="bi bi-chevron-right" style="color: #94a3b8;"></i>
                    </a>
                @else
                    <button type="button" class="btn-sso btn-google" onclick="openGoogleModal()">
                        <div style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                            <svg width="22" height="22" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.35 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                            </svg>
                        </div>
                        <div class="btn-text-block">
                            <div class="btn-main-text">ลงชื่อเข้าใช้ด้วย Google</div>
                            <div class="btn-sub-text">บัญชีอีเมลโรงพยาบาล หรือ Gmail</div>
                        </div>
                        <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 20px; font-weight: 500;">OAuth 2.0</span>
                    </button>
                @endif

                <!-- ThaID SSO -->
                <button type="button" class="btn-sso btn-thaid" onclick="openThaidModal()">
                    <div class="thaid-logo-box">
                        <img src="{{ asset('images/thaid-logo.jpg') }}" alt="ThaID Logo">
                    </div>
                    <div class="btn-text-block">
                        <div class="btn-main-text">ลงชื่อเข้าใช้ด้วย ThaID</div>
                        <div class="btn-sub-text">บัตรประชาชนดิจิทัล กรมการปกครอง (DOPA)</div>
                    </div>
                    <i class="bi bi-qr-code-scan" style="font-size: 20px; color: #38bdf8;"></i>
                </button>
            </div>


            <!-- New Staff Notice -->
            <div class="new-staff-notice">
                <i class="bi bi-info-circle-fill text-primary" style="font-size: 16px;"></i>
                <span>สำหรับบุคลากรใหม่: กรุณาติดต่อผู้ดูแลระบบสารสนเทศ (Admin) เพื่อสร้างบัญชีและรับลิงก์ตั้งรหัสผ่าน</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            โรงพยาบาลทุ่งหัวช้าง &bull; ระบบสุขภาพดิจิทัล (Digital Health Platform)
        </div>
    </div>

    <!-- ThaID QR Code Modal -->
    <div class="modal-overlay" id="thaidModal">
        <div class="modal-content" style="text-align: center;">
            <button type="button" class="modal-close" onclick="closeThaidModal()">&times;</button>
            
            <div style="margin-bottom: 12px;">
                <img src="{{ asset('images/thaid-logo.jpg') }}" alt="ThaID Logo" style="width: 54px; height: 54px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1.5px solid #bae6fd; margin-bottom: 6px;">
                <h3 style="font-size: 17px; font-weight: 700; color: #0c4a6e; margin: 0;">ยืนยันตัวตนด้วย ThaID</h3>
                <div style="font-size: 11.5px; color: #0284c7; font-weight: 500;">ระบบพิสูจน์และยืนยันตัวตนทางดิจิทัล กรมการปกครอง</div>
            </div>
            
            <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 10px;">
                เปิดแอปพลิเคชัน <strong>ThaID</strong> บนโทรศัพท์มือถือ แล้วกดปุ่มสแกน QR Code นี้
            </p>

            <div class="qr-frame">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(route('auth.thaid')) }}" alt="ThaID QR Code">
            </div>

            <div style="font-size: 12px; color: #0d9488; font-weight: 600; margin-bottom: 16px;">
                <i class="bi bi-clock"></i> อายุ QR Code: <span id="countdown">02:59</span> นาที
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="{{ route('auth.thaid') }}" class="btn-sso btn-thaid" style="padding: 10px 16px; justify-content: center; font-size: 13.5px; border-radius: 10px;">
                    <i class="bi bi-box-arrow-up-right"></i> เปิดหน้าเว็บ ThaID Gateway ทางการ
                </a>
            </div>
        </div>
    </div>

    <!-- Google OAuth Setup Modal -->
    <div class="modal-overlay" id="googleModal">
        <div class="modal-content" style="max-width: 460px; text-align: left;">
            <button type="button" class="modal-close" onclick="closeGoogleModal()">&times;</button>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #fee2e2; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #ea4335; flex-shrink: 0;">
                    <i class="bi bi-google"></i>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 700; color: #1e293b; margin: 0;">Google OAuth 2.0</h3>
                    <div style="font-size: 12px; color: #0284c7; font-weight: 500;">ระบบพร้อมเชื่อมต่อ Google Single Sign-On</div>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                ยังไม่ได้กำหนดค่า Google Client ID และ Client Secret ในระบบ กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแลระบบ (Admin) เพื่อตั้งค่าในระบบก่อนเปิดใช้งาน
            </p>

            <div style="display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeGoogleModal()" style="padding: 8px 18px; background: #0d9488; color: white; border: none; border-radius: 8px; font-weight: 500; font-family: inherit; font-size: 13px; cursor: pointer;">
                    ตกลง
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // ThaID Modal
        function openThaidModal() {
            document.getElementById('thaidModal').style.display = 'flex';
            startTimer();
        }

        function closeThaidModal() {
            document.getElementById('thaidModal').style.display = 'none';
        }

        // Google Modal
        function openGoogleModal() {
            document.getElementById('googleModal').style.display = 'flex';
        }

        function closeGoogleModal() {
            document.getElementById('googleModal').style.display = 'none';
        }

        let timerInterval;
        function startTimer() {
            let duration = 179;
            const display = document.getElementById('countdown');
            clearInterval(timerInterval);

            timerInterval = setInterval(function () {
                let minutes = parseInt(duration / 60, 10);
                let seconds = parseInt(duration % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--duration < 0) {
                    clearInterval(timerInterval);
                    display.textContent = "หมดอายุ";
                }
            }, 1000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const thaidModal = document.getElementById('thaidModal');
            const googleModal = document.getElementById('googleModal');
            if (event.target === thaidModal) closeThaidModal();
            if (event.target === googleModal) closeGoogleModal();
        };
    </script>
</body>
</html>
