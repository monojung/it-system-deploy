@extends('layouts.app')

@section('title', 'รายละเอียดครุภัณฑ์ ' . $asset->asset_code)
@section('page_title', 'ข้อมูลครุภัณฑ์: ' . $asset->asset_code)
@section('page_subtitle', $asset->name . ' - ' . ($asset->department?->name ?? 'ไม่ระบุแผนก'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('assets.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> ทะเบียนครุภัณฑ์
        </a>
        <span class="badge {{ $asset->status_badge }}" style="font-size: 13px; padding: 6px 14px;">
            {{ $asset->status_label }}
        </span>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="{{ route('repairs.create', ['asset_id' => $asset->id]) }}" class="btn btn-primary">
            <i class="bi bi-wrench"></i>
            <span>แจ้งซ่อมเครื่องนี้</span>
        </a>
        <a href="{{ route('assets.label', $asset) }}" target="_blank" class="btn btn-secondary">
            <i class="bi bi-qr-code"></i>
            <span>พิมพ์สติกเกอร์ QR</span>
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
        <a href="{{ route('assets.edit', $asset) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i>
            <span>แก้ไข</span>
        </a>
        <button type="button" class="btn btn-danger" onclick="openDeleteModal('{{ $asset->id }}', '{{ addslashes($asset->asset_code) }}', '{{ addslashes($asset->name) }}', '{{ addslashes($asset->brand . ' ' . $asset->model) }}', '{{ addslashes($asset->department?->name ?? 'ไม่ระบุแผนก') }}', '{{ addslashes($asset->deviceType?->name ?? '-') }}', '{{ addslashes($asset->status_label) }}', '{{ $asset->status_badge }}', {{ (int)$asset->repairs_count }})">
            <i class="bi bi-trash3"></i>
            <span>ลบครุภัณฑ์</span>
        </button>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Asset Info Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-info-circle text-primary"></i>
                <span>ข้อมูลจำเพาะครุภัณฑ์ (Specifications)</span>
            </div>
            <span style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 15px;">
                {{ $asset->asset_code }}
            </span>
        </div>
        <div class="card-body">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: var(--text-main);">
                {{ $asset->name }}
            </h2>
            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">
                {{ $asset->brand }} {{ $asset->model }} &bull; ประเภท: {{ $asset->deviceType?->name }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 12px; color: var(--text-muted);">Serial Number (S/N)</div>
                    <div style="font-weight: 600; font-family: monospace; font-size: 14px; margin-top: 2px;">
                        {{ $asset->serial_number ?? '-' }}
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 12px; color: var(--text-muted);">IP Address</div>
                    <div style="font-weight: 600; font-family: monospace; font-size: 14px; margin-top: 2px;">
                        {{ $asset->ip_address ?? '-' }}
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 12px; color: var(--text-muted);">MAC Address</div>
                    <div style="font-weight: 600; font-family: monospace; font-size: 13.5px; margin-top: 2px;">
                        {{ $asset->mac_address ?? '-' }}
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 12px; color: var(--text-muted);">ปีงบประมาณ</div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 2px;">
                        {{ $asset->budget_year ?? '-' }}
                    </div>
                </div>
            </div>

            <!-- Hardware Specs Detailed Grid -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div style="font-weight: 700; font-size: 14.5px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-cpu-fill text-primary" style="font-size: 18px;"></i>
                        <span>รายละเอียดคุณลักษณะและสเปกเครื่อง (Hardware Specs)</span>
                    </div>
                    @if($asset->is_computer)
                        <span class="badge badge-info" style="font-size: 11.5px; padding: 4px 10px;">
                            <i class="bi bi-pc-display me-1"></i> คอมพิวเตอร์ประมวลผล
                        </span>
                    @endif
                </div>

                @if($asset->cpu_model || $asset->ram_capacity || $asset->os_name || $asset->storage_capacity)
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px;">
                        {{-- 1. CPU Processor --}}
                        <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 14px; position: relative;">
                            <div style="font-size: 11.5px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; margin-bottom: 6px;">
                                <i class="bi bi-cpu"></i> หน่วยประมวลผล (CPU)
                            </div>
                            <div style="font-weight: 700; font-size: 15px; color: #0c4a6e; line-height: 1.3;">
                                {{ $asset->cpu_model ?: 'ไม่ระบุ' }}
                            </div>
                            @if($asset->cpu_speed)
                                <div style="font-size: 12px; color: #0369a1; margin-top: 4px;">
                                    <i class="bi bi-speedometer2 me-1"></i> {{ $asset->cpu_speed }}
                                </div>
                            @endif
                            @if($asset->cpu_model)
                                <div style="margin-top: 10px; border-top: 1px dashed #bae6fd; padding-top: 6px;">
                                    <a href="{{ route('assets.index', ['cpu' => explode(' ', $asset->cpu_model)[2] ?? $asset->cpu_model]) }}" style="font-size: 11px; color: #0284c7; text-decoration: none; font-weight: 600;">
                                        <i class="bi bi-search me-1"></i> ค้นหาเครื่อง CPU นี้
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- 2. RAM Memory --}}
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px; position: relative;">
                            <div style="font-size: 11.5px; font-weight: 700; color: #16a34a; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; margin-bottom: 6px;">
                                <i class="bi bi-memory"></i> หน่วยความจำ (RAM)
                            </div>
                            <div style="font-weight: 700; font-size: 15px; color: #14532d; line-height: 1.3;">
                                {{ $asset->ram_capacity ? $asset->ram_capacity . ' GB' : '-' }}
                                @if($asset->ram_type)
                                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 11px; margin-left: 4px;">
                                        {{ $asset->ram_type }}
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: #15803d; margin-top: 4px;">
                                @if($asset->ram_bus) <span>{{ $asset->ram_bus }}</span> @endif
                                @if($asset->ram_slots) <span>• {{ $asset->ram_slots }}</span> @endif
                            </div>
                            @if($asset->ram_type)
                                <div style="margin-top: 10px; border-top: 1px dashed #bbf7d0; padding-top: 6px;">
                                    <a href="{{ route('assets.index', ['ram_type' => $asset->ram_type]) }}" style="font-size: 11px; color: #16a34a; text-decoration: none; font-weight: 600;">
                                        <i class="bi bi-search me-1"></i> ค้นหาเครื่องที่ใช้ {{ $asset->ram_type }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- 3. Storage Drive --}}
                        <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 12px; padding: 14px; position: relative;">
                            <div style="font-size: 11.5px; font-weight: 700; color: #9333ea; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; margin-bottom: 6px;">
                                <i class="bi bi-device-hdd"></i> ไดรฟ์เก็บข้อมูล (Storage)
                            </div>
                            <div style="font-weight: 700; font-size: 15px; color: #581c87; line-height: 1.3;">
                                {{ $asset->storage_capacity ?: '-' }}
                            </div>
                            <div style="font-size: 12px; color: #7e22ce; margin-top: 4px;">
                                {{ $asset->storage_type ?: 'ฮาร์ดดิสก์/ไดรฟ์' }}
                                @if($asset->storage_second)
                                    <div style="font-size: 11px; margin-top: 2px; color: #6b21a8;">+ {{ $asset->storage_second }}</div>
                                @endif
                            </div>
                            @if($asset->storage_type)
                                <div style="margin-top: 10px; border-top: 1px dashed #e9d5ff; padding-top: 6px;">
                                    <a href="{{ route('assets.index', ['storage_type' => $asset->storage_type]) }}" style="font-size: 11px; color: #9333ea; text-decoration: none; font-weight: 600;">
                                        <i class="bi bi-search me-1"></i> ค้นหาเครื่องชนิดไดรฟ์นี้
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- 4. Operating System (OS) & License --}}
                        <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 14px; position: relative;">
                            <div style="font-size: 11.5px; font-weight: 700; color: #0d9488; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; margin-bottom: 6px;">
                                <i class="bi bi-windows"></i> ระบบปฏิบัติการ (OS)
                            </div>
                            <div style="font-weight: 700; font-size: 15px; color: #134e4a; line-height: 1.3;">
                                {{ $asset->os_name ?: 'ไม่ระบุ OS' }}
                            </div>
                            @if($asset->os_license)
                                <div style="font-size: 12px; color: #0f766e; margin-top: 4px;">
                                    <i class="bi bi-patch-check me-1"></i> {{ $asset->os_license }}
                                </div>
                            @endif
                            @if($asset->os_name)
                                <div style="margin-top: 10px; border-top: 1px dashed #99f6e4; padding-top: 6px;">
                                    <a href="{{ route('assets.index', ['os' => $asset->os_name]) }}" style="font-size: 11px; color: #0d9488; text-decoration: none; font-weight: 600;">
                                        <i class="bi bi-search me-1"></i> ค้นหาเครื่องที่ใช้ {{ $asset->os_name }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Graphics & Monitor Secondary Details --}}
                    @if($asset->gpu_model || $asset->monitor_size)
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 13px;">
                            @if($asset->gpu_model)
                                <div>
                                    <strong style="color: #475569;"><i class="bi bi-gpu-card text-primary me-1"></i> การ์ดแสดงผล:</strong>
                                    <span>{{ $asset->gpu_model }}</span>
                                </div>
                            @endif
                            @if($asset->monitor_size)
                                <div>
                                    <strong style="color: #475569;"><i class="bi bi-display text-info me-1"></i> ขนาดจอภาพ:</strong>
                                    <span>{{ $asset->monitor_size }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                {{-- Full Specs / Remarks Text --}}
                @if($asset->specs)
                    <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; font-size: 13.5px; line-height: 1.5; color: #334155;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">รายละเอียดสเปกเพิ่มเติม:</div>
                        <div style="white-space: pre-line;">{{ $asset->specs }}</div>
                    </div>
                @elseif(!$asset->cpu_model && !$asset->ram_capacity)
                    <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 14px; font-size: 14px; color: var(--text-muted);">
                        ไม่มีข้อมูลสเปกฮาร์ดแวร์เพิ่มเติม
                    </div>
                @endif
            </div>

            @if($asset->notes)
            <div>
                <div style="font-weight: 600; font-size: 13.5px; color: var(--text-main); margin-bottom: 4px;">หมายเหตุ:</div>
                <div style="font-size: 13px; color: var(--text-muted);">{{ $asset->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Side: Location, Custodian, Financial -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-geo-alt text-danger"></i>
                    <span>สถานที่ตั้ง & ผู้ครอบครอง</span>
                </div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted);">แผนก / หน่วยงาน:</div>
                    <div style="font-weight: 600; font-size: 14.5px;">{{ $asset->department?->name ?? 'ไม่ระบุ' }}</div>
                    <div style="font-size: 12px; color: #64748b;">{{ $asset->department?->building }}</div>
                </div>

                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted);">จุดที่ตั้ง / ห้องทำงาน:</div>
                    <div style="font-weight: 600; font-size: 14px;">{{ $asset->location_detail ?? '-' }}</div>
                </div>

                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted);">ผู้ครอบครอง / ผู้ดูแลเครื่อง:</div>
                    <div style="font-weight: 600; font-size: 14px; color: #0f766e;">{{ $asset->custodian_name ?? '-' }}</div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border); margin: 16px 0;">

                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted);">วันที่จัดซื้อ / ตรวจรับ:</div>
                    <div style="font-weight: 500; font-size: 13.5px;">
                        {{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; color: var(--text-muted);">ราคาจัดซื้อ:</div>
                    <div style="font-weight: 700; font-size: 15px; color: #0284c7;">
                        {{ $asset->price ? number_format($asset->price, 2) . ' บาท' : '-' }}
                    </div>
                </div>

                <div>
                    <div style="font-size: 12px; color: var(--text-muted);">การรับประกัน (Warranty):</div>
                    @if($asset->warranty_expire_date)
                        @php $isExpired = $asset->warranty_expire_date->isPast(); @endphp
                        <div style="font-weight: 600; font-size: 13.5px; color: {{ $isExpired ? '#dc2626' : '#16a34a' }};">
                            หมดประกัน: {{ $asset->warranty_expire_date->format('d/m/Y') }}
                            <span class="badge {{ $isExpired ? 'badge-danger' : 'badge-success' }}" style="margin-left: 6px;">
                                {{ $isExpired ? 'หมดประกันแล้ว' : 'อยู่ในประกัน' }}
                            </span>
                        </div>
                    @else
                        <div style="font-size: 13px; color: #94a3b8;">ไม่ระบุวันหมดประกัน</div>
                    @endif
                </div>
            </div>
        </div>

        @if($asset->image)
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-image"></i>
                    <span>รูปภาพครุภัณฑ์</span>
                </div>
            </div>
            <div class="card-body" style="text-align: center;">
                <img src="{{ asset($asset->image) }}" alt="รูปครุภัณฑ์" style="max-width: 100%; max-height: 240px; border-radius: 8px;">
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Asset Repair History Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-clock-history text-primary"></i>
            <span>ประวัติการแจ้งซ่อมบำรุงของเครื่องนี้ ({{ $asset->repairs->count() }} ครั้ง)</span>
        </div>
        <a href="{{ route('repairs.create', ['asset_id' => $asset->id]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> แจ้งซ่อมเครื่องนี้
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่ใบแจ้งซ่อม</th>
                        <th>อาการเสีย / หัวข้อ</th>
                        <th>ระดับความเร่งด่วน</th>
                        <th>สถานะ</th>
                        <th>ช่างผู้ดำเนินการ</th>
                        <th>ค่าใช้จ่าย (บาท)</th>
                        <th>วันที่แจ้ง</th>
                        <th style="text-align: center;">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asset->repairs as $repair)
                    <tr>
                        <td>
                            <a href="{{ route('repairs.show', $repair) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                {{ $repair->ticket_number }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $repair->title }}</div>
                            @if($repair->solution)
                            <div style="font-size: 12px; color: #065f46;">วิธีแก้: {{ $repair->solution }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $repair->urgency_badge }}">
                                {{ $repair->urgency_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $repair->status_badge }}">
                                {{ $repair->status_label }}
                            </span>
                        </td>
                        <td style="font-size: 13px;">{{ $repair->technician?->name ?? 'รอรับเรื่อง' }}</td>
                        <td style="font-weight: 600;">{{ number_format($repair->total_cost, 2) }}</td>
                        <td style="font-size: 12.5px; color: var(--text-muted);">
                            {{ $repair->created_at->format('d/m/Y') }}
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            ไม่มีประวัติการส่งซ่อมสำหรับครุภัณฑ์นี้ (อุปกรณ์ทำงานปกติ)
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Asset Deletion Confirmation Modal -->
<div id="deleteAssetModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 520px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); overflow: hidden; animation: modalScaleIn 0.22s ease-out;">
        {{-- Modal Header --}}
        <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 20px 24px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <div>
                    <div style="font-size: 16.5px; font-weight: 700; letter-spacing: -0.2px;">ยืนยันการลบรายการครุภัณฑ์</div>
                    <div style="font-size: 12px; color: rgba(255, 255, 255, 0.85);">Asset Deletion Confirmation & Safety Check</div>
                </div>
            </div>
            <button type="button" onclick="closeDeleteModal()" style="background: transparent; border: none; color: rgba(255, 255, 255, 0.8); font-size: 22px; cursor: pointer;" title="ปิดหน้าต่าง">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding: 24px;">
            {{-- Asset Summary Card --}}
            <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                    <span id="del_asset_code" style="font-family: monospace; font-weight: 700; font-size: 14px; color: #0284c7; background: #e0f2fe; padding: 4px 10px; border-radius: 6px; border: 1px solid #bae6fd;">-</span>
                    <span id="del_asset_status" class="badge badge-secondary">-</span>
                </div>
                <div style="font-weight: 700; font-size: 14.5px; color: #0f172a; margin-bottom: 8px;" id="del_asset_name">-</div>
                <div style="display: flex; font-size: 13px; color: #475569; margin-bottom: 6px; gap: 8px;">
                    <strong style="color: #1e293b; min-width: 90px;">ยี่ห้อ / รุ่น:</strong>
                    <span id="del_asset_brand_model">-</span>
                </div>
                <div style="display: flex; font-size: 13px; color: #475569; margin-bottom: 6px; gap: 8px;">
                    <strong style="color: #1e293b; min-width: 90px;">แผนก / ที่ตั้ง:</strong>
                    <span id="del_asset_dept">-</span>
                </div>
                <div style="display: flex; font-size: 13px; color: #475569; margin-bottom: 0; gap: 8px;">
                    <strong style="color: #1e293b; min-width: 90px;">ประเภท:</strong>
                    <span id="del_asset_type">-</span>
                </div>
            </div>

            {{-- Case 1: Cannot Delete Due to Repair Records --}}
            <div id="del_blocked_section" style="display: none;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="bi bi-shield-exclamation text-warning" style="font-size: 20px; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="color: #92400e; font-size: 14px;">ไม่สามารถลบครุภัณฑ์นี้ได้</strong>
                            <div style="margin-top: 4px; color: #78350f; font-size: 13.5px; line-height: 1.5;">
                                ครุภัณฑ์นี้มีประวัติการแจ้งซ่อมในระบบจำนวน <strong id="del_repair_count_num" style="color: #b45309; font-size: 15px;">0</strong> รายการ เพื่อรักษาประวัติการซ่อมบำรุงตามระเบียบงานสารสนเทศโรงพยาบาล
                            </div>
                            <div style="margin-top: 8px; font-size: 12.5px; color: #92400e; background: rgba(245, 158, 11, 0.12); padding: 8px 12px; border-radius: 8px;">
                                💡 <strong>คำแนะนำ:</strong> หากอุปกรณ์ใช้งานไม่ได้ ท่านสามารถเข้าไป <strong>"แก้ไขข้อมูล"</strong> แล้วเปลี่ยนสถานะเป็น <strong>"ชำรุดรอซ่อม"</strong> หรือ <strong>"รอจำหน่าย/แทงจำหน่าย"</strong> แทนการลบ
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                        ปิดหน้าต่าง
                    </button>
                </div>
            </div>

            {{-- Case 2: Can Delete Safely (0 Repairs) --}}
            <div id="del_allowed_section" style="display: none;">
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; color: #991b1b; font-size: 13.5px; line-height: 1.5;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="font-size: 14px;">คำเตือน: ยืนยันการลบถาวร</strong>
                            <div style="margin-top: 4px;">
                                ข้อมูลครุภัณฑ์และประวัติทั้งหมดจะถูกลบออกจากระบบ และไม่สามารถย้อนคืนได้
                            </div>
                            <div style="font-size: 12px; margin-top: 6px; color: #991b1b;">
                                🛡️ ระบบจะทำการบันทึกข้อมูลการลบนี้ลงใน <strong>Audit Log</strong> เพื่อการตรวจสอบย้อนหลัง
                            </div>
                        </div>
                    </div>
                </div>

                <form id="deleteAssetForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-danger" style="box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
                            <i class="bi bi-trash3-fill"></i>
                            <span>ยืนยันลบครุภัณฑ์นี้</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeleteModal(id, code, name, brandModel, dept, type, statusLabel, statusBadge, repairsCount) {
        document.getElementById('del_asset_code').textContent = code;
        document.getElementById('del_asset_name').textContent = name;
        document.getElementById('del_asset_brand_model').textContent = brandModel && brandModel.trim() ? brandModel : '-';
        document.getElementById('del_asset_dept').textContent = dept;
        document.getElementById('del_asset_type').textContent = type;

        const statusElem = document.getElementById('del_asset_status');
        statusElem.textContent = statusLabel;
        statusElem.className = 'badge ' + statusBadge;

        if (repairsCount > 0) {
            document.getElementById('del_repair_count_num').textContent = repairsCount;
            document.getElementById('del_blocked_section').style.display = 'block';
            document.getElementById('del_allowed_section').style.display = 'none';
        } else {
            document.getElementById('deleteAssetForm').action = '/assets/' + id;
            document.getElementById('del_blocked_section').style.display = 'none';
            document.getElementById('del_allowed_section').style.display = 'block';
        }

        const modal = document.getElementById('deleteAssetModal');
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteAssetModal');
        modal.style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteAssetModal');
        if (e.target === modal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endpush
