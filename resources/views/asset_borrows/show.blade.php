@extends('layouts.app')

@section('title', 'รายละเอียดการยืม-คืนอุปกรณ์ - ' . $borrow->borrow_no)
@section('page_title', 'รายละเอียดการยืม-คืนอุปกรณ์ไอที')
@section('page_subtitle', 'รหัสคำขอ: ' . $borrow->borrow_no)

@section('topbar-actions')
<div style="display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="{{ route('asset-borrows.print', $borrow->id) }}" target="_blank" class="topbar-btn" style="display: inline-flex; align-items: center; gap: 6px;">
        <i class="bi bi-printer"></i>
        <span>พิมพ์ใบยืม-คืน A4</span>
    </a>

    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
        @if($borrow->status === 'pending')
            <form action="{{ route('asset-borrows.approve', $borrow->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันการอนุมัติคำขอยืมอุปกรณ์นี้หรือไม่?')">
                @csrf
                <button type="submit" class="topbar-btn topbar-btn-primary" style="background: #10b981; border-color: #10b981;">
                    <i class="bi bi-check-lg"></i> อนุมัติคำขอ
                </button>
            </form>
            <button type="button" class="topbar-btn" style="color: #ef4444;" onclick="document.getElementById('rejectModal').style.display='flex'">
                <i class="bi bi-x-circle"></i> ปฏิเสธ
            </button>
        @elseif($borrow->status === 'approved')
            <button type="button" class="topbar-btn topbar-btn-primary" style="background: #0d9488; border-color: #0d9488;" onclick="document.getElementById('dispatchModal').style.display='flex'">
                <i class="bi bi-box-arrow-right"></i> ส่งมอบอุปกรณ์
            </button>
        @elseif($borrow->status === 'borrowed')
            <button type="button" class="topbar-btn topbar-btn-primary" style="background: #10b981; border-color: #10b981;" onclick="document.getElementById('returnModal').style.display='flex'">
                <i class="bi bi-arrow-down-left-circle"></i> รับคืนอุปกรณ์
            </button>
        @endif
    @endif

    @if(in_array($borrow->status, ['pending']) || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician())))
        <a href="{{ route('asset-borrows.edit', $borrow->id) }}" class="topbar-btn">
            <i class="bi bi-pencil"></i> แก้ไข
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="content-container">

    <!-- Back Button & Status Alert -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <a href="{{ route('asset-borrows.index') }}" class="btn btn-outline-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-arrow-left"></i> กลับหน้ารวมรายการยืม-คืน
        </a>

        <div>
            {!! $borrow->status_badge !!}
            @if($borrow->is_overdue)
                <span class="badge bg-danger ms-1">เกินกำหนดส่งคืน</span>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-check-circle-fill font-lg"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info" style="background: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-info-circle-fill font-lg"></i>
        <span>{{ session('info') }}</span>
    </div>
    @endif

    <!-- WORKFLOW ACTION HERO PANEL (ศูนย์สั่งการสถานะคำขอยืม-คืน) -->
    <div class="card" style="border-radius: 16px; border: 1.5px solid #0d9488; background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); margin-bottom: 24px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.12); padding: 20px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span>ศูนย์จัดการคำขอยืม-คืนอุปกรณ์</span>
                        <span class="badge" style="background: #ccfbf1; color: #0f766e; font-size: 11px;">Staff Action Panel</span>
                        {!! $borrow->status_badge !!}
                    </div>
                    <div style="font-size: 13px; color: #475569; margin-top: 3px;">
                        @if($borrow->status === 'pending')
                            <span style="color: #0284c7; font-weight: 600;"><i class="bi bi-hourglass-split"></i> รอการตรวจสอบและอนุมัติจากเจ้าหน้าที่ไอที</span>
                        @elseif($borrow->status === 'approved')
                            <span style="color: #10b981; font-weight: 600;"><i class="bi bi-check-circle-fill"></i> อนุมัติแล้ว &bull; พร้อมตรวจสอบและส่งมอบอุปกรณ์ให้ผู้ยืม</span>
                        @elseif($borrow->status === 'borrowed')
                            <span style="color: #0d9488; font-weight: 600;"><i class="bi bi-box-arrow-right"></i> อุปกรณ์ถูกส่งมอบแล้ว กำลังอยู่ระหว่างการยืมใช้งาน</span>
                        @elseif($borrow->status === 'returned')
                            <span style="color: #16a34a; font-weight: 600;"><i class="bi bi-check2-all"></i> ส่งมอบคืนอุปกรณ์และตรวจรับสภาพเรียบร้อยแล้ว</span>
                        @elseif($borrow->status === 'rejected')
                            <span style="color: #ef4444; font-weight: 600;"><i class="bi bi-x-circle-fill"></i> คำขอนี้ไม่ได้รับการอนุมัติ</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
                    @if($borrow->status === 'pending')
                        <form action="{{ route('asset-borrows.approve', $borrow->id) }}" method="POST" style="display: inline;" id="approveForm">
                            @csrf
                            <button type="button" onclick="confirmApprove()" class="btn btn-success" style="background: #10b981; border: none; font-weight: 700; padding: 10px 22px; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>อนุมัติคำขอยืม</span>
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('rejectModal').style.display='flex'" style="font-weight: 600; padding: 10px 18px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-x-circle"></i>
                            <span>ปฏิเสธคำขอ</span>
                        </button>
                    @elseif($borrow->status === 'approved')
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('dispatchModal').style.display='flex'" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none; font-weight: 700; padding: 10px 22px; border-radius: 10px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>ส่งมอบอุปกรณ์ให้ผู้ยืม</span>
                        </button>
                    @elseif($borrow->status === 'borrowed')
                        <button type="button" class="btn btn-success" onclick="document.getElementById('returnModal').style.display='flex'" style="background: #10b981; border: none; font-weight: 700; padding: 10px 22px; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); display: inline-flex; align-items: center; gap: 8px;">
                            <i class="bi bi-arrow-down-left-circle-fill"></i>
                            <span>บันทึกรับคืนอุปกรณ์</span>
                        </button>
                    @endif
                @endif

                <a href="{{ route('asset-borrows.print', $borrow->id) }}" target="_blank" class="btn btn-outline-secondary" style="font-weight: 600; padding: 10px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-printer"></i>
                    <span>พิมพ์ใบยืม-คืน (A4)</span>
                </a>

                @if(in_array($borrow->status, ['pending']) || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician())))
                    <a href="{{ route('asset-borrows.edit', $borrow->id) }}" class="btn btn-outline-secondary" style="font-weight: 600; padding: 10px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-pencil"></i>
                        <span>แก้ไขคำขอ</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- WORKFLOW PROGRESS STEPPER -->
    <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); padding: 22px;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; position: relative;">
            
            {{-- Step 1: ยื่นคำขอ --}}
            <div style="text-align: center;">
                <div style="width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; background: #0d9488; color: #ffffff; box-shadow: 0 2px 8px rgba(13,148,136,0.3);">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">1. ยื่นคำขอยืม</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $borrow->created_at ? $borrow->created_at->format('d/m/Y H:i') : '-' }}</div>
            </div>

            {{-- Step 2: อนุมัติ --}}
            @php
                $step2Active = in_array($borrow->status, ['approved', 'borrowed', 'returned']);
            @endphp
            <div style="text-align: center;">
                <div style="width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; background: {{ $step2Active ? '#0284c7' : '#e2e8f0' }}; color: {{ $step2Active ? '#ffffff' : '#64748b' }};">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div style="font-weight: 700; font-size: 13px; color: {{ $step2Active ? '#0f172a' : '#94a3b8' }};">2. เจ้าหน้าที่อนุมัติ</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ $borrow->approved_at ? $borrow->approved_at->format('d/m/Y H:i') : ($borrow->status === 'rejected' ? 'ปฏิเสธคำขอ' : 'รอการอนุมัติ') }}
                </div>
            </div>

            {{-- Step 3: ส่งมอบอุปกรณ์ --}}
            @php
                $step3Active = in_array($borrow->status, ['borrowed', 'returned']);
            @endphp
            <div style="text-align: center;">
                <div style="width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; background: {{ $step3Active ? '#0d9488' : '#e2e8f0' }}; color: {{ $step3Active ? '#ffffff' : '#64748b' }};">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <div style="font-weight: 700; font-size: 13px; color: {{ $step3Active ? '#0f172a' : '#94a3b8' }};">3. ส่งมอบใช้งาน</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ $borrow->dispatched_at ? $borrow->dispatched_at->format('d/m/Y H:i') : 'รอรับอุปกรณ์' }}
                </div>
            </div>

            {{-- Step 4: รับคืนเรียบร้อย --}}
            @php
                $step4Active = ($borrow->status === 'returned');
            @endphp
            <div style="text-align: center;">
                <div style="width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; background: {{ $step4Active ? '#10b981' : '#e2e8f0' }}; color: {{ $step4Active ? '#ffffff' : '#64748b' }};">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div style="font-weight: 700; font-size: 13px; color: {{ $step4Active ? '#0f172a' : '#94a3b8' }};">4. ส่งคืนเรียบร้อย</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ $borrow->actual_return_date ? $borrow->actual_return_date->format('d/m/Y') : 'ยังไม่ส่งคืน' }}
                </div>
        </div>
    </div>

    @if($borrow->repair)
    <!-- LINKED REPAIR TICKET BANNER -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1.5px solid #93c5fd; border-radius: 16px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);">
                <i class="bi bi-tools"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-weight: 800; font-size: 15.5px; color: #1e3a8a;">ผูกกับใบแจ้งซ่อม: #{{ $borrow->repair->ticket_number }}</span>
                    <span class="badge {{ $borrow->repair->status_badge }}" style="font-size: 11px;">{{ $borrow->repair->status_label }}</span>
                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 11px;">เครื่องสำรองใช้งานระหว่างซ่อม</span>
                </div>
                <div style="font-size: 13px; color: #475569; margin-top: 4px;">
                    <strong>เรื่องที่แจ้งซ่อม:</strong> {{ $borrow->repair->title }}
                    @if($borrow->repair->asset)
                        • <strong>เครื่องที่ส่งซ่อม:</strong> {{ $borrow->repair->asset->name }} ({{ $borrow->repair->asset->asset_code }})
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('repairs.show', $borrow->repair->id) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 9px 18px; border-radius: 10px; box-shadow: 0 2px 6px rgba(37,99,235,0.2);">
            <i class="bi bi-arrow-up-right-circle"></i> ดูรายละเอียดใบแจ้งซ่อม
        </a>
    </div>
    @endif

    <!-- MAIN TWO-COLUMN DETAILS -->
    <div class="row g-4 mb-4">
        
        <!-- LEFT COLUMN: อุปกรณ์ที่ยืม & อุปกรณ์เสริม -->
        <div class="col-lg-6">
            {{-- CARD: Asset Info --}}
            <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-pc-display text-primary"></i>
                        <span>ข้อมูลอุปกรณ์ที่ยืม (IT Asset)</span>
                    </div>
                    @if($borrow->asset)
                        <a href="{{ route('assets.show', $borrow->asset_id) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 12px; padding: 2px 10px;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ดูครุภัณฑ์
                        </a>
                    @endif
                </div>

                <div class="card-body" style="padding: 20px;">
                    @if($borrow->asset)
                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 60px; height: 60px; border-radius: 14px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">
                                <i class="bi bi-laptop"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 17px; font-weight: 800; color: #0f172a;">{{ $borrow->asset->name }}</div>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                                    <span class="badge bg-light text-dark border">
                                        {{ $borrow->asset->brand }} {{ $borrow->asset->model }}
                                    </span>
                                    <span class="badge bg-primary">รหัส: {{ $borrow->asset->asset_code }}</span>
                                    <span class="badge bg-info text-dark">S/N: {{ $borrow->asset->serial_number ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-top: 16px; font-size: 13px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                <div><strong>ประเภท:</strong> {{ $borrow->asset->deviceType?->name ?? 'อุปกรณ์ IT' }}</div>
                                <div><strong>สถานะครุภัณฑ์:</strong> {{ $borrow->asset->status_label }}</div>
                                <div style="grid-column: 1 / -1;"><strong>สเปคฮาร์ดแวร์:</strong> {{ $borrow->asset->formatted_specs }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted">ไม่พบข้อมูลอุปกรณ์ในระบบ</div>
                    @endif
                </div>
            </div>

            {{-- CARD: Accessories --}}
            <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-box-seam text-purple"></i>
                        <span>รายการอุปกรณ์เสริมและอุปกรณ์ต่อพ่วงที่ยืมไปด้วย</span>
                    </div>
                </div>

                <div class="card-body" style="padding: 20px;">
                    @if(!empty($borrow->accessories))
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @php
                                $accItems = is_array($borrow->accessories) ? $borrow->accessories : array_map('trim', explode(',', $borrow->accessories));
                            @endphp
                            @foreach($accItems as $item)
                                @if(!empty($item))
                                <span style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-weight: 600; font-size: 13px; padding: 6px 12px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-check2"></i> {{ trim($item) }}
                                </span>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted" style="font-size: 13px;">(ไม่มีการระบุอุปกรณ์เสริมเพิ่มเติม ยืมเฉพาะตัวเครื่อง)</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: ข้อมูลผู้ยืม, วันเวลา, และบันทึกส่งมอบ/รับคืน -->
        <div class="col-lg-6">
            {{-- CARD: Requester & Schedule --}}
            <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-person-badge text-info"></i>
                        <span>ข้อมูลผู้ขอยืม & กำหนดการยืม</span>
                    </div>
                </div>

                <div class="card-body" style="padding: 20px;">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 13.5px;">
                        <tr>
                            <td style="width: 140px; color: #64748b; font-weight: 600;">ผู้ขอยืม:</td>
                            <td style="color: #0f172a; font-weight: 700;">{{ $borrow->borrower_name }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">แผนก/กลุ่มงาน:</td>
                            <td style="color: #0f172a;">{{ $borrow->department?->name ?? 'ไม่ระบุ' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">เบอร์โทรศัพท์:</td>
                            <td style="color: #0f172a;">{{ $borrow->contact_phone }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">วันที่ยืม/รับมอบ:</td>
                            <td style="color: #0f172a; font-weight: 700;">
                                {{ $borrow->borrow_date ? $borrow->borrow_date->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">กำหนดส่งคืน:</td>
                            <td style="color: #0f172a; font-weight: 700;">
                                {{ $borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-' }}
                                <span class="badge bg-light text-dark border ms-1">รวม {{ $borrow->duration_days }} วัน</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">วัตถุประสงค์:</td>
                            <td style="color: #1e293b;">{{ $borrow->purpose }}</td>
                        </tr>
                        @if($borrow->location_used)
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">สถานที่ใช้งาน:</td>
                            <td style="color: #1e293b;">{{ $borrow->location_used }}</td>
                        </tr>
                        @endif
                        @if($borrow->notes)
                        <tr>
                            <td style="color: #64748b; font-weight: 600;">หมายเหตุ:</td>
                            <td style="color: #64748b;">{{ $borrow->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- CARD: Handover & Return Records --}}
            <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                    <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-clipboard2-check text-success"></i>
                        <span>บันทึกการส่งมอบและการตรวจรับคืน (Handover & Return Audit)</span>
                    </div>
                </div>

                <div class="card-body" style="padding: 20px;">
                    <div class="row g-3">
                        {{-- การส่งมอบ --}}
                        <div class="col-sm-6" style="border-right: 1px dashed #e2e8f0;">
                            <div style="font-weight: 700; font-size: 13px; color: #0d9488; margin-bottom: 6px;">
                                <i class="bi bi-box-arrow-right me-1"></i>ข้อมูลการส่งมอบ:
                            </div>
                            @if($borrow->dispatched_at)
                                <div style="font-size: 12.5px; color: #1e293b;">
                                    <div><strong>ผู้ส่งมอบ:</strong> {{ $borrow->dispatcher?->name ?? '-' }}</div>
                                    <div><strong>วันที่ส่งมอบ:</strong> {{ $borrow->dispatched_at->format('d/m/Y H:i') }}</div>
                                    <div><strong>สภาพก่อนมอบ:</strong> {{ $borrow->dispatch_condition ?: 'ปกติ' }}</div>
                                </div>
                            @else
                                <div style="font-size: 12.5px; color: #94a3b8; font-style: italic;">ยังไม่ได้ส่งมอบอุปกรณ์</div>
                            @endif
                        </div>

                        {{-- การรับคืน --}}
                        <div class="col-sm-6">
                            <div style="font-weight: 700; font-size: 13px; color: #16a34a; margin-bottom: 6px;">
                                <i class="bi bi-arrow-down-left-circle me-1"></i>ข้อมูลการรับคืน:
                            </div>
                            @if($borrow->status === 'returned' && $borrow->actual_return_date)
                                <div style="font-size: 12.5px; color: #1e293b;">
                                    <div><strong>ผู้รับคืน:</strong> {{ $borrow->receiver?->name ?? '-' }}</div>
                                    <div><strong>วันที่รับคืน:</strong> {{ $borrow->actual_return_date->format('d/m/Y') }}</div>
                                    <div><strong>สภาพหลังคืน:</strong> {{ $borrow->return_condition ?: 'ปกติ สมบูรณ์' }}</div>
                                    @if($borrow->return_notes)
                                        <div style="color: #64748b;"><strong>ข้อสังเกต:</strong> {{ $borrow->return_notes }}</div>
                                    @endif

                                    @if($borrow->asset_id && (str_contains($borrow->return_condition ?? '', 'ชำรุด') || str_contains($borrow->return_condition ?? '', 'เสีย') || str_contains($borrow->return_condition ?? '', 'พัง')))
                                    <div style="margin-top: 10px; padding: 10px 12px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px;">
                                        <div style="font-size: 11.5px; color: #be123c; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i> พบอุปกรณ์ชำรุดจากการใช้งาน
                                        </div>
                                        <a href="{{ route('repairs.create', ['asset_id' => $borrow->asset_id, 'description' => 'ส่งซ่อมหลังรับคืนจากการยืม #' . $borrow->borrow_no . ' (สภาพ: ' . $borrow->return_condition . ($borrow->return_notes ? ' | ' . $borrow->return_notes : '') . ')']) }}" class="btn btn-sm btn-danger w-100" style="font-size: 11.5px; padding: 4px 8px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                            <i class="bi bi-wrench-adjustable"></i> เปิดใบแจ้งซ่อมอุปกรณ์นี้ทันที
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <div style="font-size: 12.5px; color: #94a3b8; font-style: italic;">ยังไม่มีการรับมอบคืน</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- MODAL 1: ส่งมอบอุปกรณ์ (Dispatch Modal) --}}
<div id="dispatchModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; margin: 20px; border: 1px solid #e2e8f0; animation: modalFadeIn 0.2s ease;">
        <div style="padding: 20px 24px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 16px;">บันทึกการส่งมอบอุปกรณ์</div>
                    <div style="font-size: 11.5px; opacity: 0.9;">Handover Equipment & Accessories</div>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('dispatchModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('asset-borrows.dispatch', $borrow->id) }}" method="POST">
            @csrf
            <div style="padding: 24px;">
                <!-- Summary Pill Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">อุปกรณ์:</span>
                        <span class="badge bg-primary" style="font-family: monospace;">{{ $borrow->asset?->asset_code }}</span>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: #0f172a;">
                        {{ $borrow->asset?->name }} ({{ $borrow->asset?->brand }} {{ $borrow->asset?->model }})
                    </div>
                    <div style="font-size: 12.5px; color: #475569; margin-top: 4px;">
                        ส่งมอบให้: <strong>{{ $borrow->borrower_name }}</strong> ({{ $borrow->department?->name }})
                    </div>
                </div>

                @if(!empty($borrow->accessories))
                <div style="margin-bottom: 18px;">
                    <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">
                        <i class="bi bi-check2-all text-primary"></i> รายการอุปกรณ์เสริมที่ต้องส่งมอบ:
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach((array)$borrow->accessories as $acc)
                            <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; font-size: 11.5px; padding: 4px 10px;">
                                ✓ {{ $acc }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        สภาพอุปกรณ์ก่อนส่งมอบ <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="dispatch_condition" class="form-control" value="ปกติ สมบูรณ์ ครบถ้วนตามรายการ" required style="border-radius: 10px; font-size: 13.5px;">
                    <div style="display: flex; gap: 6px; margin-top: 6px;">
                        <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('input[name=dispatch_condition]').value='ปกติ สมบูรณ์ ครบถ้วนตามรายการ'" style="font-size: 11px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px;">
                            ปกติ สมบูรณ์ ครบถ้วน
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('input[name=dispatch_condition]').value='มีรอยขีดข่วนเล็กน้อยตามการใช้งาน ตัวเครื่องทำงานปกติ'" style="font-size: 11px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px;">
                            มีรอยขีดข่วนเล็กน้อย
                        </button>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #166534; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="font-size: 16px;"></i>
                    <span>เมื่อกดยืนยัน ระบบจะปรับสถานะครุภัณฑ์ในคลังเป็น <strong>"กำลังถูกยืมใช้งาน"</strong> ทันที</span>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-light" onclick="document.getElementById('dispatchModal').style.display='none'" style="border-radius: 8px; font-weight: 600;">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none; border-radius: 8px; font-weight: 700; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-check-circle-fill"></i> ยืนยันส่งมอบอุปกรณ์
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: รับคืนอุปกรณ์ (Return Modal) --}}
<div id="returnModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; margin: 20px; border: 1px solid #e2e8f0;">
        <div style="padding: 20px 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-arrow-return-left"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 16px;">บันทึกการรับมอบคืนอุปกรณ์</div>
                    <div style="font-size: 11.5px; opacity: 0.9;">Return Inspection & Inventory Restock</div>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('returnModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('asset-borrows.return', $borrow->id) }}" method="POST">
            @csrf
            <div style="padding: 24px; max-height: 75vh; overflow-y: auto;">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                            วันที่ส่งคืนจริง <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="actual_return_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 10px; font-size: 13.5px;">
                    </div>
                    <div class="col-md-6">
                        <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                            สภาพอุปกรณ์เมื่อรับคืน <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="return_condition" class="form-select" required style="border-radius: 10px; font-size: 13.5px;">
                            <option value="ปกติ สมบูรณ์">✅ ปกติ สมบูรณ์ (ใช้งานได้ตามปกติ)</option>
                            <option value="อุปกรณ์ไม่ครบ">⚠️ อุปกรณ์ไม่ครบ (ขาดสายชาร์จ/เมาส์/อุปกรณ์เสริม)</option>
                            <option value="ชำรุดเสียหาย">❌ ชำรุดเสียหาย (ต้องส่งช่างซ่อมบำรุง)</option>
                        </select>
                    </div>
                </div>

                @if(!empty($borrow->accessories))
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                        <i class="bi bi-card-checklist text-primary"></i> เช็กตรวจรับอุปกรณ์เสริมที่คืน:
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        @foreach((array)$borrow->accessories as $acc)
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #475569; cursor: pointer;">
                                <input type="checkbox" checked style="accent-color: #10b981;">
                                <span>{{ $acc }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        ปรับสถานะอุปกรณ์ในคลังครุภัณฑ์เป็น <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="target_asset_status" class="form-select" required style="border-radius: 10px; font-size: 13.5px;">
                        <option value="spare">เครื่องสำรอง (Spare - พร้อมให้ยืมต่อในคลัง)</option>
                        <option value="active">ใช้งานปกติ (Active - ส่งคืนประจำแผนกเดิม)</option>
                        <option value="repairing">ส่งซ่อมบำรุง (Repairing - ชำรุดเสียหาย)</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        บันทึกข้อสังเกต / หมายเหตุรับคืน
                    </label>
                    <textarea name="return_notes" rows="2" class="form-control" placeholder="บันทึกสภาพเครื่อง อุปกรณ์ที่ขาด หรือข้อสังเกตเพิ่มเติม (ถ้ามี)" style="border-radius: 10px; font-size: 13.5px;"></textarea>
                </div>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-light" onclick="document.getElementById('returnModal').style.display='none'" style="border-radius: 8px; font-weight: 600;">ยกเลิก</button>
                <button type="submit" class="btn btn-success" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 8px; font-weight: 700; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-check-circle-fill"></i> ยืนยันบันทึกรับคืนอุปกรณ์
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: ปฏิเสธคำขอ (Reject Modal) --}}
<div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; margin: 20px; border: 1px solid #e2e8f0;">
        <div style="padding: 18px 24px; background: #ef4444; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div style="font-weight: 800; font-size: 16px;">ปฏิเสธคำขอยืมอุปกรณ์</div>
            </div>
            <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('asset-borrows.reject', $borrow->id) }}" method="POST">
            @csrf
            <div style="padding: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                    ระบุเหตุผลที่ไม่อนุมัติคำขอ <span style="color: #ef4444;">*</span>
                </label>
                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                    <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('textarea[name=reason]').value='อุปกรณ์มีภารกิจอื่นที่จำเป็นในวันดังกล่าว'" style="font-size: 11px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px;">
                        อุปกรณ์ติดภารกิจอื่น
                    </button>
                    <button type="button" class="btn btn-light btn-sm" onclick="document.querySelector('textarea[name=reason]').value='อุปกรณ์อยู่ระหว่างรอการตรวจเช็คสภาพและส่งซ่อมบำรุง'" style="font-size: 11px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 8px;">
                        เครื่องรอตรวจซ่อม
                    </button>
                </div>
                <textarea name="reason" rows="3" class="form-control" placeholder="ระบุเหตุผลที่จำเป็น..." required style="border-radius: 10px; font-size: 13.5px;"></textarea>
            </div>

            <div style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-light" onclick="document.getElementById('rejectModal').style.display='none'" style="border-radius: 8px; font-weight: 600;">ยกเลิก</button>
                <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 700; padding: 8px 20px;">
                    ยืนยันปฏิเสธคำขอ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmApprove() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันอนุมัติคำขอยืมอุปกรณ์?',
                text: 'เมื่ออนุมัติแล้ว จะสามารถดำเนินการส่งมอบอุปกรณ์ให้แก่ผู้ยืมได้ทันที',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ยืนยัน อนุมัติคำขอ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveForm').submit();
                }
            });
        } else {
            if (confirm('ยืนยันการอนุมัติคำขอยืมอุปกรณ์นี้หรือไม่?')) {
                document.getElementById('approveForm').submit();
            }
        }
    }
</script>
@endsection
