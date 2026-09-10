<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
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

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .auth-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            padding: 34px 28px 24px;
            text-align: center;
        }

        .icon-box {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .auth-title {
            font-size: 20px;
            font-weight: 700;
        }

        .auth-subtitle {
            font-size: 13px;
            color: #ccfbf1;
            margin-top: 4px;
        }

        .auth-body {
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .btn-submit-main {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
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
        }

        .link-back {
            display: block;
            text-align: center;
            color: #64748b;
            text-decoration: none;
            font-size: 13.5px;
            margin-top: 16px;
            font-weight: 500;
        }

        .link-back:hover {
            color: #0d9488;
        }

        .auth-footer {
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
    <div class="auth-card">
        <div class="auth-header">
            <div class="icon-box">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="auth-title">ขอลิงก์รีเซ็ตรหัสผ่าน</h1>
            <p class="auth-subtitle">ระบบบริหารจัดการสารสนเทศ โรงพยาบาลทุ่งหัวช้าง</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-size: 13px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <p style="font-size: 13.5px; color: #475569; margin-bottom: 18px; line-height: 1.5;">
                กรุณาระบุอีเมลที่ท่านใช้ลงทะเบียนในระบบ เราจะส่งลิงก์สำหรับกำหนดรหัสผ่านใหม่ไปยังกล่องข้อความอีเมลของท่าน
            </p>

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">อีเมลของคุณ <span style="color: #ef4444;">*</span></label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope-at input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control-custom" placeholder="เช่น yourname@thchospital.go.th" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-submit-main">
                    <i class="bi bi-send-fill"></i> ส่งลิงก์รีเซ็ตรหัสผ่านทางอีเมล
                </button>

                <a href="{{ route('login') }}" class="link-back">
                    <i class="bi bi-arrow-left"></i> กลับไปยังหน้าเข้าสู่ระบบ
                </a>
            </form>
        </div>

        <div class="auth-footer">
            โรงพยาบาลทุ่งหัวช้าง &bull; ระบบสุขภาพดิจิทัล (Digital Health Platform)
        </div>
    </div>
</body>
</html>
