@extends('layouts.app')

@section('title', 'ข้อมูลส่วนตัวและความปลอดภัย')
@section('page_title', 'ข้อมูลส่วนตัวและความปลอดภัยดิจิทัล')
@section('page_subtitle', 'จัดการโปรไฟล์ผู้ใช้งาน การเชื่อมโยงบัญชี Google & ThaiD และระบบความปลอดภัย 2 ชั้น (Google Authenticator MFA)')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Staff Identity Summary Banner (Hero Badge) -->
    <div class="card" style="background: linear-gradient(135deg, #064e3b 0%, #0f766e 50%, #0369a1 100%); color: white; border: none; overflow: hidden; position: relative; box-shadow: 0 10px 25px -5px rgba(15, 118, 110, 0.25);">
        <!-- Background Decorative Elements -->
        <div style="position: absolute; right: -30px; top: -30px; width: 200px; height: 200px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); pointer-events: none;"></div>
        <div style="position: absolute; right: 120px; bottom: -50px; width: 150px; height: 150px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); pointer-events: none;"></div>

        <div class="card-body" style="padding: 28px 32px; position: relative; z-index: 1;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
                <!-- Left: Avatar & Identity -->
                <div style="display: flex; align-items: center; gap: 22px;">
                    <div style="width: 86px; height: 86px; border-radius: 50%; background: white; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; overflow: hidden; border: 4px solid rgba(255, 255, 255, 0.4); box-shadow: 0 4px 14px rgba(0,0,0,0.15); flex-shrink: 0;">
                        @if($user->avatar)
                            @if(str_starts_with($user->avatar, 'http'))
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @elseif(file_exists(public_path($user->avatar)))
                                <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                            @endif
                        @else
                            {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                        @endif
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                            <h2 style="font-size: 24px; font-weight: 700; margin: 0; color: white;">{{ $user->name }}</h2>
                            <span style="font-size: 13px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 9999px; font-weight: 500;">
                                @ {{ $user->username }}
                            </span>
                            <span class="badge {{ $user->role_badge }}" style="font-size: 12px; padding: 4px 10px;">
                                <i class="bi bi-shield-check"></i> {{ $user->role_name }}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap; font-size: 13px; opacity: 0.95;">
                            <div><i class="bi bi-hospital"></i> {{ $user->department?->name ?? 'ไม่ระบุแผนกสังกัด' }}</div>
                            @if($user->position)
                                <div><i class="bi bi-briefcase"></i> {{ $user->position }}</div>
                            @endif
                            @if($user->email)
                                <div><i class="bi bi-envelope"></i> {{ $user->email }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Security Level Status -->
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <!-- MFA Status Indicator -->
                    <div style="background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 12px 16px; min-width: 140px; text-align: center;">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 4px;">2-Factor (MFA)</div>
                        @if($user->isMfaActive())
                            <div style="color: #4ade80; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-shield-fill-check"></i> เปิดใช้งานแล้ว
                            </div>
                        @else
                            <div style="color: #f87171; font-weight: 600; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-shield-slash"></i> ยังไม่เปิดใช้งาน
                            </div>
                        @endif
                    </div>

                    <!-- Google Link Status Indicator -->
                    <div style="background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 12px 16px; min-width: 140px; text-align: center;">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 4px;">Google Account</div>
                        @if($user->hasGoogleLinked())
                            <div style="color: #60a5fa; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-google"></i> เชื่อมต่อแล้ว
                            </div>
                        @else
                            <div style="color: #e2e8f0; font-weight: 500; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-link-45deg"></i> ยังไม่เชื่อมต่อ
                            </div>
                        @endif
                    </div>

                    <!-- Thai ID Status Indicator -->
                    <div style="background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 12px 16px; min-width: 140px; text-align: center;">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 4px;">Thai ID (ThaID)</div>
                        @if($user->hasThaidLinked())
                            <div style="color: #34d399; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-person-vcard-fill"></i> ยืนยันแล้ว
                            </div>
                        @else
                            <div style="color: #fcd34d; font-weight: 600; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="bi bi-exclamation-triangle"></i> รอระบุเลข CID
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Digital Identity Linkage (สถาปัตยกรรมอัตลักษณ์ดิจิทัล ThaiD ➔ Google ➔ Hospital IT System) -->
    <div class="card" style="border-top: 4px solid #0284c7;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 16px; color: #0f172a;">การเชื่อมโยงบัญชีอัตลักษณ์ดิจิทัล (Digital Identity Linkage)</div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 400;">สถาปัตยกรรมเชื่อมโยงบัตรประชาชนดิจิทัล ThaiD สู่บัญชี Google เพื่อความสะดวกและปลอดภัยสูงสุด</div>
                </div>
            </div>
            @if($user->hasGoogleLinked() && $user->hasThaidLinked())
                <span class="badge badge-success" style="font-size: 12px; padding: 6px 12px;">
                    <i class="bi bi-check-all"></i> เชื่อมโยงสมบูรณ์ (ThaiD + Google)
                </span>
            @endif
        </div>

        <div class="card-body" style="padding: 24px;">
            <!-- Architecture Flow Diagram -->
            <div style="background: linear-gradient(90deg, #f0fdf4 0%, #eff6ff 50%, #f8fafc 100%); border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #0f766e; letter-spacing: 0.8px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-diagram-2"></i> แผนผังการเชื่อมโยงตัวตนดิจิทัล (Identity Federation Architecture)
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <!-- Step 1: ThaiD -->
                    <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 20px; margin: 0 auto 8px;">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <div style="font-weight: 700; font-size: 13px; color: #1e3a8a;">1. บัตรประชาชนดิจิทัล (ThaiD)</div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">ยืนยันตัวตนระดับชาติ (DOPA CID)</div>
                        <div style="margin-top: 8px;">
                            @if($user->hasThaidLinked())
                                <span style="display: inline-block; font-size: 11px; background: #dcfce7; color: #166534; font-weight: 600; padding: 2px 8px; border-radius: 9999px;">
                                    <i class="bi bi-check-circle-fill"></i> ผูกเลข 13 หลักแล้ว
                                </span>
                            @else
                                <span style="display: inline-block; font-size: 11px; background: #fef3c7; color: #92400e; font-weight: 600; padding: 2px 8px; border-radius: 9999px;">
                                    <i class="bi bi-exclamation-circle"></i> ยังไม่ได้ระบุ
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Arrow 1 -->
                    <div style="color: #94a3b8; font-size: 22px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-right d-none d-md-block"></i>
                        <i class="bi bi-arrow-down d-md-none"></i>
                    </div>

                    <!-- Step 2: Google Account -->
                    <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #fecaca; border-radius: 10px; padding: 14px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px; margin: 0 auto 8px;">
                            <i class="bi bi-google"></i>
                        </div>
                        <div style="font-weight: 700; font-size: 13px; color: #991b1b;">2. บัญชี Google องค์กร</div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">เชื่อมต่อ ThaiD ➔ Google SSO</div>
                        <div style="margin-top: 8px;">
                            @if($user->hasGoogleLinked())
                                <span style="display: inline-block; font-size: 11px; background: #dbeafe; color: #1e40af; font-weight: 600; padding: 2px 8px; border-radius: 9999px;">
                                    <i class="bi bi-check-circle-fill"></i> เชื่อมต่อ Google แล้ว
                                </span>
                            @else
                                <span style="display: inline-block; font-size: 11px; background: #f1f5f9; color: #64748b; font-weight: 600; padding: 2px 8px; border-radius: 9999px;">
                                    ยังไม่เชื่อมต่อ
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Arrow 2 -->
                    <div style="color: #94a3b8; font-size: 22px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-right d-none d-md-block"></i>
                        <i class="bi bi-arrow-down d-md-none"></i>
                    </div>

                    <!-- Step 3: Hospital IT System -->
                    <div style="flex: 1; min-width: 200px; background: white; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 20px; margin: 0 auto 8px;">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <div style="font-weight: 700; font-size: 13px; color: #115e59;">3. ระบบสารสนเทศ IT System</div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">เข้าใช้งานด้วย One-Tap & MFA</div>
                        <div style="margin-top: 8px;">
                            <span style="display: inline-block; font-size: 11px; background: #ecfdf5; color: #047857; font-weight: 600; padding: 2px 8px; border-radius: 9999px;">
                                <i class="bi bi-shield-check"></i> พร้อมใช้งาน
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Linkage Controls (Google & ThaiD) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                <!-- Google Account Card -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-google"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 14px;">บัญชี Google (Google Account)</div>
                                    <div style="font-size: 11px; color: #64748b;">ใช้สำหรับล็อกอินผ่าน Google One-Tap / SSO</div>
                                </div>
                            </div>
                            @if($user->hasGoogleLinked())
                                <span class="badge badge-success" style="font-size: 11px; padding: 4px 8px;">
                                    <i class="bi bi-check-circle-fill"></i> เชื่อมต่อแล้ว
                                </span>
                            @else
                                <span class="badge badge-light" style="font-size: 11px; padding: 4px 8px; color: #64748b;">
                                    ยังไม่เชื่อมต่อ
                                </span>
                            @endif
                        </div>

                        @if($user->hasGoogleLinked())
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">อีเมล Google ที่เชื่อมโยง:</div>
                                <div style="font-size: 13px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-envelope-at text-danger"></i> {{ $user->google_email ?? 'เชื่อมต่อด้วย Google ID' }}
                                </div>
                                @if($user->google_id)
                                    <div style="font-size: 10px; color: #94a3b8; margin-top: 4px; font-family: monospace;">Google ID: {{ substr($user->google_id, 0, 10) }}...</div>
                                @endif
                            </div>
                        @else
                            <p style="font-size: 12px; color: #64748b; line-height: 1.6; margin-bottom: 16px;">
                                คุณยังไม่ได้เชื่อมต่อบัญชี Google หากเชื่อมต่อแล้ว คุณจะสามารถกดเข้าสู่ระบบด้วย Google หรือล็อกอินผ่าน Single Sign-On (SSO) ได้ทันทีโดยไม่ต้องจำรหัสผ่าน
                            </p>
                        @endif
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @if($user->hasGoogleLinked())
                            <form action="{{ route('profile.unlink-google') }}" method="POST" onsubmit="return confirm('คุณต้องการยกเลิกการเชื่อมโยงบัญชี Google นี้ใช่หรือไม่?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-link-45deg"></i> ยกเลิกการเชื่อมต่อ Google
                                </button>
                            </form>
                        @else
                            <a href="{{ route('profile.link-google') }}" class="btn btn-danger btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                <i class="bi bi-google"></i> เชื่อมต่อบัญชี Google ของคุณ
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Thai ID Card -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-person-vcard"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 14px;">บัตรประชาชนดิจิทัล (ThaiD / CID)</div>
                                    <div style="font-size: 11px; color: #64748b;">เชื่อมโยงเข้าหาบัญชี Google เพื่อยืนยันตัวตนระดับชาติ</div>
                                </div>
                            </div>
                            @if($user->hasThaidLinked())
                                <span class="badge badge-success" style="font-size: 11px; padding: 4px 8px;">
                                    <i class="bi bi-check-circle-fill"></i> ยืนยันแล้ว
                                </span>
                            @else
                                <span class="badge badge-warning" style="font-size: 11px; padding: 4px 8px;">
                                    ยังไม่ระบุเลข
                                </span>
                            @endif
                        </div>

                        @if($user->hasThaidLinked())
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">เลขประจำตัวประชาชน 13 หลัก:</div>
                                <div style="font-size: 14px; font-weight: 700; font-family: monospace; color: #0f766e; letter-spacing: 1px;">
                                    {{ $user->formatted_cid }}
                                </div>
                                <div style="font-size: 11px; color: #16a34a; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <i class="bi bi-shield-check"></i> ผ่านการตรวจสอบ Checksum Modulo 11 ตามมาตรฐาน DOPA
                                </div>
                            </div>
                        @else
                            <p style="font-size: 12px; color: #64748b; line-height: 1.6; margin-bottom: 12px;">
                                ระบุเลขประจำตัวประชาชน 13 หลักของคุณ เพื่อใช้เชื่อมโยงกับแอป ThaID ของกรมการปกครอง และเตรียมพร้อมสำหรับการล็อกอินผ่าน Google ในอนาคต
                            </p>
                        @endif
                    </div>

                    <!-- CID Update Form -->
                    <form action="{{ route('profile.link-cid') }}" method="POST" style="margin-top: 8px;">
                        @csrf
                        <div style="display: flex; gap: 8px;">
                            <input type="text" name="cid" class="form-control form-control-sm" placeholder="เลขบัตร ปชช. 13 หลัก" maxlength="13" value="{{ old('cid', $user->cid) }}" required style="font-family: monospace; letter-spacing: 1px;" pattern="[0-9]{13}" title="กรุณากรอกตัวเลข 13 หลัก">
                            <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink: 0;">
                                <i class="bi bi-save"></i> บันทึก CID
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Google Authenticator (MFA Hub) - ผู้ใช้เปิด/ปิดเองได้ -->
    <div class="card" style="border-top: 4px solid #059669;" id="mfa-section">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 16px; color: #0f172a;">Google Authenticator (การยืนยันตัวตน 2 ชั้น - MFA)</div>
                    <div style="font-size: 12px; color: #64748b; font-weight: 400;">คุณสามารถเปิดหรือปิดการใช้งาน Google Authenticator ได้ด้วยตนเองเพื่อความปลอดภัยสูงสุด</div>
                </div>
            </div>
            <div>
                @if($user->isMfaActive())
                    <span class="badge badge-success" style="font-size: 13px; padding: 6px 14px;">
                        <i class="bi bi-shield-fill-check"></i> 🟢 เปิดใช้งาน MFA อยู่
                    </span>
                @else
                    <span class="badge badge-light" style="font-size: 13px; padding: 6px 14px; color: #64748b; border: 1px solid #cbd5e1;">
                        <i class="bi bi-shield-slash"></i> ⚪ ปิดใช้งาน MFA
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body" style="padding: 24px;">
            @if($user->isMfaActive())
                <!-- State A: MFA is ACTIVE -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 16px; font-weight: 700; color: #166534; margin: 0 0 4px 0;">
                                    บัญชีของคุณได้รับการปกป้องด้วย Google Authenticator แล้ว
                                </h3>
                                <p style="font-size: 13px; color: #15803d; line-height: 1.5; margin: 0;">
                                    ทุกครั้งที่ลงชื่อเข้าใช้ระบบ คุณจะต้องกรอกรหัสยืนยัน 6 หลักจากแอป Google Authenticator ในโทรศัพท์มือถือของคุณ
                                </p>
                                @if($user->mfa_enrolled_at)
                                    <div style="font-size: 12px; color: #047857; margin-top: 8px;">
                                        <i class="bi bi-clock-history"></i> เปิดใช้งานเมื่อ: <strong>{{ thai_date($user->mfa_enrolled_at, 'full') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            @if($user->mfa_enforced)
                                <span class="badge badge-warning" style="font-size: 12px; padding: 8px 12px;">
                                    <i class="bi bi-lock-fill"></i> บังคับใช้โดยผู้ดูแลระบบ (Admin Enforced)
                                </span>
                            @else
                                <!-- Button to trigger Disable Modal/Form -->
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleDisableMfaForm();">
                                    <i class="bi bi-shield-x"></i> ปิดใช้งาน Google Authenticator
                                </button>
                            @endif

                            <!-- Reset Key button -->
                            <form action="{{ route('profile.mfa.reset') }}" method="POST" onsubmit="return confirm('คุณต้องการสร้างคีย์ลับและ QR Code ชุดใหม่ใช่หรือไม่? (แอปเดิมจะต้องสแกนใหม่)');">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" title="สร้าง QR Code ใหม่เมื่อเปลี่ยนโทรศัพท์">
                                    <i class="bi bi-arrow-clockwise"></i> ขอ QR Code ใหม่
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Collapsible Disable MFA Box -->
                    @if(!$user->mfa_enforced)
                        <div id="disableMfaBox" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #86efac;">
                            <form action="{{ route('profile.mfa.disable') }}" method="POST" style="max-width: 480px;">
                                @csrf
                                <div style="font-size: 13px; font-weight: 700; color: #b91c1c; margin-bottom: 8px;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> ยืนยันการปิดใช้งาน Google Authenticator (MFA)
                                </div>
                                <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                                    การปิดใช้งาน MFA จะลดระดับความปลอดภัยของบัญชีลง กรุณากรอกรหัสผ่านของคุณเพื่อยืนยันการทำรายการ:
                                </p>
                                <div style="display: flex; gap: 8px;">
                                    <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="รหัสผ่านปัจจุบันของคุณ" required>
                                    <button type="submit" class="btn btn-danger btn-sm" style="flex-shrink: 0;">
                                        ยืนยันปิด MFA
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleDisableMfaForm();">
                                        ยกเลิก
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Show current backup QR code accordion (Optional view) -->
                <details style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 18px;">
                    <summary style="font-size: 13px; font-weight: 600; color: #0f766e; cursor: pointer;">
                        <i class="bi bi-qr-code"></i> แสดง QR Code และ Secret Key สำหรับสแกนเพิ่มในเครื่องสำรอง
                    </summary>
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; margin-top: 16px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                        <div style="width: 130px; height: 130px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div style="flex: 1; min-width: 260px;">
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">รหัสคีย์ลับ (Secret Key):</div>
                            <div style="display: flex; align-items: center; gap: 8px; max-width: 380px;">
                                <input type="text" value="{{ $user->mfa_secret }}" class="form-control form-control-sm" id="activeSecretKey" readonly style="font-family: monospace; font-weight: 700; color: #0f766e; background: white;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('activeSecretKey').value); alert('คัดลอกคีย์เรียบร้อยแล้ว: ' + document.getElementById('activeSecretKey').value);">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
                                คุณสามารถใช้คีย์นี้เพื่อป้อนลงในแอป Google Authenticator บนโทรศัพท์เครื่องใหม่ได้
                            </div>
                        </div>
                    </div>
                </details>

            @else
                <!-- State B: MFA is DISABLED - Clear 3-Step Setup Flow -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 700; color: #0f766e; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-shield-plus"></i> ขั้นตอนการเชื่อมต่อและเปิดใช้งาน Google Authenticator ด้วยตนเอง
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-bottom: 18px;">
                        ทำตาม 3 ขั้นตอนง่ายๆ ด้านล่าง เพื่อเปิดการยืนยันตัวตน 2 ชั้น ป้องกันการถูกโจรกรรมข้อมูลและบัญชีผู้ใช้งาน
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <!-- Step 1 & 2: Download & Scan -->
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #0284c7; margin-bottom: 10px;">
                                1. ดาวน์โหลดแอป และ 2. สแกน QR Code
                            </div>

                            <div style="display: flex; gap: 16px; align-items: center;">
                                <div style="width: 120px; height: 120px; background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ $qrCodeUrl }}" alt="Google Authenticator QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                                <div style="flex: 1;">
                                    <p style="font-size: 12px; color: #475569; margin-bottom: 8px; line-height: 1.5;">
                                        เปิดแอป <strong>Google Authenticator</strong> บนมือถือ กดเครื่องหมาย <strong>+</strong> แล้วเลือก <strong>"สแกนคิวอาร์โค้ด"</strong>
                                    </p>
                                    <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">หรือพิมพ์รหัสคีย์ลับด้วยตนเอง:</div>
                                    <div style="display: flex; gap: 6px;">
                                        <input type="text" value="{{ $user->mfa_secret }}" class="form-control form-control-sm" id="setupSecretKey" readonly style="font-family: monospace; font-size: 11px; font-weight: 700; color: #0f766e; background: #f8fafc;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('setupSecretKey').value); alert('คัดลอกคีย์เรียบร้อยแล้ว: ' + document.getElementById('setupSecretKey').value);" title="คัดลอกคีย์">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Enter Code & Enable -->
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #059669; margin-bottom: 10px;">
                                    3. ยืนยันรหัส 6 หลักเพื่อเปิดใช้งาน
                                </div>
                                <p style="font-size: 12px; color: #475569; margin-bottom: 14px; line-height: 1.5;">
                                    ป้อนรหัส OTP 6 หลักที่แสดงในแอป Google Authenticator บนโทรศัพท์ของคุณ เพื่อยืนยันว่าเชื่อมต่อสำเร็จ
                                </p>

                                <form action="{{ route('profile.mfa.enable') }}" method="POST">
                                    @csrf
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label class="form-label required" for="otp_code" style="font-size: 12px;">รหัส 6 หลักจาก Google Authenticator</label>
                                        <input type="text" id="otp_code" name="otp_code" class="form-control" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required style="font-size: 20px; font-weight: 700; letter-spacing: 6px; text-align: center; font-family: monospace;" autocomplete="one-time-code" autofocus>
                                        @error('otp_code')
                                            <div style="color: #dc2626; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700;">
                                        <i class="bi bi-shield-check"></i> เปิดใช้งาน Google Authenticator (MFA)
                                    </button>
                                </form>
                            </div>

                            <div style="margin-top: 14px; display: flex; justify-content: flex-end;">
                                <form action="{{ route('profile.mfa.reset') }}" method="POST" onsubmit="return confirm('ต้องการสร้างคีย์และ QR Code ใหม่ใช่หรือไม่?');">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm" style="color: #64748b; text-decoration: none; font-size: 11px; padding: 0;">
                                        <i class="bi bi-arrow-clockwise"></i> สร้าง QR Code ใหม่
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Section: Personal Profile & Change Password (2 Columns) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
        <!-- Card 1: ข้อมูลผู้ใช้งาน (Profile Info) -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-person-badge text-primary" style="font-size: 20px;"></i>
                    <span>ข้อมูลส่วนตัวบุคลากร</span>
                </div>
            </div>
            <div class="card-body" style="padding: 24px;">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Avatar Change -->
                    <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 22px; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0284c7); color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600; overflow: hidden; border: 2px solid #cbd5e1; flex-shrink: 0;">
                            @if($user->avatar)
                                @if(str_starts_with($user->avatar, 'http'))
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @elseif(file_exists(public_path($user->avatar)))
                                    <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                                @endif
                            @else
                                {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                            @endif
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label" for="avatar" style="margin-bottom: 4px; font-size: 13px;">เปลี่ยนรูปภาพโปรไฟล์</label>
                            <input type="file" id="avatar" name="avatar" class="form-control form-control-sm" accept="image/*">
                            <span class="form-text" style="font-size: 11px;">รองรับ JPG, PNG สูงสุด 2MB</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="name">ชื่อ - นามสกุล</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">ชื่อผู้ใช้งาน (Username)</label>
                        <input type="text" id="username" class="form-control" value="{{ $user->username }}" disabled style="background-color: #f8fafc; font-family: monospace; font-weight: 600;">
                        <span class="form-text">ชื่อผู้ใช้งานไม่สามารถเปลี่ยนแปลงได้</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">อีเมลติดต่อ (Primary Email)</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" placeholder="เช่น staff@thchospital.go.th">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">เบอร์โทรศัพท์ / เบอร์ภายใน</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="เช่น ต่อ 101 หรือ 081-xxxxxxx">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="position">ตำแหน่ง</label>
                        <input type="text" id="position" name="position" class="form-control" value="{{ old('position', $user->position) }}" placeholder="เช่น พยาบาลวิชาชีพปฏิบัติการ, เจ้าหน้าที่ IT">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="department_id">แผนก / ฝ่ายสังกัด</label>
                        @if($user->isAdmin())
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">-- ไม่ระบุ --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ $user->department?->name ?? 'ไม่ระบุ' }}" disabled style="background-color: #f8fafc;">
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px; width: 100%;">
                        <i class="bi bi-floppy-fill"></i> บันทึกข้อมูลส่วนตัว
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 2: เปลี่ยนรหัสผ่าน (Change Password) -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-shield-lock text-warning" style="font-size: 20px;"></i>
                    <span>เปลี่ยนรหัสผ่านเข้าสู่ระบบ</span>
                </div>
            </div>
            <div class="card-body" style="padding: 24px;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; margin-bottom: 20px; font-size: 12px; color: #92400e; line-height: 1.5;">
                    <i class="bi bi-info-circle-fill"></i> <strong>คำแนะนำด้านความปลอดภัย:</strong><br>
                    รหัสผ่านควรมีความยาวอย่างน้อย 6 ตัวอักษร ประกอบด้วยตัวอักษรพิมพ์ใหญ่ พิมพ์เล็ก และตัวเลขเพื่อความปลอดภัยสูงสุด
                </div>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label required" for="current_password">รหัสผ่านปัจจุบัน</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••" required>
                        @error('current_password')
                            <div style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="new_password">รหัสผ่านใหม่</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" required>
                        @error('new_password')
                            <div style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="new_password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" required>
                    </div>

                    <button type="submit" class="btn btn-warning" style="margin-top: 10px; width: 100%;">
                        <i class="bi bi-key-fill"></i> อัปเดตรหัสผ่านใหม่
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function toggleDisableMfaForm() {
    const box = document.getElementById('disableMfaBox');
    if (!box) return;
    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        box.style.display = 'none';
    }
}
</script>
@endsection
