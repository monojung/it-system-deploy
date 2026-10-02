@extends('layouts.app')

@section('title', 'คู่มือการใช้งานระบบ (System Manuals)')
@section('page_title', 'ศูนย์คู่มือการใช้งานระบบ')
@section('page_subtitle', 'เอกสารคู่มือการใช้งานและสถาปัตยกรรมระบบเวอร์ชัน 2.8.0 (ไฟล์ PDF พร้อมภาพประกอบ)')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 40px;">
    
    <!-- Hero Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d9488 100%); border-radius: 20px; padding: 32px 28px; color: #ffffff; margin-bottom: 28px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -20px; bottom: -20px; font-size: 160px; color: rgba(255,255,255,0.04); pointer-events: none;">
            <i class="bi bi-journal-bookmark-fill"></i>
        </div>
        <div style="max-width: 720px; position: relative; z-index: 1;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); padding: 5px 14px; border-radius: 9999px; font-size: 12px; font-weight: 600; margin-bottom: 14px; backdrop-filter: blur(8px);">
                <i class="bi bi-patch-check-fill text-warning"></i>
                <span>เอกสารฉบับทางการ &bull; Release v2.8.0 Production Ready</span>
            </div>
            <h2 style="font-size: 26px; font-weight: 800; margin: 0 0 10px 0; line-height: 1.3;">
                คู่มือการใช้งานและเอกสารเชิงเทคนิคประจำระบบ
            </h2>
            <p style="font-size: 14px; color: #cbd5e1; margin: 0; line-height: 1.6;">
                ดาวน์โหลดคู่มือการใช้งานฉบับสมบูรณ์ในรูปแบบไฟล์ PDF คุณภาพสูง พร้อมภาพประกอบหน้าจอ แผนผังการทำงาน และแนวทางปฏิบัติมาตรฐาน แบ่งตามกลุ่มเป้าหมายผู้ใช้งานจริง
            </p>
        </div>
    </div>

    <!-- 3 Manuals Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px;">
        
        <!-- MANUAL 1: USER MANUAL -->
        <div style="background: #ffffff; border-radius: 18px; border: 1.5px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(2, 132, 199, 0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.04)';">
            <div>
                <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 22px 24px; color: #ffffff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                            1 ไฟล์ &bull; END-USER
                        </span>
                        <span style="font-size: 12px; opacity: 0.85;">1.8 MB (PDF)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 800; margin: 0;">คู่มือสำหรับผู้ใช้งานทั่วไป</h3>
                            <span style="font-size: 12px; opacity: 0.9;">End-User Service & Helpdesk Manual</span>
                        </div>
                    </div>
                </div>

                <div style="padding: 22px 24px;">
                    <div style="margin-bottom: 16px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">กลุ่มเป้าหมาย:</strong>
                        <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px;">
                            บุคลากรทุกแผนก, แพทย์, พยาบาล, เจ้าหน้าที่ธุรการ
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">เนื้อหาสำคัญในคู่มือ:</strong>
                        <ul style="font-size: 12.5px; color: #475569; padding-left: 20px; margin: 6px 0 0 0; line-height: 1.7;">
                            <li>การเข้าสู่ระบบผ่าน ThaiD Digital ID และบัญชีองค์กร</li>
                            <li>ขั้นตอนการแจ้งซ่อมคอมพิวเตอร์และระบบเครือข่าย</li>
                            <li>การติดตามสถานะงานซ่อมแบบเรียลไทม์ และระบบ LINE Notify</li>
                            <li>การตรวจรับงานและการประเมินความพึงพอใจ 5 ระดับ</li>
                            <li>การยื่นคำขอยืมอุปกรณ์คอมพิวเตอร์ และตรวจสอบทรัพย์สินตนเอง</li>
                            <li>คำถามที่พบบ่อย (FAQ) และข้อควรปฏิบัติด้านความปลอดภัย</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                <a href="{{ asset('docs/User_Manual_IT_System_THC.pdf') }}" download="User_Manual_IT_System_THC.pdf" class="btn btn-primary btn-sm flex-grow-1" style="font-weight: 700; border-radius: 10px; padding: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="bi bi-file-earmark-pdf-fill"></i> ดาวน์โหลด PDF (1.8 MB)
                </a>
                <a href="{{ asset('docs/User_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-weight: 600; border-radius: 10px; padding: 10px 14px;" title="เปิดอ่านในเบราว์เซอร์">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <!-- MANUAL 2: ADMIN MANUAL -->
        <div style="background: #ffffff; border-radius: 18px; border: 1.5px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(67, 56, 202, 0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.04)';">
            <div>
                <div style="background: linear-gradient(135deg, #312e81 0%, #1e1b4b 100%); padding: 22px 24px; color: #ffffff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                            1 ไฟล์ &bull; ADMIN & TECH
                        </span>
                        <span style="font-size: 12px; opacity: 0.85;">2.2 MB (PDF)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 800; margin: 0;">คู่มือสำหรับแอดมินและช่างไอที</h3>
                            <span style="font-size: 12px; opacity: 0.9;">IT Administrator & Fleet Management Manual</span>
                        </div>
                    </div>
                </div>

                <div style="padding: 22px 24px;">
                    <div style="margin-bottom: 16px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">กลุ่มเป้าหมาย:</strong>
                        <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px;">
                            ผู้ดูแลระบบ (Super Admin, IT Admin), ช่างเทคนิคซ่อมบำรุง
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">เนื้อหาสำคัญในคู่มือ:</strong>
                        <ul style="font-size: 12.5px; color: #475569; padding-left: 20px; margin: 6px 0 0 0; line-height: 1.7;">
                            <li>แดชบอร์ดสถิติผู้บริหารและการประเมินเกณฑ์ SLA</li>
                            <li>วงจรการบริหารตั๋วงานซ่อม การจ่ายงาน และการเบิกตัดสต็อกอะไหล่</li>
                            <li>การบริหารทะเบียนครุภัณฑ์ IT Asset, งบประมาณ และประเภทการถือครอง</li>
                            <li>ระบบมอนิเตอร์ Agent Fleet v2.8.0 สด Real-time (CPU/RAM/Disk)</li>
                            <li>การสั่งอัปเดต Agent ทางไกล และระบบถอนการติดตั้งระยะไกล</li>
                            <li>การสำรองข้อมูล (Backup) และการตรวจสอบ Audit Logs</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                <a href="{{ asset('docs/Admin_Manual_IT_System_THC.pdf') }}" download="Admin_Manual_IT_System_THC.pdf" class="btn btn-dark btn-sm flex-grow-1" style="background: #1e1b4b; border-color: #1e1b4b; font-weight: 700; border-radius: 10px; padding: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="bi bi-file-earmark-pdf-fill"></i> ดาวน์โหลด PDF (2.2 MB)
                </a>
                <a href="{{ asset('docs/Admin_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-weight: 600; border-radius: 10px; padding: 10px 14px;" title="เปิดอ่านในเบราว์เซอร์">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <!-- MANUAL 3: DEVELOPER MANUAL -->
        <div style="background: #ffffff; border-radius: 18px; border: 1.5px solid #e2e8f0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(15, 23, 42, 0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.04)';">
            <div>
                <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 22px 24px; color: #ffffff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                            1 ไฟล์ &bull; DEVELOPER
                        </span>
                        <span style="font-size: 12px; opacity: 0.85;">2.3 MB (PDF)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 18px; font-weight: 800; margin: 0;">คู่มือสำหรับผู้พัฒนาและวิศวกร</h3>
                            <span style="font-size: 12px; opacity: 0.9;">Technical Architecture & Engineering Manual</span>
                        </div>
                    </div>
                </div>

                <div style="padding: 22px 24px;">
                    <div style="margin-bottom: 16px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">กลุ่มเป้าหมาย:</strong>
                        <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px;">
                            Software Developers, DevOps, QA, System Integrators
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <strong style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">เนื้อหาสำคัญในคู่มือ:</strong>
                        <ul style="font-size: 12.5px; color: #475569; padding-left: 20px; margin: 6px 0 0 0; line-height: 1.7;">
                            <li>สถาปัตยกรรม Laravel 11.x, PHP 8.2+, MySQL และ Redis Cache</li>
                            <li>โครงสร้างฐานข้อมูล ERD และ Foreign Key Constraints</li>
                            <li>RESTful API Endpoints & โปรโตคอล JSON-RPC Telemetry</li>
                            <li>สถาปัตยกรรม C# Native Agent (Dual Threading, Self-Update, Uninstaller)</li>
                            <li>การเขียนชุดทดสอบ PHPUnit Feature Tests (100% Passed)</li>
                            <li>CI/CD Automation Deployment Pipeline ด้วย <code>deploy_new.ps1</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                <a href="{{ asset('docs/Developer_Manual_IT_System_THC.pdf') }}" download="Developer_Manual_IT_System_THC.pdf" class="btn btn-secondary btn-sm flex-grow-1" style="background: #334155; border-color: #334155; font-weight: 700; border-radius: 10px; padding: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="bi bi-file-earmark-pdf-fill"></i> ดาวน์โหลด PDF (2.3 MB)
                </a>
                <a href="{{ asset('docs/Developer_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-weight: 600; border-radius: 10px; padding: 10px 14px;" title="เปิดอ่านในเบราว์เซอร์">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Metadata & Notice Box -->
    <div style="margin-top: 30px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-printer-fill"></i>
            </div>
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">ไฟล์ PDF พร้อมพิมพ์ในขนาด A4 มาตรฐาน</div>
                <div style="font-size: 12px; color: #64748b;">จัดหน้าและตัดขอบแบบ A4 Color พร้อมรูปภาพประกอบความละเอียดสูงและแผนผังระบบ</div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <span style="font-size: 12px; color: #64748b;">เวอร์ชันระบบ: <b>v{{ app_version() }}</b></span>
        </div>
    </div>

</div>
@endsection
