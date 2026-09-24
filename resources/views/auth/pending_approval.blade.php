<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>รอการตรวจสอบยืนยันตัวตน - กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</title>
    
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
            --amber: #f59e0b;
            --amber-dark: #d97706;
            --amber-light: #fef3c7;
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
            background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, transparent 70%);
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
            background: radial-gradient(circle, rgba(13, 148, 136, 0.25) 0%, transparent 70%);
            bottom: -100px;
            right: -100px;
            pointer-events: none;
            filter: blur(40px);
        }

        .status-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 10;
            text-align: center;
        }

        /* Header Variations */
        .card-header-pending {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
            padding: 38px 28px 28px;
            position: relative;
        }

        .card-header-rejected {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 38px 28px 28px;
            position: relative;
        }

        .icon-pulse-box {
            width: 82px;
            height: 82px;
            border-radius: 26px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: pulse-glow 2.5s infinite;
        }

        @keyframes pulse-glow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 14px rgba(255, 255, 255, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        .status-card h1 {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .status-card p.subtitle {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 300;
            line-height: 1.5;
            max-width: 440px;
            margin: 0 auto;
        }

        .card-body {
            padding: 28px 24px;
        }

        /* Success / Flash Message */
        .alert-toast {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        /* Profile Summary Details Card */
        .info-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 18px 20px;
            margin-bottom: 22px;
            text-align: left;
        }

        .panel-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        .badge-dept {
            background: #ccfbf1;
            color: #0f766e;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        /* Security Advisory Box */
        .advisory-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            text-align: left;
            font-size: 12.5px;
            color: #92400e;
            line-height: 1.5;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .advisory-box i {
            font-size: 20px;
            color: #d97706;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .rejection-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            text-align: left;
            font-size: 12.5px;
            color: #991b1b;
            line-height: 1.5;
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .rejection-box i {
            font-size: 20px;
            color: #dc2626;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 14.5px;
            font-weight: 600;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .btn-refresh:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 148, 136, 0.4);
            color: white;
        }

        .btn-edit-profile {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #334155;
            border-radius: 12px;
            padding: 11px 18px;
            font-size: 13.5px;
            font-weight: 500;
            font-family: 'Prompt', sans-serif;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-edit-profile:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .card-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
        }

        .contact-admin {
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .logout-btn:hover {
            text-decoration: underline;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 12px 10px;
                align-items: flex-start;
                padding-top: max(16px, env(safe-area-inset-top));
                padding-bottom: max(16px, env(safe-area-inset-bottom));
            }
            .status-card {
                border-radius: 18px;
            }
            .card-header-pending,
            .card-header-rejected {
                padding: 24px 16px 18px;
            }
            .icon-pulse-box {
                width: 64px;
                height: 64px;
                font-size: 28px;
                border-radius: 20px;
            }
            .status-card h1 {
                font-size: 18px;
            }
            .card-body {
                padding: 20px 16px;
            }
            .info-item {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 4px;
            }
            .info-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>

    <div class="status-card">
        @if($user->isRejected())
            <!-- Rejected State -->
            <div class="card-header-rejected">
                <div class="icon-pulse-box" style="animation: none;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <h1>ไม่ผ่านการยืนยันตัวตน</h1>
                <p class="subtitle">ผู้ดูแลระบบได้ปฏิเสธการยืนยันตัวตนสำหรับบัญชีนี้</p>
            </div>

            <div class="card-body">
                <div class="rejection-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>เหตุผลที่ปฏิเสธ:</strong><br>
                        {{ $user->rejection_reason ?: 'ข้อมูลไม่ตรงกับฐานข้อมูลบุคลากรของโรงพยาบาล หรือไม่ใช่เจ้าหน้าที่ของโรงพยาบาล' }}
                    </div>
                </div>

                <div class="info-panel">
                    <div class="panel-title">ข้อมูลที่ลงทะเบียนไว้</div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-person"></i> ชื่อ - นามสกุล</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-envelope"></i> อีเมล</span>
                        <span class="info-value">{{ $user->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-building"></i> แผนกที่เลือก</span>
                        <span class="info-value">{{ $user->department?->name ?? 'ไม่ระบุ' }}</span>
                    </div>
                </div>

                <div class="action-group">
                    <a href="{{ route('auth.complete-profile') }}" class="btn-refresh">
                        <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลและส่งคำขอใหม่อีกครั้ง
                    </a>
                </div>
            </div>

        @else
            <!-- Pending State -->
            <div class="card-header-pending">
                <div class="icon-pulse-box">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h1>รอการตรวจสอบยืนยันตัวตน</h1>
                <p class="subtitle">ระบบได้บันทึกข้อมูลและส่งคำขอให้ผู้ดูแลระบบตรวจสอบความถูกต้องว่าเป็นเจ้าหน้าที่ของโรงพยาบาลจริง</p>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert-toast">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 16px;"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <!-- Profile Summary -->
                <div class="info-panel">
                    <div class="panel-title">
                        <span>ข้อมูลคำขอยืนยันตัวตน</span>
                        <span class="badge-dept">{{ $user->department?->name ?? 'ยังไม่ระบุแผนก' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-person"></i> ชื่อจริง - นามสกุล</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-envelope"></i> อีเมลเข้าสู่ระบบ</span>
                        <span class="info-value">{{ $user->email ?: $user->username }}</span>
                    </div>
                    @if($user->position)
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-briefcase"></i> ตำแหน่งงาน</span>
                        <span class="info-value">{{ $user->position }}</span>
                    </div>
                    @endif
                    @if($user->phone)
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-telephone"></i> เบอร์โทรศัพท์</span>
                        <span class="info-value">{{ $user->phone }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label"><i class="bi bi-calendar-event"></i> วันที่ยื่นคำขอ</span>
                        <span class="info-value">{{ thai_date($user->created_at, 'full') }}</span>
                    </div>
                </div>

                <!-- Advisory Box -->
                <div class="advisory-box">
                    <i class="bi bi-shield-lock-fill"></i>
                    <div>
                        <strong>ขั้นตอนการตรวจสอบ:</strong><br>
                        กลุ่มงานสุขภาพดิจิทัลจะตรวจสอบชื่อและแผนกของท่านกับฝ่ายบริหารบุคคล (HR) เมื่อได้รับการอนุมัติ ท่านจะเข้าใช้งานระบบสารสนเทศได้ทันทีโดยไม่ต้องลงทะเบียนซ้ำ
                    </div>
                </div>

                <div class="action-group">
                    <a href="{{ route('dashboard') }}" class="btn-refresh">
                        <i class="bi bi-arrow-clockwise"></i> ตรวจสอบสถานะการอนุมัติอีกครั้ง
                    </a>
                    <a href="{{ route('auth.complete-profile') }}" class="btn-edit-profile">
                        <i class="bi bi-pencil"></i> แก้ไขชื่อ-สกุล หรือเปลี่ยนแผนกสังกัด
                    </a>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="card-footer">
            <span class="contact-admin">
                <i class="bi bi-hospital"></i> กลุ่มงานสุขภาพดิจิทัล รพ.ทุ่งหัวช้าง
            </span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </button>
            </form>
        </div>
    </div>

</body>
</html>
