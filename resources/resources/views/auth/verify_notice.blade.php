<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบอีเมลของคุณ - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
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
            padding: 24px 16px;
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
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.22) 0%, transparent 70%);
            bottom: -100px;
            right: -100px;
            pointer-events: none;
            filter: blur(40px);
        }

        .verify-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .verify-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 38px 24px 28px;
            position: relative;
        }

        .icon-pulse-box {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            animation: floatIcon 3s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .verify-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .verify-subtitle {
            font-size: 13.5px;
            color: #ccfbf1;
            margin-top: 4px;
        }

        .verify-body {
            padding: 32px 28px;
        }

        .email-display-badge {
            background: #f0fdfa;
            border: 1.5px solid #99f6e4;
            color: #0f766e;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            word-break: break-all;
            display: inline-block;
            margin: 10px 0 22px;
            box-shadow: 0 2px 6px rgba(13, 148, 136, 0.08);
        }

        /* Step List */
        .step-list {
            text-align: left;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            font-size: 13.5px;
            color: #334155;
            line-height: 1.5;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #0d9488;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Developer Quick Access Box */
        .dev-box {
            background: #fefce8;
            border: 1.5px dashed #facc15;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 22px;
            text-align: left;
        }

        .dev-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #854d0e;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .dev-btn {
            display: block;
            width: 100%;
            text-align: center;
            background: #eab308;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(234, 179, 8, 0.35);
            transition: all 0.2s;
        }

        .dev-btn:hover {
            background: #ca8a04;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Action Buttons */
        .btn-action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-resend {
            width: 100%;
            padding: 12px 18px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #334155;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-resend:hover {
            border-color: #0d9488;
            color: #0d9488;
            background: #f0fdfa;
        }

        .link-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            margin-top: 6px;
            transition: color 0.2s;
        }

        .link-back:hover {
            color: #0d9488;
        }

        .verify-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <!-- Header -->
        <div class="verify-header">
            <div class="icon-pulse-box">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h1 class="verify-title">ตรวจสอบอีเมลของคุณ</h1>
            <p class="verify-subtitle">ระบบบริหารจัดการสารสนเทศ โรงพยาบาลทุ่งหัวช้าง</p>
        </div>

        <!-- Body -->
        <div class="verify-body">
            @if(session('success'))
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px;">
                    <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
                </div>
            @endif

            <p style="font-size: 14.5px; color: #475569; margin-bottom: 6px;">
                ระบบได้จัดส่งลิงก์สำหรับ<strong>ยืนยันตัวตนและตั้งรหัสผ่านครั้งแรก</strong>ไปยังอีเมล:
            </p>

            <div class="email-display-badge">
                <i class="bi bi-envelope-at-fill" style="color: #0d9488; margin-right: 4px;"></i>
                {{ $email ?: 'อีเมลของคุณที่ลงทะเบียนไว้' }}
            </div>

            <!-- Steps -->
            <div class="step-list">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div>เปิดกล่องข้อความอีเมลของท่าน <em>(รวมถึงกล่องข้อความขยะ/Spam)</em></div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div>กดปุ่ม <strong>"ยืนยันและตั้งรหัสผ่านครั้งแรก"</strong> ภายใน 24 ชั่วโมง</div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>กำหนดรหัสผ่านใหม่เพื่อเริ่มเข้าใช้งานระบบทันที</div>
                </div>
            </div>

            <!-- Quick Password Setup Button -->
            @if(!empty($devSetupUrl))
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%); border: 2px solid #86efac; border-radius: 14px; padding: 18px 16px; margin-bottom: 22px; text-align: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
                    <div style="font-size: 13px; font-weight: 700; color: #166534; margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="bi bi-shield-check" style="font-size: 18px;"></i>
                        <span>ลิงก์ตั้งรหัสผ่านของคุณพร้อมใช้งานแล้ว:</span>
                    </div>
                    <p style="font-size: 12.5px; color: #374151; margin-bottom: 12px; line-height: 1.5;">
                        ท่านสามารถกดปุ่มด้านล่างนี้เพื่อ<strong>ตั้งรหัสผ่านและเริ่มเข้าสู่ระบบได้ทันที</strong>
                    </p>
                    <a href="{{ $devSetupUrl }}" class="dev-btn" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%); font-size: 14.5px; padding: 12px 18px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);">
                        <i class="bi bi-key-fill"></i> 🔐 กดที่นี่เพื่อตั้งรหัสผ่านทันที (คลิกเข้าใช้งาน)
                    </a>
                    
                    @if($isLogMailer ?? true)
                        <div style="margin-top: 12px; font-size: 11.5px; color: #64748b; background: rgba(255,255,255,0.7); padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0; text-align: left;">
                            ℹ️ <em>ขณะนี้เซิร์ฟเวอร์บันทึกอีเมลลง Log ในเครื่อง (<code>MAIL_MAILER=log</code>) จึงไม่ได้ส่งออกไปยังกล่องจดหมายจริงภายนอก ท่านสามารถกดปุ่มด้านบนนี้เพื่อตั้งรหัสผ่านได้ทันที หรือตั้งค่า SMTP ในระบบเพื่อส่งเข้า Gmail จริง</em>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Action buttons -->
            <div class="btn-action-group">
                <form action="{{ route('auth.resend-setup-link') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="btn-resend">
                        <i class="bi bi-arrow-clockwise"></i> ส่งลิงก์ยืนยันใหม่อีกครั้ง (Resend Link)
                    </button>
                </form>

                <a href="{{ route('login') }}" class="link-back">
                    <i class="bi bi-arrow-left"></i> กลับไปยังหน้าเข้าสู่ระบบ
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="verify-footer">
            หากไม่ได้รับอีเมล กรุณาติดต่อกลุ่มงานสุขภาพดิจิทัล โทร. {{ setting('hospital_phone', '053-595055') }} ต่อ 101
        </div>
    </div>
</body>
</html>
