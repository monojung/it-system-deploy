<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันตัวตน 2 ชั้น (Google Authenticator) - โรงพยาบาลทุ่งหัวช้าง</title>
    
    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #042f2e 0%, #0f172a 50%, #083344 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1e293b;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.28) 0%, transparent 70%);
            top: -150px;
            left: -150px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.22) 0%, transparent 70%);
            bottom: -100px;
            right: -100px;
            pointer-events: none;
        }

        .mfa-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .mfa-header {
            background: linear-gradient(135deg, #0f766e 0%, #042f2e 100%);
            color: white;
            padding: 34px 28px 26px;
            text-align: center;
            position: relative;
        }

        .auth-badge {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .auth-badge i {
            font-size: 32px;
            color: #38bdf8;
        }

        .mfa-header h1 {
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .mfa-header p {
            font-size: 13px;
            opacity: 0.88;
            font-weight: 300;
        }

        .mfa-body {
            padding: 28px 30px;
        }

        .user-preview {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-meta h4 {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .user-meta p {
            font-size: 12px;
            color: #64748b;
        }

        .otp-group {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 20px 0 16px;
        }

        .otp-input {
            width: 54px;
            height: 62px;
            border-radius: 12px;
            border: 2px solid #cbd5e1;
            background: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            color: #0f172a;
            transition: all 0.2s ease;
            outline: none;
        }

        .otp-input:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
            background: #f0fdf4;
        }

        .otp-input.filled {
            border-color: #0d9488;
            background: #f8fafc;
        }

        .timer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 12px;
            color: #64748b;
        }

        .timer-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #f1f5f9;
            border-radius: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: #0f766e;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Prompt', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(13, 148, 136, 0.4);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .help-box {
            margin-top: 24px;
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .help-box strong {
            color: #0f766e;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #64748b;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .cancel-link:hover {
            color: #dc2626;
        }
    </style>
</head>
<body>

    <div class="mfa-card">
        <div class="mfa-header">
            <div class="auth-badge">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>การยืนยันตัวตนแบบ 2 ขั้นตอน (MFA)</h1>
            <p>โรงพยาบาลทุ่งหัวช้าง • ระบบความปลอดภัยสารสนเทศ</p>
        </div>

        <div class="mfa-body">
            @if($errors->has('code'))
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px; flex-shrink: 0;"></i>
                    <span>{{ $errors->first('code') }}</span>
                </div>
            @endif

            <div class="user-preview">
                <div class="user-avatar">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                    @endif
                </div>
                <div class="user-meta">
                    <h4>{{ $user->name }}</h4>
                    <p>{{ $user->email ?? $user->username }} &bull; {{ $user->department?->name ?? 'บุคลากร รพ.' }}</p>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569; text-align: center; margin-bottom: 6px;">
                กรุณาเปิดแอป <strong>Google Authenticator</strong> บนสมาร์ตโฟน แล้วนำรหัส <strong>6 หลัก</strong> มาก่อกยืนยันตัวตน
            </p>

            <form action="{{ route('auth.mfa-verify') }}" method="POST" id="mfaForm">
                @csrf
                <input type="hidden" name="code" id="finalCode" value="">

                <div class="otp-group" id="otpBoxes">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="one-time-code" autofocus>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric">
                </div>

                <div class="timer-bar">
                    <span>รอบการอัปเดตรหัส (30 วินาที)</span>
                    <div class="timer-pill">
                        <span class="pulse-dot"></span>
                        <span id="countdown">30s</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-check2-circle"></i>
                    <span>ยืนยันรหัสความปลอดภัย</span>
                </button>
            </form>

            <div class="help-box">
                <div style="font-weight: 600; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-info-circle-fill text-primary"></i> <span>ไม่พบรหัสหรือมีปัญหาการเข้าใช้งาน?</span>
                </div>
                หากทำโทรศัพท์หาย หรือเปลี่ยนเครื่องใหม่ กรุณาติดต่อผู้ดูแลระบบ (Admin) ศูนย์คอมพิวเตอร์ โทร 053-975201 ต่อกลุ่มงานสุขภาพดิจิทัล เพื่อรีเซ็ต MFA Secret Key
            </div>

            <a href="{{ route('login') }}" class="cancel-link">
                <i class="bi bi-arrow-left"></i> ยกเลิกและกลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = Array.from(document.querySelectorAll('.otp-input'));
            const finalCode = document.getElementById('finalCode');
            const form = document.getElementById('mfaForm');

            // Auto-advance OTP inputs
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    const val = e.target.value.replace(/[^0-9]/g, '');
                    e.target.value = val ? val[0] : '';

                    if (e.target.value) {
                        e.target.classList.add('filled');
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    } else {
                        e.target.classList.remove('filled');
                    }

                    updateFinalCode();

                    // Auto submit if all 6 digits entered
                    if (inputs.every(i => i.value !== '')) {
                        form.submit();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        inputs[index - 1].classList.remove('filled');
                        updateFinalCode();
                    }
                });

                // Paste full code handler
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                    if (!text) return;

                    text.slice(0, 6).split('').forEach((char, i) => {
                        if (inputs[i]) {
                            inputs[i].value = char;
                            inputs[i].classList.add('filled');
                        }
                    });

                    updateFinalCode();
                    if (text.length >= 6) {
                        form.submit();
                    } else if (inputs[text.length]) {
                        inputs[text.length].focus();
                    }
                });
            });

            function updateFinalCode() {
                finalCode.value = inputs.map(i => i.value).join('');
            }

            form.addEventListener('submit', (e) => {
                updateFinalCode();
                if (finalCode.value.length !== 6) {
                    e.preventDefault();
                    alert('กรุณากรอกรหัส Google Authenticator ให้ครบ 6 หลัก');
                    inputs[0].focus();
                }
            });

            // TOTP 30-second cycle countdown
            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = 30 - (now % 30);
                const countdownEl = document.getElementById('countdown');
                if (countdownEl) {
                    countdownEl.textContent = `${remaining}s`;
                }
            }
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>
