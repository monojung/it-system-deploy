<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการลงทะเบียนและตั้งรหัสผ่าน</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Prompt', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            padding: 36px 30px;
            text-align: center;
        }
        .header-icon {
            display: inline-block;
            width: 58px;
            height: 58px;
            line-height: 58px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            font-size: 28px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: #ccfbf1;
        }
        .content {
            padding: 36px 32px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 14px;
        }
        .badge-dept {
            display: inline-block;
            background: #f0fdfa;
            color: #0d9488;
            border: 1px solid #99f6e4;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .info-box {
            background: #f8fafc;
            border-left: 4px solid #0d9488;
            padding: 14px 18px;
            border-radius: 0 10px 10px 0;
            margin: 20px 0;
            font-size: 13.5px;
            color: #475569;
        }
        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
        }
        .btn-setup {
            display: inline-block;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            padding: 14px 34px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
        }
        .expiry-note {
            font-size: 12.5px;
            color: #94a3b8;
            text-align: center;
            margin-top: 10px;
        }
        .divider {
            border-top: 1px dashed #e2e8f0;
            margin: 28px 0;
        }
        .raw-link {
            font-size: 12px;
            color: #64748b;
            word-break: break-all;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">🏥</div>
            <h1>{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</h1>
            <p>{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} &bull; ระบบบริหารจัดการสารสนเทศ</p>
        </div>

        <!-- Body Content -->
        <div class="content">
            <div class="greeting">
                เรียน คุณ{{ $user->name }}
            </div>

            @if($user->department)
                <span class="badge-dept">🏢 {{ $user->department->name }}</span>
            @endif

            <p style="font-size: 14.5px; color: #334155; margin: 0 0 16px 0;">
                ท่านได้รับการลงทะเบียนเข้าใช้งานระบบบริหารจัดการสารสนเทศ กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง เรียบร้อยแล้ว เพื่อความมั่นคงปลอดภัยของข้อมูล ขอความกรุณากดปุ่มด้านล่างนี้เพื่อ<strong>ยืนยันอีเมลและกำหนดรหัสผ่านเข้าสู่ระบบครั้งแรก</strong>ของท่าน
            </p>

            <div class="info-box">
                🛡️ <strong>มาตรการความปลอดภัยไซเบอร์:</strong> บัญชีของท่านจะเริ่มใช้งานได้สมบูรณ์หลังจากที่ท่านกดลิงก์ยืนยันและกำหนดรหัสผ่านเรียบร้อยแล้ว
            </div>

            <!-- CTA Button -->
            <div class="btn-wrapper">
                <a href="{{ $setupUrl }}" target="_blank" class="btn-setup">
                    🔐 ยืนยันอีเมลและตั้งรหัสผ่านครั้งแรก
                </a>
                <div class="expiry-note">
                    ⏰ ลิงก์นี้มีความปลอดภัยเฉพาะบัญชีของท่าน และมีอายุการใช้งาน 24 ชั่วโมง
                </div>
            </div>

            <div class="divider"></div>

            <p style="font-size: 12.5px; color: #64748b; margin-bottom: 6px;">
                หากปุ่มด้านบนไม่สามารถคลิกได้ กรุณาคัดลอกลิงก์ด้านล่างไปเปิดในเว็บเบราว์เซอร์:
            </p>
            <div class="raw-link">
                {{ $setupUrl }}
            </div>

            <p style="font-size: 12px; color: #94a3b8; margin-top: 18px;">
                * หากท่านไม่ได้เป็นผู้ลงทะเบียนหรือร้องขออีเมลนี้ กรุณาเพิกเฉยต่ออีเมลฉบับนี้ หรือติดต่อกลุ่มงานสุขภาพดิจิทัล
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</strong> &bull; สุขภาพดิจิทัล (Digital Health Platform)<br>
            โทรศัพท์: {{ setting('hospital_phone', '053-595055') }} &bull; ต่อช่าง IT 101, 102
        </div>
    </div>
</body>
</html>
