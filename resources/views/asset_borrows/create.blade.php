@extends('layouts.app')

@section('title', 'ยื่นคำขอยืมอุปกรณ์คอมพิวเตอร์และพัสดุไอที')
@section('page_title', 'ยื่นคำขอยืมอุปกรณ์คอมพิวเตอร์และพัสดุไอที')
@section('page_subtitle', 'เลือกอุปกรณ์จากคลังคอมพิวเตอร์และครุภัณฑ์ IT โรงพยาบาลทุ่งหัวช้าง')

@section('content')
<div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div class="header-left" style="display: flex; align-items: center; gap: 14px;">
        <div class="header-icon" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.3);">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="header-title" style="font-size: 21px; font-weight: 800; color: #0f172a; margin: 0;">แบบฟอร์มขอยืมอุปกรณ์ / ครุภัณฑ์คอมพิวเตอร์</h1>
                <span style="background: #e0f2fe; color: #0369a1; font-size: 11.5px; font-weight: 700; padding: 2px 10px; border-radius: 20px; border: 1px solid #bae6fd;">
                    พร้อมให้ยืม {{ $availableAssets->count() }} ชิ้น
                </span>
            </div>
            <p class="header-subtitle" style="font-size: 13px; color: #64748b; margin: 4px 0 0;">
                ระบบบริการยืมเครื่องคอมพิวเตอร์ โน้ตบุ๊ก โปรเจคเตอร์ และอุปกรณ์ต่อพ่วง โรงพยาบาลทุ่งหัวช้าง
            </p>
        </div>
    </div>
    <div class="header-right">
        <a href="{{ route('asset-borrows.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px;">
            <i class="bi bi-arrow-left"></i> ย้อนกลับหน้ารายการยืม-คืน
        </a>
    </div>
</div>

<div class="content-container" style="max-width: 1020px; margin: 0 auto; padding-bottom: 90px;">

    <!-- Step Tracker Bar -->
    <div style="background: #ffffff; border-radius: 14px; border: 1px solid #e2e8f0; padding: 14px 20px; margin-bottom: 24px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: #0d9488; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">1</div>
            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">เลือกอุปกรณ์</span>
        </div>
        <div style="height: 2px; width: 30px; background: #cbd5e1;" class="d-none d-md-block"></div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">2</div>
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">ข้อมูลผู้ขอยืม</span>
        </div>
        <div style="height: 2px; width: 30px; background: #cbd5e1;" class="d-none d-md-block"></div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">3</div>
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">กำหนดเวลา & วัตถุประสงค์</span>
        </div>
        <div style="height: 2px; width: 30px; background: #cbd5e1;" class="d-none d-md-block"></div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">4</div>
            <span style="font-size: 13px; font-weight: 600; color: #64748b;">อุปกรณ์เสริม & ยืนยัน</span>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.08);">
        <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-exclamation-octagon-fill" style="font-size: 18px;"></i>
            <span>โปรดตรวจสอบข้อมูลต่อไปนี้ก่อนดำเนินการ:</span>
        </div>
        <ul style="margin: 0; padding-left: 24px; font-size: 13.5px; line-height: 1.6;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('asset-borrows.store') }}" method="POST" id="borrowForm">
        @csrf

        @if(isset($linkedRepair) && $linkedRepair)
            <input type="hidden" name="repair_id" value="{{ $linkedRepair->id }}">
            <div style="background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%); border: 1.5px solid #38bdf8; border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 11.5px; font-weight: 800; text-transform: uppercase; background: #0284c7; color: #ffffff; padding: 2px 8px; border-radius: 6px;">
                                ขอยืมเครื่องสำรองสำหรับงานซ่อม
                            </span>
                            <span class="badge bg-primary" style="font-family: monospace;">#{{ $linkedRepair->ticket_number }}</span>
                        </div>
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 4px;">
                            {{ $linkedRepair->title }}
                        </div>
                        <div style="font-size: 12px; color: #475569; margin-top: 2px;">
                            ผู้แจ้ง: <strong>{{ $linkedRepair->requester_name }}</strong> &bull; แผนก: <strong>{{ $linkedRepair->department?->name }}</strong>
                            @if($linkedRepair->asset)
                                &bull; อุปกรณ์ที่ส่งซ่อม: <strong>{{ $linkedRepair->asset->name }} ({{ $linkedRepair->asset->asset_code }})</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('repairs.show', $linkedRepair->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 12px; font-weight: 600;">
                    <i class="bi bi-box-arrow-up-right me-1"></i> ดูใบแจ้งซ่อม
                </a>
            </div>
        @endif

        <!-- CARD 1: เลือกอุปกรณ์จากคลังครุภัณฑ์ IT -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 9px; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                        <i class="bi bi-pc-display"></i>
                    </div>
                    <span>1. เลือกอุปกรณ์ที่ต้องการยืมจากคลัง (Equipment Selection)</span>
                </div>
                <span style="font-size: 12px; color: #64748b;">คลิกการ์ดเพื่อเลือกอุปกรณ์ หรือค้นหาด้านล่าง</span>
            </div>

            <div class="card-body" style="padding: 22px;">

                <!-- Selected Device Showcase Card (Shown when an asset is selected) -->
                <div id="selectedAssetShowcase" style="display: none; margin-bottom: 20px; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); border: 1.5px solid #0d9488; border-radius: 14px; padding: 18px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.12); position: relative; transition: all 0.2s;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div id="showcaseIconWrapper" style="width: 52px; height: 52px; border-radius: 12px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(13, 148, 136, 0.3);">
                                <i class="bi bi-laptop" id="showcaseIcon"></i>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800; color: #0f766e; background: #ccfbf1; padding: 2px 8px; border-radius: 6px;">
                                        ✓ อุปกรณ์ที่เลือกยืม
                                    </span>
                                    <span class="badge bg-primary" id="showcaseCode" style="font-size: 12px; font-family: monospace;">-</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" id="showcaseStatus" style="font-size: 11.5px;">พร้อมใช้งาน</span>
                                </div>
                                <div id="showcaseName" style="font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 4px;">-</div>
                                <div id="showcaseBrandModel" style="font-size: 13.5px; color: #334155; font-weight: 600;">-</div>
                                <div id="showcaseSerial" style="font-size: 12px; color: #64748b; margin-top: 2px; font-family: monospace;">-</div>
                            </div>
                        </div>

                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="scrollToDevicePicker()" style="border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-arrow-repeat"></i> เปลี่ยนเครื่องที่เลือก
                            </button>
                            <span id="showcaseDept" style="font-size: 12px; color: #475569; background: rgba(255,255,255,0.8); padding: 3px 8px; border-radius: 6px; border: 1px solid #cbd5e1;">-</span>
                        </div>
                    </div>

                    <!-- Specs Breakdown -->
                    <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed rgba(13, 148, 136, 0.3); font-size: 12.5px; color: #1e293b; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span style="font-weight: 700; color: #0f766e;"><i class="bi bi-cpu"></i> ข้อมูลสเปค:</span>
                        <span id="showcaseSpecs" style="color: #334155;">-</span>
                    </div>
                </div>

                <!-- Synced Main Select for Form Submission -->
                <select name="asset_id" id="asset_id" style="display: none;" required onchange="syncSelectedAssetFromSelect(this)">
                    <option value="">-- กรุณาเลือกอุปกรณ์คอมพิวเตอร์ / พัสดุ IT --</option>
                    @foreach($availableAssets as $asset)
                        @php
                            $cat = 'other';
                            $devTypeName = mb_strtolower($asset->deviceType?->name ?? '');
                            $assetName = mb_strtolower($asset->name . ' ' . $asset->model);
                            if (str_contains($devTypeName, 'notebook') || str_contains($devTypeName, 'laptop') || str_contains($assetName, 'notebook') || str_contains($assetName, 'laptop') || str_contains($assetName, 'โน้ตบุ๊ก')) {
                                $cat = 'notebook';
                            } elseif (str_contains($devTypeName, 'pc') || str_contains($devTypeName, 'desktop') || str_contains($devTypeName, 'all in one') || str_contains($assetName, 'pc') || str_contains($assetName, 'คอมพิวเตอร์') || str_contains($assetName, 'all-in-one')) {
                                $cat = 'desktop';
                            } elseif (str_contains($devTypeName, 'projector') || str_contains($assetName, 'projector') || str_contains($assetName, 'โปรเจคเตอร์') || str_contains($assetName, 'จอ')) {
                                $cat = 'projector';
                            }
                        @endphp
                        <option value="{{ $asset->id }}" 
                                data-category="{{ $cat }}"
                                data-name="{{ $asset->name }}"
                                data-code="{{ $asset->asset_code }}"
                                data-brand="{{ $asset->brand }}"
                                data-model="{{ $asset->model }}"
                                data-serial="{{ $asset->serial_number }}"
                                data-specs="{{ $asset->formatted_specs }}"
                                data-status="{{ $asset->status_label }}"
                                data-dept="{{ $asset->department?->name ?? 'คลังส่วนกลาง' }}"
                                {{ old('asset_id', $selectedAssetId) == $asset->id ? 'selected' : '' }}>
                            [{{ $asset->status_label }}] {{ $asset->name }} - {{ $asset->brand }} {{ $asset->model }} (รหัส: {{ $asset->asset_code }})
                        </option>
                    @endforeach
                </select>

                <!-- Filter & Search Controls -->
                <div id="devicePickerContainer">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                        <!-- Category Filter Tabs -->
                        <div class="category-pills-wrap" style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-category active" data-filter="all" onclick="filterDeviceCategory('all', this)" style="border-radius: 20px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;">
                                ทั้งหมด ({{ $availableAssets->count() }})
                            </button>
                            <button type="button" class="btn btn-sm btn-category" data-filter="notebook" onclick="filterDeviceCategory('notebook', this)" style="border-radius: 20px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;">
                                💻 โน้ตบุ๊ก (Notebooks)
                            </button>
                            <button type="button" class="btn btn-sm btn-category" data-filter="desktop" onclick="filterDeviceCategory('desktop', this)" style="border-radius: 20px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;">
                                🖥️ คอมพิวเตอร์ (PC)
                            </button>
                            <button type="button" class="btn btn-sm btn-category" data-filter="projector" onclick="filterDeviceCategory('projector', this)" style="border-radius: 20px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;">
                                📽️ โปรเจคเตอร์ & จอภาพ
                            </button>
                            <button type="button" class="btn btn-sm btn-category" data-filter="other" onclick="filterDeviceCategory('other', this)" style="border-radius: 20px; font-weight: 600; padding: 6px 14px; font-size: 12.5px;">
                                📦 อุปกรณ์อื่นๆ
                            </button>
                        </div>

                        <!-- Live Search Input -->
                        <div style="position: relative; min-width: 240px; flex: 1; max-width: 320px;">
                            <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                            <input type="text" id="assetSearchInput" class="form-control form-control-sm" placeholder="ค้นหาชื่อ, รหัส, สเปค, ยี่ห้อ..." oninput="filterDeviceCards()" style="padding-left: 34px; border-radius: 20px; height: 34px; font-size: 12.5px;">
                        </div>
                    </div>

                    <!-- Device Cards Grid -->
                    <div id="deviceCardGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 14px; max-height: 380px; overflow-y: auto; padding: 4px; border: 1px solid #f1f5f9; border-radius: 12px; background: #f8fafc;">
                        @forelse($availableAssets as $asset)
                            @php
                                $cat = 'other';
                                $icon = 'bi-box-seam';
                                $devTypeName = mb_strtolower($asset->deviceType?->name ?? '');
                                $assetName = mb_strtolower($asset->name . ' ' . $asset->model);
                                if (str_contains($devTypeName, 'notebook') || str_contains($devTypeName, 'laptop') || str_contains($assetName, 'notebook') || str_contains($assetName, 'laptop') || str_contains($assetName, 'โน้ตบุ๊ก')) {
                                    $cat = 'notebook';
                                    $icon = 'bi-laptop';
                                } elseif (str_contains($devTypeName, 'pc') || str_contains($devTypeName, 'desktop') || str_contains($devTypeName, 'all in one') || str_contains($assetName, 'pc') || str_contains($assetName, 'คอมพิวเตอร์') || str_contains($assetName, 'all-in-one')) {
                                    $cat = 'desktop';
                                    $icon = 'bi-display';
                                } elseif (str_contains($devTypeName, 'projector') || str_contains($assetName, 'projector') || str_contains($assetName, 'โปรเจคเตอร์') || str_contains($assetName, 'จอ')) {
                                    $cat = 'projector';
                                    $icon = 'bi-projector';
                                }
                            @endphp
                            <div class="device-card {{ old('asset_id', $selectedAssetId) == $asset->id ? 'selected-card' : '' }}" 
                                 id="device-card-{{ $asset->id }}"
                                 data-id="{{ $asset->id }}"
                                 data-category="{{ $cat }}"
                                 data-search="{{ mb_strtolower($asset->name . ' ' . $asset->brand . ' ' . $asset->model . ' ' . $asset->asset_code . ' ' . $asset->serial_number . ' ' . $asset->formatted_specs . ' ' . ($asset->department?->name ?? '')) }}"
                                 onclick="selectAssetCard('{{ $asset->id }}')"
                                 style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px; cursor: pointer; transition: all 0.2s ease; position: relative;">
                                
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="device-card-icon" style="width: 38px; height: 38px; border-radius: 10px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                            <i class="bi {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.3;" title="{{ $asset->name }}">
                                                {{ Str::limit($asset->name, 28) }}
                                            </div>
                                            <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                                {{ $asset->brand }} {{ $asset->model }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="check-indicator" style="width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: center; color: transparent; font-size: 12px; transition: all 0.2s;">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                </div>

                                <div style="margin-top: 10px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-family: monospace; font-size: 11px; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 5px; font-weight: 600;">
                                        {{ $asset->asset_code }}
                                    </span>
                                    @if($asset->status === 'spare')
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 10.5px;">เครื่องสำรอง</span>
                                    @else
                                        <span class="badge" style="background: #f0fdf4; color: #16a34a; font-size: 10.5px;">พร้อมใช้งาน</span>
                                    @endif
                                </div>

                                @if($asset->formatted_specs)
                                <div style="margin-top: 8px; font-size: 11.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $asset->formatted_specs }}">
                                    <i class="bi bi-cpu" style="font-size: 11px;"></i> {{ Str::limit($asset->formatted_specs, 45) }}
                                </div>
                                @endif

                                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #f1f5f9; font-size: 11px; color: #94a3b8; display: flex; justify-content: space-between; align-items: center;">
                                    <span><i class="bi bi-geo-alt"></i> {{ $asset->department?->name ?? 'คลังส่วนกลาง' }}</span>
                                    <span style="color: #0d9488; font-weight: 600;">เลือกเครื่องนี้ &rarr;</span>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; padding: 30px; text-align: center; color: #64748b;">
                                <i class="bi bi-inbox" style="font-size: 32px; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                                ไม่พบอุปกรณ์ที่พร้อมให้ยืมในขณะนี้
                            </div>
                        @endforelse
                    </div>

                    <div id="noSearchMatchMsg" style="display: none; padding: 24px; text-align: center; color: #64748b; background: #ffffff; border-radius: 10px; border: 1px dashed #cbd5e1; margin-top: 10px;">
                        <i class="bi bi-search" style="font-size: 24px; color: #94a3b8; display: block; margin-bottom: 6px;"></i>
                        ไม่พบอุปกรณ์ที่ตรงกับคำค้นหา โปรดลองใช้คำค้นอื่น หรือเลือกหมวดหมู่อื่น
                    </div>
                </div>

            </div>
        </div>

        <!-- CARD 2: ข้อมูลผู้ขอยืม & แผนก -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <span>2. ข้อมูลผู้ขอยืมและหน่วยงาน (Requester Information)</span>
                </div>
                @if($user)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="resetUserAutofill()" style="border-radius: 8px; font-size: 11.5px; font-weight: 600; padding: 3px 10px;">
                    <i class="bi bi-arrow-clockwise"></i> ใช้ข้อมูลของฉัน
                </button>
                @endif
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="borrower_name" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                            <i class="bi bi-person text-primary"></i>
                            <span>ชื่อ-สกุล ผู้ขอยืม</span>
                            <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="borrower_name" id="borrower_name" class="form-control" 
                               value="{{ old('borrower_name', $prefillBorrower['name'] ?? ($user ? $user->name : '')) }}" required placeholder="เช่น นพ.สมชาย ใจดี / พว.สุดา สุขสันต์" style="border-radius: 10px; font-size: 13.5px;">
                    </div>

                    <div class="col-md-4">
                        <label for="department_id" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                            <i class="bi bi-building text-info"></i>
                            <span>กลุ่มงาน / แผนกที่ขอยืม</span>
                            <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="department_id" id="department_id" class="form-select" required style="border-radius: 10px; font-size: 13.5px;">
                            <option value="">-- เลือกกลุ่มงาน/แผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $prefillBorrower['department_id'] ?? ($user ? $user->department_id : '')) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="contact_phone" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                            <i class="bi bi-telephone text-success"></i>
                            <span>เบอร์โทรศัพท์ติดต่อ</span>
                            <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" 
                               value="{{ old('contact_phone', $prefillBorrower['phone'] ?? ($user ? $user->phone : '')) }}" required placeholder="เช่น 081-234-5678 / เบอร์ภายใน" style="border-radius: 10px; font-size: 13.5px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: กำหนดเวลาและวัตถุประสงค์การยืม -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 9px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <span>3. กำหนดเวลาและวัตถุประสงค์การนำไปใช้งาน (Schedule & Purpose)</span>
                </div>
                <div id="durationBadge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 700; padding: 3px 12px; border-radius: 20px;">
                    ระยะเวลายืม: 1 วัน
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <!-- Quick Duration Presets -->
                <div style="margin-bottom: 16px;">
                    <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 8px;">ปุ่มลัดระยะเวลา:</span>
                    <div style="display: inline-flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-outline-secondary btn-sm duration-btn" onclick="setDuration(1)" style="border-radius: 8px; font-size: 12px; padding: 4px 10px;">
                            ⚡ ยืม 1 วัน (คืนพรุ่งนี้)
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm duration-btn" onclick="setDuration(3)" style="border-radius: 8px; font-size: 12px; padding: 4px 10px;">
                            📅 ยืม 3 วัน
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm duration-btn" onclick="setDuration(7)" style="border-radius: 8px; font-size: 12px; padding: 4px 10px;">
                            🗓️ ยืม 7 วัน (1 สัปดาห์)
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm duration-btn" onclick="setDuration(14)" style="border-radius: 8px; font-size: 12px; padding: 4px 10px;">
                            ⏳ ยืม 14 วัน (2 สัปดาห์)
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="borrow_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                            วันที่ต้องการยืม / รับมอบเครื่อง <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="borrow_date" id="borrow_date" class="form-control" 
                               value="{{ old('borrow_date', date('Y-m-d')) }}" required onchange="calculateDuration()" style="border-radius: 10px; font-size: 13.5px;">
                    </div>

                    <div class="col-md-6">
                        <label for="expected_return_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                            กำหนดวันที่ส่งคืนอุปกรณ์ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="expected_return_date" id="expected_return_date" class="form-control" 
                               value="{{ old('expected_return_date', date('Y-m-d', strtotime('+1 day'))) }}" required onchange="calculateDuration()" style="border-radius: 10px; font-size: 13.5px;">
                    </div>
                </div>

                <div class="mb-3">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="purpose" style="font-size: 13px; font-weight: 600; color: #334155; margin: 0;">
                            วัตถุประสงค์การขอยืมใช้งาน <span style="color: #ef4444;">*</span>
                        </label>
                        <span style="font-size: 11.5px; color: #94a3b8;">คลิกข้อความสำเร็จรูปเพื่อกรอกด่วน</span>
                    </div>

                    <!-- Purpose Suggestions -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                        <button type="button" class="btn btn-light btn-sm" onclick="setPurpose('ใช้สำหรับการประชุมคณะกรรมการและผู้บริหาร รพ.')" style="border: 1px solid #e2e8f0; font-size: 11.5px; border-radius: 6px; padding: 2px 8px; color: #475569;">
                            📋 ประชุมคณะกรรมการ/ผู้บริหาร
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="setPurpose('ออกหน่วยบริการแพทย์เคลื่อนที่ พอ.สว. อ.ทุ่งหัวช้าง')" style="border: 1px solid #e2e8f0; font-size: 11.5px; border-radius: 6px; padding: 2px 8px; color: #475569;">
                            🚑 ออกหน่วยแพทย์เคลื่อนที่/พอ.สว.
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="setPurpose('จัดโครงการฝึกอบรมการใช้งานระบบสารสนเทศโรงพยาบาล')" style="border: 1px solid #e2e8f0; font-size: 11.5px; border-radius: 6px; padding: 2px 8px; color: #475569;">
                            🎓 จัดฝึกอบรมระบบสารสนเทศ
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="setPurpose('ใช้งานชั่วคราวระหว่างรอส่งซ่อมเครื่องคอมพิวเตอร์ประจำแผนก')" style="border: 1px solid #e2e8f0; font-size: 11.5px; border-radius: 6px; padding: 2px 8px; color: #475569;">
                            💻 ใช้งานทดแทนเครื่องส่งซ่อม
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="setPurpose('นำเสนอผลงานวิชาการและจัดนิทรรศการสุขภาพ')" style="border: 1px solid #e2e8f0; font-size: 11.5px; border-radius: 6px; padding: 2px 8px; color: #475569;">
                            📊 นำเสนอผลงาน/นิทรรศการ
                        </button>
                    </div>

                    <textarea name="purpose" id="purpose" rows="2" class="form-control" required placeholder="ระบุเหตุผลและภารกิจที่นำอุปกรณ์ไปใช้งาน..." style="border-radius: 10px; font-size: 13.5px;">{{ old('purpose', $prefillBorrower['purpose'] ?? '') }}</textarea>
                </div>

                <div class="mb-0">
                    <label for="location_used" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        สถานที่นำไปใช้งาน
                    </label>
                    <input type="text" name="location_used" id="location_used" class="form-control" 
                           value="{{ old('location_used', $prefillBorrower['location'] ?? '') }}" placeholder="เช่น ห้องประชุม 1 อาคารอำนวยการ / รพ.สต.บ้านทุ่งหัวช้าง / แผนกผู้ป่วยนอก" style="border-radius: 10px; font-size: 13.5px;">
                </div>
            </div>
        </div>

        <!-- CARD 4: อุปกรณ์เสริมที่ขอยืมไปด้วย (Accessories) -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 9px; background: #f3e8ff; color: #7e22ce; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span>4. อุปกรณ์เสริมและอุปกรณ์ต่อพ่วงที่ต้องการยืม (Accessories Checklist)</span>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectStandardAccessories()" style="border-radius: 8px; font-size: 11.5px; font-weight: 600; padding: 3px 10px;">
                        ⚡ เลือกชุดมาตรฐาน (Adapter + กระเป๋า + เมาส์)
                    </button>
                    <button type="button" class="btn btn-light btn-sm" onclick="clearAccessories()" style="border: 1px solid #e2e8f0; border-radius: 8px; font-size: 11.5px; color: #64748b; padding: 3px 10px;">
                        ล้างทั้งหมด
                    </button>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
                    กรุณาเลือกอุปกรณ์เสริมที่ต้องการรับไปด้วย เพื่อให้เจ้าหน้าที่เตรียมพร้อมและลงบันทึกในใบยืม-คืน:
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; margin-bottom: 18px;">
                    @php
                        $accIcons = [
                            'Adapter ชาร์จ / สายไฟ AC' => 'bi-plug-fill',
                            'กระเป๋าใส่โน้ตบุ๊ก' => 'bi-briefcase-fill',
                            'เมาส์ (Mouse)' => 'bi-mouse-fill',
                            'สายแปลงสัญญาณ HDMI / VGA' => 'bi-display',
                            'ปลั๊กพ่วงสายไฟ (Extension Cord)' => 'bi-lightning-charge-fill',
                            'รีโมทคอนโทรล (Remote Control)' => 'bi-controller',
                            'สายสัญญาณ LAN (UTP)' => 'bi-ethernet',
                            'เคส / กระเป๋าใส่อุปกรณ์' => 'bi-box-seam-fill',
                        ];
                    @endphp
                    @foreach($presetAccessories as $acc)
                        @php
                            $checked = is_array(old('accessories')) && in_array($acc, old('accessories'));
                            $icon = $accIcons[$acc] ?? 'bi-check-circle';
                        @endphp
                        <label class="accessory-card {{ $checked ? 'checked-acc' : '' }}" 
                               style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid {{ $checked ? '#0d9488' : '#e2e8f0' }}; border-radius: 12px; background: {{ $checked ? '#f0fdfa' : '#ffffff' }}; cursor: pointer; transition: all 0.2s;">
                            <input type="checkbox" name="accessories[]" value="{{ $acc }}" 
                                   {{ $checked ? 'checked' : '' }}
                                   onchange="toggleAccCard(this)"
                                   style="width: 18px; height: 18px; accent-color: #0d9488; flex-shrink: 0;">
                            <i class="bi {{ $icon }}" style="font-size: 16px; color: {{ $checked ? '#0d9488' : '#64748b' }};"></i>
                            <span style="font-size: 13px; color: #1e293b; font-weight: 500;">{{ $acc }}</span>
                        </label>
                    @endforeach
                </div>

                <div>
                    <label for="accessories_other" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        อุปกรณ์เสริมอื่นๆ เพิ่มเติม (ถ้ามี)
                    </label>
                    <input type="text" name="accessories_other" id="accessories_other" class="form-control" 
                           value="{{ old('accessories_other') }}" placeholder="ระบุอุปกรณ์อื่นๆ เพิ่มเติม เช่น ตัวชี้เลเซอร์ (Presenter), สายต่อสัญญาณเสียง 3.5mm" style="border-radius: 10px; font-size: 13.5px;">
                </div>
            </div>
        </div>

        <!-- CARD 5: หมายเหตุและข้อตกลงการยืม -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 30px; box-shadow: var(--shadow-sm); overflow: hidden; background: #ffffff;">
            <div class="card-body" style="padding: 22px;">
                <div class="mb-3">
                    <label for="notes" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        ข้อความหรือหมายเหตุถึงเจ้าหน้าที่ไอที (ถ้ามี)
                    </label>
                    <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="เช่น ขอให้ช่วยติดตั้งโปรแกรม Zoom เพิ่มเติม, ต้องการใช้งานก่อนเวลา 08:30 น." style="border-radius: 10px; font-size: 13.5px;">{{ old('notes') }}</textarea>
                </div>

                <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; margin: 0;">
                        <input type="checkbox" required style="width: 20px; height: 20px; accent-color: #0d9488; margin-top: 2px; flex-shrink: 0;">
                        <span style="font-size: 12.5px; color: #334155; line-height: 1.6;">
                            <strong>คำรับรองและข้อตกลงการยืม:</strong> ข้าพเจ้าขอรับรองว่าข้อมูลข้างต้นเป็นความจริง และยินยอมดูแลรักษาอุปกรณ์คอมพิวเตอร์และอุปกรณ์เสริมที่ได้รับมอบให้อยู่ในสภาพเรียบร้อย ปลอดภัย พร้อมส่งคืนตามกำหนดเวลา หากเกิดความเสียหายหรือสูญหายเนื่องจากความประมาทเลินเล่อ ข้าพเจ้ายินดีรับผิดชอบตามระเบียบของทางราชการ
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sticky Floating Bottom Bar for Quick Submission -->
        <div id="floatingActionBar" style="position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-top: 1px solid #e2e8f0; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08); padding: 14px 24px; z-index: 1000;">
            <div style="max-width: 1020px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div>
                        <div id="barAssetSummary" style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                            ยังไม่ได้เลือกอุปกรณ์
                        </div>
                        <div id="barDateSummary" style="font-size: 11.5px; color: #64748b;">
                            กำหนดคืน: -
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="{{ route('asset-borrows.index') }}" class="btn btn-light" style="border-radius: 10px; padding: 9px 20px; font-weight: 600; font-size: 13.5px;">
                        ยกเลิก
                    </a>
                    <button type="submit" id="submitBorrowBtn" class="btn btn-primary" style="border-radius: 10px; padding: 9px 28px; font-weight: 700; font-size: 14px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3); display: inline-flex; align-items: center; gap: 8px;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>บันทึกและยื่นคำขอยืมอุปกรณ์</span>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>

@push('styles')
<style>
.btn-category {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    transition: all 0.15s ease;
}
.btn-category:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}
.btn-category.active {
    background: #0d9488;
    color: #ffffff;
    border-color: #0d9488;
    box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25);
}
.device-card:hover {
    transform: translateY(-2px);
    border-color: #0d9488 !important;
    box-shadow: 0 6px 16px rgba(13, 148, 136, 0.12);
}
.device-card.selected-card {
    border-color: #0d9488 !important;
    background: #f0fdfa !important;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.25);
}
.device-card.selected-card .check-indicator {
    background: #0d9488;
    border-color: #0d9488;
    color: #ffffff !important;
}
.accessory-card:hover {
    border-color: #0d9488 !important;
}
.accessory-card.checked-acc {
    border-color: #0d9488 !important;
    background: #f0fdfa !important;
}
@media (max-width: 768px) {
    #floatingActionBar {
        padding: 10px 16px;
    }
    #floatingActionBar > div {
        flex-direction: column;
        align-items: stretch;
    }
    #floatingActionBar button[type="submit"] {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Global user autofill cache
const loggedInUser = {
    name: @json($user ? $user->name : ''),
    deptId: @json($user ? $user->department_id : ''),
    phone: @json($user ? $user->phone : '')
};

function selectAssetCard(assetId) {
    const select = document.getElementById('asset_id');
    if (!select) return;

    select.value = assetId;
    syncSelectedAssetFromSelect(select);
}

function syncSelectedAssetFromSelect(select) {
    const showcase = document.getElementById('selectedAssetShowcase');
    const selectedOption = select.options[select.selectedIndex];
    const allCards = document.querySelectorAll('.device-card');

    allCards.forEach(c => c.classList.remove('selected-card'));

    if (!selectedOption || !selectedOption.value) {
        showcase.style.display = 'none';
        document.getElementById('barAssetSummary').textContent = 'ยังไม่ได้เลือกอุปกรณ์';
        return;
    }

    const assetId = selectedOption.value;
    const activeCard = document.getElementById('device-card-' + assetId);
    if (activeCard) {
        activeCard.classList.add('selected-card');
    }

    // Populate showcase
    const name = selectedOption.getAttribute('data-name') || '-';
    const code = selectedOption.getAttribute('data-code') || '-';
    const brand = selectedOption.getAttribute('data-brand') || '';
    const model = selectedOption.getAttribute('data-model') || '';
    const serial = selectedOption.getAttribute('data-serial') || 'ไม่ระบุ';
    const specs = selectedOption.getAttribute('data-specs') || 'ไม่ระบุสเปคละเอียด';
    const status = selectedOption.getAttribute('data-status') || 'พร้อมใช้งาน';
    const dept = selectedOption.getAttribute('data-dept') || 'คลังส่วนกลาง';
    const cat = selectedOption.getAttribute('data-category') || 'other';

    document.getElementById('showcaseName').textContent = name;
    document.getElementById('showcaseCode').textContent = code;
    document.getElementById('showcaseBrandModel').textContent = (brand + ' ' + model).trim() || '-';
    document.getElementById('showcaseSerial').textContent = 'S/N: ' + serial;
    document.getElementById('showcaseSpecs').textContent = specs;
    document.getElementById('showcaseStatus').textContent = status;
    document.getElementById('showcaseDept').textContent = 'ประจำที่: ' + dept;

    // Update icon
    const iconEl = document.getElementById('showcaseIcon');
    if (cat === 'notebook') iconEl.className = 'bi bi-laptop';
    else if (cat === 'desktop') iconEl.className = 'bi bi-display';
    else if (cat === 'projector') iconEl.className = 'bi bi-projector';
    else iconEl.className = 'bi bi-box-seam';

    showcase.style.display = 'block';

    // Update Floating bar summary
    document.getElementById('barAssetSummary').textContent = 'กำลังขอยืม: ' + name + ' (' + code + ')';
}

function scrollToDevicePicker() {
    const el = document.getElementById('devicePickerContainer');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const search = document.getElementById('assetSearchInput');
        if (search) search.focus();
    }
}

function filterDeviceCategory(category, btn) {
    document.querySelectorAll('.btn-category').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    filterDeviceCards();
}

function filterDeviceCards() {
    const activeCategoryBtn = document.querySelector('.btn-category.active');
    const selectedCat = activeCategoryBtn ? activeCategoryBtn.getAttribute('data-filter') : 'all';
    const keyword = (document.getElementById('assetSearchInput')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.device-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardCat = card.getAttribute('data-category');
        const searchContent = card.getAttribute('data-search') || '';

        const matchesCat = (selectedCat === 'all' || cardCat === selectedCat);
        const matchesKeyword = !keyword || searchContent.includes(keyword);

        if (matchesCat && matchesKeyword) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const noMatchMsg = document.getElementById('noSearchMatchMsg');
    if (noMatchMsg) {
        noMatchMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function setDuration(days) {
    const borrowInput = document.getElementById('borrow_date');
    const returnInput = document.getElementById('expected_return_date');

    let baseDate = new Date();
    if (borrowInput && borrowInput.value) {
        baseDate = new Date(borrowInput.value);
        if (isNaN(baseDate.getTime())) baseDate = new Date();
    }

    const returnDate = new Date(baseDate);
    returnDate.setDate(returnDate.getDate() + parseInt(days));

    const yyyy = returnDate.getFullYear();
    const mm = String(returnDate.getMonth() + 1).padStart(2, '0');
    const dd = String(returnDate.getDate()).padStart(2, '0');

    returnInput.value = `${yyyy}-${mm}-${dd}`;
    calculateDuration();
}

function calculateDuration() {
    const borrowInput = document.getElementById('borrow_date');
    const returnInput = document.getElementById('expected_return_date');
    const badge = document.getElementById('durationBadge');
    const barDate = document.getElementById('barDateSummary');

    if (!borrowInput || !returnInput || !borrowInput.value || !returnInput.value) return;

    const bDate = new Date(borrowInput.value);
    const rDate = new Date(returnInput.value);

    const diffTime = rDate - bDate;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        badge.style.background = '#fef2f2';
        badge.style.color = '#dc2626';
        badge.style.borderColor = '#fecaca';
        badge.textContent = '⚠️ วันที่ส่งคืนต้องไม่น้อยกว่าวันที่เริ่มยืม';
        barDate.textContent = 'กำหนดคืน: วันที่ไม่ถูกต้อง';
    } else if (diffDays === 0) {
        badge.style.background = '#fef9c3';
        badge.style.color = '#854d0e';
        badge.style.borderColor = '#fde047';
        badge.textContent = 'ยืมภายในวันเดียวกัน (คืนวันนี้)';
        barDate.textContent = 'กำหนดคืน: ภายในวันเดียวกัน (' + returnInput.value + ')';
    } else {
        badge.style.background = '#f0fdf4';
        badge.style.color = '#166534';
        badge.style.borderColor = '#bbf7d0';
        badge.textContent = `ระยะเวลายืม: ${diffDays} วัน`;
        barDate.textContent = `กำหนดคืน: ${returnInput.value} (${diffDays} วัน)`;
    }
}

function setPurpose(text) {
    const purposeEl = document.getElementById('purpose');
    if (!purposeEl) return;

    if (!purposeEl.value.trim()) {
        purposeEl.value = text;
    } else {
        purposeEl.value = purposeEl.value + ' / ' + text;
    }
}

function toggleAccCard(checkbox) {
    const label = checkbox.closest('label');
    if (!label) return;

    if (checkbox.checked) {
        label.classList.add('checked-acc');
        label.style.borderColor = '#0d9488';
        label.style.background = '#f0fdfa';
    } else {
        label.classList.remove('checked-acc');
        label.style.borderColor = '#e2e8f0';
        label.style.background = '#ffffff';
    }
}

function selectStandardAccessories() {
    const standardKeywords = ['adapter', 'ชาร์จ', 'กระเป๋า', 'เมาส์', 'mouse'];
    document.querySelectorAll('input[name="accessories[]"]').forEach(cb => {
        const val = cb.value.toLowerCase();
        const shouldCheck = standardKeywords.some(k => val.includes(k));
        cb.checked = shouldCheck;
        toggleAccCard(cb);
    });
}

function clearAccessories() {
    document.querySelectorAll('input[name="accessories[]"]').forEach(cb => {
        cb.checked = false;
        toggleAccCard(cb);
    });
}

function resetUserAutofill() {
    if (loggedInUser.name) document.getElementById('borrower_name').value = loggedInUser.name;
    if (loggedInUser.deptId) document.getElementById('department_id').value = loggedInUser.deptId;
    if (loggedInUser.phone) document.getElementById('contact_phone').value = loggedInUser.phone;
}

// Initial setup on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('asset_id');
    if (select && select.value) {
        syncSelectedAssetFromSelect(select);
    }
    calculateDuration();
});
</script>
@endpush
@endsection
