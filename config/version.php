<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Version Information
    |--------------------------------------------------------------------------
    |
    | Canonical version number, release date, and changelog history for
    | the Thung Hua Chang Hospital IT Service Management Platform.
    |
    */

    'version' => env('APP_VERSION', '2.2.5'),
    'release_name' => 'Thung Hua Chang IT Service Platform',
    'release_date' => '2026-09-11',
    'build' => '20260911.3',
    'environment' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Remote Update Repository Configuration
    |--------------------------------------------------------------------------
    |
    | Target Git repository and branch used to check for system updates and
    | deploy releases for Thung Hua Chang Hospital IT Platform.
    |
    */
    'update_repo_url' => env('SYSTEM_UPDATE_REPO_URL', 'https://github.com/monojung/it-system-deploy.git'),
    'update_branch' => env('SYSTEM_UPDATE_BRANCH', 'main'),
    'update_remote_name' => env('SYSTEM_UPDATE_REMOTE_NAME', 'deploy'),

    /*
    |--------------------------------------------------------------------------
    | Changelog & Release History
    |--------------------------------------------------------------------------
    |
    | Structured release notes categorized by version numbers.
    |
    */
    'changelog' => [
        '2.2.5' => [
            'date' => '2026-09-11',
            'badge' => 'Mobile Experience & LAN Access Optimization',
            'badge_color' => '#0d9488',
            'title' => 'ปรับปรุงการเข้าใช้งานผ่านสมาร์ตโฟนบนเครือข่าย และออกแบบ Mobile App Navigation Bar',
            'highlights' => [
                'ปลดล็อกการเข้าใช้งานจากมือถือ/แท็บเล็ตผ่าน Wi-Fi และเครือข่าย LAN ภายในโรงพยาบาล (Binding 0.0.0.0 และเปิด Windows Firewall TCP Port 8000)',
                'เพิ่มแถบเมนูลอยด้านล่างสไตล์แอปมือถือ (Mobile Bottom Application Navigation Bar) 5 เมนูหลัก พร้อมปุ่มลอยแจ้งซ่อมด่วนตรงกลาง',
                'ปรับปรุง Topbar บนหน้าจอมือถือให้กะทัดรัด ซ่อนข้อความยาวเหลือเฉพาะไอคอนเพื่อป้องกันเนื้อหาล้นจอ',
                'แก้ปัญหา iOS Safari Auto-Zoom โดยควบคุมขนาดตัวอักษร 16px บนช่องกรอกข้อมูลและฟอร์มทั้งหมดบนอุปกรณ์พกพา',
                'ปรับปรุงหน้า Login ให้รองรับ Responsive จอมือถือขนาดเล็ก และรองรับขอบจอโค้ง (Viewport-Fit Safe Area Insets)',
                'สร้างสคริปต์ run_mobile_server.bat สำหรับเริ่มรันเซิร์ฟเวอร์แบบรองรับการเข้าถึงจากมือถือได้ทันทีในคลิกเดียว',
            ],
        ],
        '2.2.4' => [
            'date' => '2026-09-11',
            'badge' => 'UI/UX Fluidity & Micro-Interactions',
            'badge_color' => '#0d9488',
            'title' => 'ปรับปรุงระบบการแสดงผลและส่วนต่อประสานผู้ใช้ให้สมูท ไหลลื่น รวดเร็ว ไร้รอยต่อ',
            'highlights' => [
                'ติดตั้ง Global Slim Top Progress Bar (สไตล์ Linear/SPA) ตอบสนองทันทีเมื่อคลิกลิงก์เปลี่ยนหน้าหรือค้นหาข้อมูล ลดความรู้สึกหน่วง',
                'เพิ่มสัมผัสตอบสนองแบบ Tactile Micro-interaction (Active Scale) บนปุ่มกดและเมนูทั่วทั้งระบบ',
                'เพิ่มระบบ Auto-Loading Spinner บนปุ่ม Submit ของฟอร์มทุกหน้าเพื่อป้องกันการกดย้ำซ้ำซ้อนและแจ้งสถานะการประมวลผล',
                'ปรับปรุงการเปิด/ปิด Sidebar บนมือถือและแท็บเล็ต ด้วย Glassmorphism Backdrop พร้อม Gesture แตะที่ว่างหรือกด Esc เพื่อปิด',
                'กำหนด Scroll Behavior เป็น Smooth, เพิ่ม Font Smoothing (Antialiased) และ Page Content Fade-in Entrance ป้องกันหน้าจอกระตุก',
                'เพิ่มปุ่มลอยเลื่อนกลับด้านบน (Floating Back-to-Top Button) พร้อมแอนิเมชันนุ่มนวลเมื่อเลื่อนหน้าจอยาว',
                'ยกระดับการโต้ตอบของ Cards, Table Rows, Inputs Focus Ring, และ Flash Alerts ให้มีปุ่มกดปิด (Dismiss) ได้อย่างราบรื่น',
            ],
        ],
        '2.2.3' => [
            'date' => '2026-09-11',
            'badge' => 'Maintenance & Storage Optimization',
            'badge_color' => '#0d9488',
            'title' => 'ระบบควบคุม Auto-Backup, ปรับปรุงการลบไฟล์สำรอง และการจัดการพื้นที่จัดเก็บ',
            'highlights' => [
                'ปิดการทำงานระบบสำรองฐานข้อมูลอัตโนมัติ (Auto-Backup) เป็นค่าเริ่มต้น และเพิ่มสวิตช์เปิด/ปิดในหน้าตั้งค่าความปลอดภัยระบบ',
                'ปรับปรุงฟังก์ชันกู้คืนข้อมูล (Restore) และการอัปเดตระบบ ให้ไม่บังคับสำรองฉุกเฉิน ช่วยประหยัดพื้นที่จัดเก็บและเพิ่มความรวดเร็ว',
                'ปรับปรุงระบบลบไฟล์สำรองฐานข้อมูล (Backup Deletion) ให้ลบไฟล์ .sql จริงออกจากดิสก์และลบระเบียนในฐานข้อมูลอย่างถูกต้องสมบูรณ์ พร้อมบันทึก Audit Log',
                'เพิ่มฟังก์ชันล้างไฟล์สำรองตกค้างที่ไม่มีในฐานข้อมูล (Clean Orphan Backups) ช่วยคืนพื้นที่จัดเก็บบนเซิร์ฟเวอร์',
                'ปรับปรุงชุดทดสอบอัตโนมัติให้ทำความสะอาดไฟล์และข้อมูลทดสอบทันที ป้องกันการสะสมของไฟล์ขยะในระบบ',
            ],
        ],
        '2.2.2' => [
            'date' => '2026-09-10',
            'badge' => 'Production Ready & Patch Deployment',
            'badge_color' => '#0284c7',
            'title' => 'ถอดถอนระบบจำลองทั้งหมด (Simulation Removal) & ระบบอัปเดตผ่านไฟล์แพตช์ ZIP',
            'highlights' => [
                'ถอดถอนระบบจำลองและโหมดทดสอบออกทั้งหมด 100%: ลบ Role Simulator, ปุ่มลัดแอดมิน/ช่าง/พยาบาล ออกจากหน้า Login',
                'ปรับปรุงโมดัล ThaID และ Google Login ให้เชื่อมต่อเกตเวย์ทางการ ปลอดภัยพร้อมใช้งานบน Production',
                'ลบเส้นทางทดสอบจำลอง /auth/simulate และ /profile/simulate-link-google ออกจากระบบทั้งหมด',
                'พัฒนาระบบอัปเดตผ่านไฟล์ ZIP แพตช์ (Manual Patch Upload) ในหน้าศูนย์ควบคุมการอัปเดตระบบ',
                'ระบบสำรองฐานข้อมูลอัตโนมัติก่อนติดตั้งแพตช์ ป้องกันความปลอดภัยให้กับไฟล์คอนฟิก (.env) และ storage',
                'จัดเตรียมแพ็กเกจขึ้นโฮสต์สำหรับ PHP 8.2+ / Laravel 12 พร้อมสคริปต์ update.php และ unzip.php สำหรับเซิร์ฟเวอร์โรงพยาบาล',
            ],
        ],
        '2.2.1' => [
            'date' => '2026-09-09',
            'badge' => 'Settings & ICT Standards Overhaul',
            'badge_color' => '#0d9488',
            'title' => 'ศูนย์ควบคุมการตั้งค่าระบบ, เกณฑ์มาตรฐานครุภัณฑ์ ICT & ระบบสเปกฮาร์ดแวร์',
            'highlights' => [
                'ปรับปรุงศูนย์ควบคุมและการตั้งค่าระบบสารสนเทศ (System Settings Hub) 7 หมวดหมู่ เชื่อมโยงข้อมูลอัตโนมัติทั้งระบบ',
                'เชื่อมโยงข้อมูลหน่วยบริการ (ชื่อ รพ., รหัสสถานพยาบาล, กลุ่มงาน) ไปยัง Sidebar, Header, Footer, ใบสั่งซ่อม และสติกเกอร์ QR Code ครุภัณฑ์',
                'คำนวณเป้าหมายเวลาและกรอบมาตรฐาน SLA งานบริการซ่อมบำรุง 4 ระดับความเร่งด่วน พร้อมระบบแจ้งเตือนงานเกินกำหนดเวลา (Overdue Alert)',
                'พัฒนาระบบแจ้งเตือนอัตโนมัติผ่าน LINE Notify เมื่อมีใบแจ้งซ่อมใหม่ พร้อมปุ่มทดสอบส่งการแจ้งเตือนจริงในหน้าจอทันที',
                'เพิ่มการตั้งค่าเซิร์ฟเวอร์อีเมล (SMTP & Mail Delivery) พร้อมปุ่มทดสอบส่งอีเมลยืนยันการเชื่อมต่อ',
                'ระบบค้นหาและกรองสเปกฮาร์ดแวร์ครุภัณฑ์ (Hardware Specs: CPU, RAM DDR4/DDR5, Storage, OS) พร้อมหน้าต่าง Explorer',
                'ระบบนำเข้าข้อมูลครุภัณฑ์จากไฟล์ CSV (CSV Import for Admin) พร้อมไฟล์เทมเพลตมาตรฐาน UTF-8 BOM และ In-Browser Preview',
                'ระบบเลือกรายการครุภัณฑ์ตามเกณฑ์ราคากลางและคุณลักษณะพื้นฐาน ICT กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม (MDES) 29 รายการ พร้อมเติมสเปกและราคาอัตโนมัติ',
            ],
        ],
        '2.2.0' => [
            'date' => '2026-09-04',
            'badge' => 'Security & Auth Upgrade',
            'badge_color' => '#10b981',
            'title' => 'ระบบเชื่อมโยง Google + Thai ID, Google Authenticator MFA & ลบโมดูล HosXP',
            'highlights' => [
                'ลบโมดูลระบบข้อมูลแม่ข่าย HosXP และการเชื่อมต่อที่เกี่ยวข้องออกทั้งหมดอย่างสมบูรณ์',
                'ออกแบบและพัฒนาระบบเชื่อมโยงบัญชี Google Account เข้ากับ Thai ID (เลขบัตรประชาชน 13 หลัก / บัตรประชาชนดิจิทัล ThaID)',
                'พัฒนาระบบความปลอดภัยยืนยันตัวตนสองขั้นตอน (Multi-Factor Authentication - MFA) บังคับใช้ Google Authenticator (RFC 6238 TOTP Engine ในตัวแบบ Pure PHP)',
                'เพิ่มสวิตช์เปิด/ปิด MFA รายบุคคล และสวิตช์บังคับใช้ MFA (Admin Enforced) พร้อมปุ่ม Quick Toggle ในหน้าจัดการผู้ใช้งาน (/users)',
                'ระบบสแกน QR Code และรหัสลับ Base32 Secret Key รองรับการใช้งานผ่าน Google Authenticator บน Android และ iOS อย่างสมบูรณ์',
                'หน้าจอท้าทายรหัส MFA Challenge สไตล์โมเดิร์น พร้อมตัวนับถอยหลังวงรอบรหัส 30 วินาที และระบบตรวจสอบความปลอดภัย',
                'เพิ่มฟังก์ชันตัวช่วยแสดงวันที่ภาษาไทย พ.ศ. (thai_date) รองรับทุกรูปแบบวัน-เวลาในระบบ',
            ],
        ],
        '2.1.0' => [
            'date' => '2026-09-04',
            'badge' => 'Feature Update',
            'badge_color' => '#0284c7',
            'title' => 'ระบบควบคุมสิทธิ์ครุภัณฑ์ตามแผนก, Quick Actions & ระบบ Version',
            'highlights' => [
                'จำกัดการค้นหา/เลือกครุภัณฑ์ในหน้าแจ้งซ่อมเฉพาะแผนกของผู้ใช้งานทั่วไป ส่วนแอดมินและช่างสามารถเลือกดูได้ทุกแผนกพร้อมจัดกลุ่ม optgroup',
                'เพิ่มปุ่มทางลัด "+ ขอข้อมูล" ใน Top Navbar ด้านบน ข้างปุ่ม "+ แจ้งซ่อมด่วน" สะดวกต่อการเข้าถึง',
                'จัดระเบียบเมนู Sidebar นำทางให้กระชับ โดยลบเมนูสร้างรายการซ้ำซ้อนออก เพื่อเน้นใช้งานผ่าน Quick Action ปุ่มลัด',
                'เพิ่มระบบแสดงจำนวนรายการต่อหน้า (10, 20, 50, 100 รายการ) สำหรับหน้า Data Requests, Repairs และ Assets พร้อมตัวแบ่งหน้าภาษาไทย',
                'สร้างระบบ Version แสดงข้อมูลรุ่นระบบ ข้อมูลสภาพแวดล้อม และบันทึกประวัติการอัปเดต (Changelog)',
            ],
        ],
        '2.0.0' => [
            'date' => '2026-09-04',
            'badge' => 'Major Update',
            'badge_color' => '#0284c7',
            'title' => 'ระบบขอข้อมูลสารสนเทศ (Data Requests) & HosXP Integration',
            'highlights' => [
                'ระบบยื่นคำขอข้อมูลสารสนเทศและสถิติทางการแพทย์ (Data Requests & Statistics)',
                'ระบบเชื่อมต่อและตรวจสอบสถานะฐานข้อมูลโรงพยาบาล HosXP ตรวจสอบ Latency และโครงสร้างตารางข้อมูล',
                'ระบบยืนยันตัวตนผ่าน Thai ID (ThaID) และ Google Single Sign-On (SSO)',
                'ระบบบันทึก Audit Logs กิจกรรมการใช้งานและเหตุการณ์สำคัญในระบบ',
                'ระบบสำรองฐานข้อมูลอัตโนมัติ (Automated Database Backup) และระบบล้างข้อมูลเพื่อเตรียมใช้งานจริง',
            ],
        ],
        '1.0.0' => [
            'date' => '2026-09-03',
            'badge' => 'Initial Release',
            'badge_color' => '#64748b',
            'title' => 'เปิดตัวระบบบริหารจัดการเทคโนโลยีสารสนเทศ (IT Service Platform)',
            'highlights' => [
                'ระบบแจ้งซ่อมบำรุงคอมพิวเตอร์และอุปกรณ์ไอที (IT Repair Tickets)',
                'ระบบบริหารคลังคอมพิวเตอร์และครุภัณฑ์ (IT Assets Management)',
                'ระบบจัดการคลังอะไหล่และอุปกรณ์ไอที (Spare Parts Inventory)',
                'ระบบรายงานสถิติประจำเดือน ประจำไตรมาส และรายงานสถานะครุภัณฑ์',
                'ระบบแดชบอร์ดภาพรวมและตัวชี้วัด SLA งานบริการสารสนเทศ',
            ],
        ],
    ],
];
