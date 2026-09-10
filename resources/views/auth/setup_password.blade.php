<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านเข้าใช้งานครั้งแรก - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
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
            color: #1e293b;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow */
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

        .setup-card {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .setup-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 34px 28px 26px;
            text-align: center;
        }

        .lock-icon-box {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .setup-title {
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .setup-subtitle {
            font-size: 13.5px;
            color: #ccfbf1;
            margin-top: 4px;
        }

        .setup-body {
            padding: 26px 28px 32px;
        }

        /* User Profile Summary Card */
        .user-profile-summary {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.25);
        }

        .user-info-text {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-meta {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .dept-tag {
            background: #f0fdfa;
            color: #0d9488;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11.5px;
            border: 1px solid #99f6e4;
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

        /* Password Strength Meter */
        .strength-meter-container {
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .strength-bar-track {
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            display: flex;
            gap: 3px;
        }

        .strength-segment {
            flex: 1;
            height: 100%;
            background: #e2e8f0;
            transition: background 0.3s ease;
        }

        .strength-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        .strength-text {
            font-weight: 600;
        }

        /* Password Checklist */
        .password-checklist {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 22px;
            font-size: 12.5px;
        }

        .check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            margin-bottom: 6px;
            transition: color 0.2s;
        }

        .check-item:last-child {
            margin-bottom: 0;
        }

        .check-item.valid {
            color: #059669;
            font-weight: 500;
        }

        .check-item i {
            font-size: 14px;
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

        .setup-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <!-- Header -->
        <div class="setup-header">
            <div class="lock-icon-box">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1 class="setup-title">ตั้งรหัสผ่านเข้าใช้งานครั้งแรก</h1>
            <p class="setup-subtitle">ระบบบริหารจัดการสารสนเทศ โรงพยาบาลทุ่งหัวช้าง</p>
        </div>

        <!-- Body -->
        <div class="setup-body">
            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 13.5px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- User Info Card -->
            <div class="user-profile-summary">
                <div class="avatar-box">
                    <i class="bi bi-person"></i>
                </div>
                <div class="user-info-text">
                    <div class="user-name">คุณ{{ $user->name }}</div>
                    <div class="user-meta">
                        <span>{{ $user->email }}</span>
                        @if($user->department)
                            <span class="dept-tag">{{ $user->department->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Password Setup Form -->
            <form action="{{ route('auth.setup-password.post', ['token' => $token]) }}" method="POST" onsubmit="return validateForm()">
                @csrf

                <!-- New Password -->
                <div class="form-group">
                    <label class="form-label" for="new_password">กำหนดรหัสผ่านใหม่ <span style="color: #ef4444;">*</span></label>
                    <div class="input-group-custom">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="new_password" name="password" class="form-control-custom" placeholder="ระบุรหัสผ่านใหม่อย่างน้อย 8 ตัวอักษร" required autocomplete="new-password" oninput="checkStrength()">
                        <button type="button" class="btn-toggle-pwd" onclick="toggleVisibility('new_password', 'eye1')" title="เปิด/ปิดดูรหัสผ่าน">
                            <i id="eye1" class="bi bi-eye"></i>
                        </button>
                    </div>

                    <!-- Strength Meter -->
                    <div class="strength-meter-container">
                        <div class="strength-bar-track">
                            <div class="strength-segment" id="seg1"></div>
                            <div class="strength-segment" id="seg2"></div>
                            <div class="strength-segment" id="seg3"></div>
                            <div class="strength-segment" id="seg4"></div>
                        </div>
                        <div class="strength-label">
                            <span>ระดับความปลอดภัย:</span>
                            <span class="strength-text" id="strengthText">รอการกรอก</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">ยืนยันรหัสผ่านใหม่อีกครั้ง <span style="color: #ef4444;">*</span></label>
                    <div class="input-group-custom">
                        <i class="bi bi-shield-check input-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-custom" placeholder="กรอกรหัสผ่านเดิมซ้ำอีกครั้ง" required autocomplete="new-password" oninput="checkMatch()">
                        <button type="button" class="btn-toggle-pwd" onclick="toggleVisibility('password_confirmation', 'eye2')" title="เปิด/ปิดดูรหัสผ่าน">
                            <i id="eye2" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Live Checklist -->
                <div class="password-checklist">
                    <div class="check-item" id="rule_length">
                        <i class="bi bi-circle"></i>
                        <span>ความยาวอย่างน้อย 8 ตัวอักษร</span>
                    </div>
                    <div class="check-item" id="rule_char">
                        <i class="bi bi-circle"></i>
                        <span>ประกอบด้วยตัวอักษรภาษาอังกฤษ</span>
                    </div>
                    <div class="check-item" id="rule_number">
                        <i class="bi bi-circle"></i>
                        <span>ประกอบด้วยตัวเลข (0-9)</span>
                    </div>
                    <div class="check-item" id="rule_match">
                        <i class="bi bi-circle"></i>
                        <span>รหัสผ่านทั้งสองช่องตรงกัน</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btnSubmit" class="btn-submit-main">
                    <i class="bi bi-check2-circle" style="font-size: 20px;"></i>
                    บันทึกรหัสผ่านและเข้าสู่ระบบทันที
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="setup-footer">
            โรงพยาบาลทุ่งหัวช้าง &bull; ระบบสุขภาพดิจิทัล (Digital Health Platform)
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
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

        function checkStrength() {
            const pwd = document.getElementById('new_password').value;
            const seg1 = document.getElementById('seg1');
            const seg2 = document.getElementById('seg2');
            const seg3 = document.getElementById('seg3');
            const seg4 = document.getElementById('seg4');
            const text = document.getElementById('strengthText');

            const ruleLength = document.getElementById('rule_length');
            const ruleChar = document.getElementById('rule_char');
            const ruleNumber = document.getElementById('rule_number');

            // Reset
            [seg1, seg2, seg3, seg4].forEach(s => s.style.background = '#e2e8f0');

            let score = 0;
            if (pwd.length >= 8) {
                score++;
                setValid(ruleLength, true);
            } else {
                setValid(ruleLength, false);
            }

            if (/[a-zA-Z]/.test(pwd)) {
                score++;
                setValid(ruleChar, true);
            } else {
                setValid(ruleChar, false);
            }

            if (/[0-9]/.test(pwd)) {
                score++;
                setValid(ruleNumber, true);
            } else {
                setValid(ruleNumber, false);
            }

            if (/[^a-zA-Z0-9]/.test(pwd)) {
                score++;
            }

            if (pwd.length === 0) {
                text.textContent = 'รอการกรอก';
                text.style.color = '#64748b';
            } else if (score <= 1) {
                seg1.style.background = '#ef4444';
                text.textContent = 'ง่ายเกินไป';
                text.style.color = '#ef4444';
            } else if (score === 2) {
                seg1.style.background = '#f59e0b';
                seg2.style.background = '#f59e0b';
                text.textContent = 'ปานกลาง';
                text.style.color = '#f59e0b';
            } else if (score === 3) {
                seg1.style.background = '#10b981';
                seg2.style.background = '#10b981';
                seg3.style.background = '#10b981';
                text.textContent = 'ปลอดภัยดี';
                text.style.color = '#10b981';
            } else {
                seg1.style.background = '#0d9488';
                seg2.style.background = '#0d9488';
                seg3.style.background = '#0d9488';
                seg4.style.background = '#0d9488';
                text.textContent = 'ความปลอดภัยสูงมาก';
                text.style.color = '#0d9488';
            }

            checkMatch();
        }

        function checkMatch() {
            const pwd = document.getElementById('new_password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const ruleMatch = document.getElementById('rule_match');

            if (confirm.length > 0 && pwd === confirm) {
                setValid(ruleMatch, true);
            } else {
                setValid(ruleMatch, false);
            }
        }

        function setValid(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.add('valid');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                element.classList.remove('valid');
                icon.className = 'bi bi-circle';
            }
        }

        function validateForm() {
            const pwd = document.getElementById('new_password').value;
            const confirm = document.getElementById('password_confirmation').value;

            if (pwd.length < 8) {
                alert('กรุณากำหนดรหัสผ่านอย่างน้อย 8 ตัวอักษร');
                return false;
            }
            if (pwd !== confirm) {
                alert('รหัสผ่านและช่องยืนยันรหัสผ่านไม่ตรงกัน');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
