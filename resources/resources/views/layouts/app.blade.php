<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบบริหารจัดการสารสนเทศ') - {{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</title>

    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --primary-glow: rgba(13, 148, 136, 0.25);
            --secondary: #0284c7;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #0d9488;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-full: 9999px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 20px rgba(13, 148, 136, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Modern Slim Scrollbar (ระบบเลื่อนหน้าจอและแถบเลื่อน) */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
            border: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 9999px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Layout Structure */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, transparent 100%);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
        }

        .brand-title {
            font-weight: 700;
            font-size: 15px;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 400;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 14px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background-color: var(--sidebar-hover);
            color: #ffffff;
            transform: translateX(3px);
        }

        .nav-item.active {
            background-color: var(--sidebar-active);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .nav-item i {
            font-size: 18px;
            width: 22px;
            text-align: center;
        }

        .nav-badge {
            margin-left: auto;
            background: #f59e0b;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            line-height: 1;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.15);
        }

        .user-widget {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, #3b82f6, #0d9488);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            color: white;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13.5px;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(13, 148, 136, 0.25);
            color: #5eead4;
            margin-top: 2px;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 280px);
        }

        /* Top Navbar */
        .topbar {
            height: 68px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: var(--text-main);
            cursor: pointer;
            padding: 6px;
        }

        .page-title-box {
            display: flex;
            flex-direction: column;
        }

        .page-title {
            font-size: 19px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        .page-breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-main);
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .topbar-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .topbar-btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white !important;
            box-shadow: 0 2px 8px var(--primary-glow);
        }

        .topbar-btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .topbar-btn-secondary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: none;
            color: white !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
        }

        .topbar-btn-secondary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        /* Version Modal Styles */
        .version-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(5px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .version-modal-overlay.active {
            display: flex;
        }

        .version-modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 660px;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
            animation: modalSlideUp 0.2s ease-out;
            overflow: hidden;
        }

        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Content Area */
        .content-body {
            padding: 28px;
            flex: 1;
        }

        /* Flash Messages */
        .alert-box {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error, .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 24px;
            transition: box-shadow 0.2s;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 24px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px var(--primary-glow);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--text-main);
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12.5px;
            border-radius: 6px;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 16px;
            border-radius: var(--radius-md);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
        }

        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-primary { background: #ccfbf1; color: #0f766e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-orange { background: #ffedd5; color: #c2410c; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        .badge-outline-secondary { border: 1px solid #cbd5e1; color: #475569; }
        .badge-outline-info { border: 1px solid #7dd3fc; color: #0284c7; }
        .badge-outline-warning { border: 1px solid #fcd34d; color: #b45309; }
        .badge-outline-danger { border: 1px solid #fca5a5; color: #b91c1c; }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        .table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: #ffffff;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .page-item .page-link:hover:not(.disabled) {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: var(--primary);
        }

        .page-item.active .page-link {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 2px 6px var(--primary-glow);
        }

        .page-item.disabled .page-link {
            background: #f8fafc;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Forms */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-label.required::after {
            content: ' *';
            color: var(--danger);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background-color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            color: var(--text-main);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .form-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        /* Footer */
        .app-footer {
            padding: 20px 28px;
            border-top: 1px solid var(--border);
            background: #ffffff;
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Pulse Animation */
        @keyframes pulseGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.03); }
        }
        .animate-pulse {
            animation: pulseGlow 1.8s infinite;
        }

        /* Responsive Mobile */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .mobile-menu-btn {
                display: block;
            }
            .topbar, .content-body, .app-footer {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        /* Print Style */
        @media print {
            .sidebar, .topbar, .app-footer, .no-print, .btn {
                display: none !important;
            }
            .main-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .content-body {
                padding: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="bi bi-hospital"></i>
                </div>
                <div>
                    <div class="brand-title">{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }}</div>
                    <div class="brand-subtitle">{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-title">เมนูหลัก (Main Menu)</div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') || request()->is('/') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>แดชบอร์ดภาพรวม</span>
                </a>
                <a href="{{ route('repairs.index') }}" class="nav-item {{ request()->routeIs('repairs.*') ? 'active' : '' }}">
                    <i class="bi bi-tools"></i>
                    <span>ระบบแจ้งซ่อมบำรุง</span>
                    @php
                        $navPendingRepairs = \App\Models\Repair::where('status', 'pending')->count();
                    @endphp
                    @if($navPendingRepairs > 0)
                        <span class="nav-badge" style="background: #ef4444;" title="{{ $navPendingRepairs }} งานซ่อมรอดำเนินการ">{{ $navPendingRepairs }}</span>
                    @endif
                </a>
                <a href="{{ route('data-requests.index') }}" class="nav-item {{ request()->routeIs('data-requests.index') || request()->routeIs('data-requests.show') || request()->routeIs('data-requests.edit') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>ขอข้อมูลสารสนเทศ & สถิติ</span>
                    @php
                        $navPendingDr = \App\Models\DataRequest::where('status', 'pending')->count();
                    @endphp
                    @if($navPendingDr > 0)
                        <span class="nav-badge" title="{{ $navPendingDr }} คำขอข้อมูลรอพิจารณา">{{ $navPendingDr }}</span>
                    @endif
                </a>

                <div class="nav-section-title">คลังและพัสดุ (Inventory)</div>
                <a href="{{ route('assets.index') }}" class="nav-item {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                    <i class="bi bi-pc-display"></i>
                    <span>คลังคอมพิวเตอร์ & ครุภัณฑ์</span>
                </a>
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
                <a href="{{ route('spare-parts.index') }}" class="nav-item {{ request()->routeIs('spare-parts.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>คลังอะไหล่ & พัสดุ IT</span>
                </a>
                @endif

                <div class="nav-section-title">รายงานและสถิติ (Reports)</div>
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
                <a href="{{ route('reports.monthly') }}" class="nav-item {{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
                    <i class="bi bi-calendar-month"></i>
                    <span>รายงานสรุปประจำเดือน</span>
                </a>
                <a href="{{ route('reports.quarterly') }}" class="nav-item {{ request()->routeIs('reports.quarterly') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i>
                    <span>รายงานสรุปประจำไตรมาส</span>
                </a>
                @endif
                <a href="{{ route('reports.assets') }}" class="nav-item {{ request()->routeIs('reports.assets') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i>
                    <span>รายงานสถานะครุภัณฑ์</span>
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="nav-section-title">ผู้ดูแลระบบ (Admin)</div>
                <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>การตั้งค่าระบบ</span>
                </a>
                <a href="{{ route('backups.index') }}" class="nav-item {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                    <i class="bi bi-database-gear"></i>
                    <span>สำรอง & กู้คืนฐานข้อมูล</span>
                </a>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>ผู้ใช้งาน, สิทธิ์ & MFA</span>
                </a>
                <a href="{{ route('departments.index') }}" class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i>
                    <span>แผนกในโรงพยาบาล</span>
                </a>
                <a href="{{ route('audit-logs.index') }}" class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>บันทึกกิจกรรมระบบ (Audit Log)</span>
                </a>
                <a href="{{ route('system-updates.index') }}" class="nav-item {{ request()->routeIs('system-updates.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-arrow-down"></i>
                    <span>อัปเดตระบบ (System Update)</span>
                </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                @auth
                <div class="user-widget">
                    <div class="user-avatar">
                        @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                            <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
                        @else
                            {{ mb_substr(auth()->user()->name, 0, 1, 'UTF-8') }}
                        @endif
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-badge">{{ auth()->user()->role_name }}</div>
                    </div>
                </div>
                @endauth

                <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between;">
                    <button type="button" onclick="openVersionModal()" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #94a3b8; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all 0.2s;" onmouseover="this.style.color='#ffffff'; this.style.borderColor='var(--primary)';" onmouseout="this.style.color='#94a3b8'; this.style.borderColor='rgba(255,255,255,0.12)';" title="คลิกดูประวัติการอัปเดตระบบ">
                        <i class="bi bi-tag-fill" style="color: var(--primary);"></i>
                        <span>v{{ app_version() }}</span>
                        <i class="bi bi-info-circle ms-1" style="font-size: 10px; opacity: 0.7;"></i>
                    </button>
                    <span style="font-size: 10px; color: #64748b;">THC IT Platform</span>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Top Navbar -->
            <header class="topbar no-print">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-title-box">
                        <h1 class="page-title">@yield('page_title', 'ระบบบริหารจัดการสารสนเทศ')</h1>
                        <span class="page-breadcrumb">@yield('page_subtitle', setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') . ' ' . setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง'))</span>
                    </div>
                </div>
                <div class="topbar-right">
                    <a href="{{ route('repairs.create') }}" class="topbar-btn topbar-btn-primary" title="แจ้งซ่อมอุปกรณ์หรือปัญหาไอที">
                        <i class="bi bi-plus-lg"></i>
                        <span>แจ้งซ่อมด่วน</span>
                    </a>
                    <a href="{{ route('data-requests.create') }}" class="topbar-btn topbar-btn-secondary" title="ยื่นคำขอข้อมูลสารสนเทศและสถิติ">
                        <i class="bi bi-plus-lg"></i>
                        <span>ขอข้อมูล</span>
                    </a>

                    @auth
                    <a href="{{ route('profile') }}" class="topbar-btn" title="แก้ไขข้อมูลส่วนตัว">
                        <i class="bi bi-person-circle"></i>
                        <span>โปรไฟล์</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="display: inline;" id="logout-form">
                        @csrf
                        <button type="button" class="topbar-btn" onclick="confirmLogout()" title="ออกจากระบบ">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>ออก</span>
                        </button>
                    </form>
                    @endauth
                </div>
            </header>

            <!-- Main Body -->
            <main class="content-body">
                <!-- Flash Alerts -->
                @if(session('success'))
                <div class="alert-box alert-success">
                    <i class="bi bi-check-circle-fill" style="font-size: 18px;"></i>
                    <div>{{ session('success') }}</div>
                </div>
                @endif

                @if(session('error'))
                <div class="alert-box alert-error">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px;"></i>
                    <div>{{ session('error') }}</div>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert-box alert-warning">
                    <i class="bi bi-exclamation-circle-fill" style="font-size: 18px;"></i>
                    <div>{{ session('warning') }}</div>
                </div>
                @endif

                @if(session('info'))
                <div class="alert-box alert-info">
                    <i class="bi bi-info-circle-fill" style="font-size: 18px;"></i>
                    <div>{{ session('info') }}</div>
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="app-footer no-print">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <strong>{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }}</strong> {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} &bull; {{ setting('hospital_name_en', 'Thung Hua Chang Hospital') }} Digital Health IT
                    <button type="button" onclick="openVersionModal()" style="background: #e2e8f0; border: none; font-size: 11px; padding: 2px 8px; border-radius: 12px; color: #475569; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='#0d9488'; this.style.color='#ffffff';" onmouseout="this.style.background='#e2e8f0'; this.style.color='#475569';" title="คลิกเพื่อดูบันทึกการอัปเดตรุ่นระบบ">
                        <i class="bi bi-tag-fill"></i> v{{ app_version() }}
                    </button>
                </div>
                <div>
                    เชื่อมต่อฐานข้อมูล MariaDB: <code>{{ config('database.connections.mysql.host') }}</code> ({{ config('database.connections.mysql.database') }})
                </div>
            </footer>
        </div>
    </div>

    <!-- System Version & Changelog Modal -->
    @php
        $globalVersionInfo = app_version_info();
    @endphp
    <div id="versionModal" class="version-modal-overlay" onclick="handleVersionModalBackdrop(event)">
        <div class="version-modal-card">
            <!-- Modal Header -->
            <div style="padding: 20px 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(13, 148, 136, 0.3); border: 1px solid rgba(13, 148, 136, 0.5); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #5eead4;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div>
                        <div style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <span>{{ $globalVersionInfo['release_name'] }}</span>
                            <span style="background: #10b981; color: white; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: 600;">v{{ $globalVersionInfo['version'] }}</span>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                            Build: {{ $globalVersionInfo['build'] }} &bull; เผยแพร่เมื่อ: {{ $globalVersionInfo['release_date'] }}
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeVersionModal()" style="background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 8px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 24px; overflow-y: auto; max-height: calc(88vh - 140px);">
                <!-- System Specs Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 24px;">
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 10px 14px; text-align: center;">
                        <div style="font-size: 11px; color: #64748b;">เวอร์ชันระบบ</div>
                        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px;">v{{ $globalVersionInfo['version'] }}</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 10px 14px; text-align: center;">
                        <div style="font-size: 11px; color: #64748b;">PHP Engine</div>
                        <div style="font-size: 15px; font-weight: 700; color: #0284c7; margin-top: 2px;">PHP {{ $globalVersionInfo['php_version'] }}</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 10px 14px; text-align: center;">
                        <div style="font-size: 11px; color: #64748b;">Laravel Framework</div>
                        <div style="font-size: 15px; font-weight: 700; color: #ef4444; margin-top: 2px;">v{{ $globalVersionInfo['laravel_version'] }}</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 10px 14px; text-align: center;">
                        <div style="font-size: 11px; color: #64748b;">สภาพแวดล้อม</div>
                        <div style="font-size: 15px; font-weight: 700; color: #10b981; margin-top: 2px;">{{ ucfirst($globalVersionInfo['environment']) }}</div>
                    </div>
                </div>

                <!-- Changelog Timeline -->
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>ประวัติและบันทึกการอัปเดตระบบ (Release Notes & Changelog)</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($globalVersionInfo['changelog'] as $ver => $log)
                    <div style="background: #ffffff; border: 1px solid {{ $loop->first ? '#0d9488' : 'var(--border)' }}; border-radius: 12px; padding: 16px; box-shadow: {{ $loop->first ? '0 4px 12px rgba(13, 148, 136, 0.08)' : '0 1px 3px rgba(0,0,0,0.02)' }};">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px; font-weight: 700; color: #0f172a;">v{{ $ver }}</span>
                                <span style="background: {{ $log['badge_color'] ?? '#64748b' }}; color: white; font-size: 10.5px; padding: 2px 8px; border-radius: 10px; font-weight: 600;">
                                    {{ $log['badge'] ?? 'Release' }}
                                </span>
                            </div>
                            <span style="font-size: 12px; color: #64748b;">
                                <i class="bi bi-calendar3"></i> {{ $log['date'] }}
                            </span>
                        </div>
                        <div style="font-size: 13.5px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">
                            {{ $log['title'] }}
                        </div>
                        <ul style="margin: 0; padding-left: 20px; font-size: 12.5px; color: #475569; line-height: 1.6;">
                            @foreach($log['highlights'] as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                @if(auth()->check() && auth()->user()->isAdmin())
                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="{{ route('system-updates.index') }}" style="font-size: 12px; font-weight: 600; text-decoration: none; padding: 5px 12px; background: #0d9488; color: #fff; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-cloud-arrow-down"></i> ตรวจสอบการอัปเดต
                    </a>
                    <a href="{{ route('settings.index') }}#version" class="text-primary" style="font-size: 12px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-sliders"></i> จัดการเวอร์ชัน &rarr;
                    </a>
                </div>
                @else
                <div style="font-size: 12px; color: #64748b;">โรงพยาบาลทุ่งหัวช้าง &bull; Digital Health Division</div>
                @endif
                <button type="button" onclick="closeVersionModal()" class="topbar-btn" style="padding: 6px 16px; font-size: 12.5px;">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function openVersionModal() {
            document.getElementById('versionModal').classList.add('active');
        }

        function closeVersionModal() {
            document.getElementById('versionModal').classList.remove('active');
        }

        function handleVersionModalBackdrop(e) {
            if (e.target.id === 'versionModal') {
                closeVersionModal();
            }
        }

        // Close version modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVersionModal();
            }
        });

        function confirmLogout() {
            Swal.fire({
                title: 'ยืนยันการออกจากระบบ?',
                text: "คุณต้องการออกจากระบบการใช้งานใช่หรือไม่",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ออกจากระบบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        function changePerPage(val) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }
    </script>
    @stack('scripts')
</body>
</html>
