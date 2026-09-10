@extends('layouts.app')

@section('title', 'แดชบอร์ดภาพรวม')
@section('page_title', 'แดชบอร์ดภาพรวมระบบสารสนเทศ')
@section('page_subtitle', 'กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.primary { background: #ccfbf1; color: #0f766e; }
    .stat-icon.warning { background: #fef3c7; color: #b45309; }
    .stat-icon.info { background: #e0f2fe; color: #0369a1; }
    .stat-icon.success { background: #d1fae5; color: #047857; }
    .stat-icon.purple { background: #f3e8ff; color: #6b21a8; }

    .stat-val {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.1;
        color: var(--text-main);
    }

    .stat-lbl {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 28px;
    }

    @media (max-width: 992px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- Top Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="bi bi-tools"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($totalRepairs) }}</div>
            <div class="stat-lbl">งานแจ้งซ่อมทั้งหมด</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning {{ $pendingRepairs > 0 ? 'animate-pulse' : '' }}">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <div class="stat-val" style="color: #b45309;">{{ number_format($pendingRepairs) }}</div>
            <div class="stat-lbl">รอช่างรับเรื่อง</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="bi bi-wrench-adjustable-circle"></i>
        </div>
        <div>
            <div class="stat-val" style="color: #0369a1;">{{ number_format($inProgressRepairs) }}</div>
            <div class="stat-lbl">กำลังดำเนินการ / รออะไหล่</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div>
            <div class="stat-val" style="color: #047857;">{{ number_format($completedRepairsThisMonth) }}</div>
            <div class="stat-lbl">ซ่อมเสร็จในเดือนนี้</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="bi bi-pc-display"></i>
        </div>
        <div>
            <div class="stat-val" style="color: #6b21a8;">{{ number_format($totalAssets) }}</div>
            <div class="stat-lbl">ครุภัณฑ์คอมพิวเตอร์</div>
        </div>
    </div>

    <a href="{{ route('data-requests.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="bi bi-file-earmark-bar-graph"></i>
        </div>
        <div>
            <div class="stat-val" style="color: #0284c7;">{{ number_format($totalDataRequests) }}</div>
            <div class="stat-lbl">ขอข้อมูลสารสนเทศ</div>
            @if($pendingDataRequests > 0)
                <div style="font-size: 11px; color: #d97706; font-weight: 600; margin-top: 2px;">
                    <i class="bi bi-clock-history"></i> รอพิจารณา {{ $pendingDataRequests }} คำขอ
                </div>
            @endif
        </div>
    </a>
</div>

<!-- Low Stock Warning Alert if any -->
@if(count($lowStockParts) > 0)
<div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 24px;">
    <div class="card-header" style="background: #fffbeb;">
        <div class="card-title" style="color: #92400e;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>แจ้งเตือนพัสดุและอะไหล่ใกล้หมดสต็อก (ต่ำกว่าเกณฑ์ขั้นต่ำ {{ count($lowStockParts) }} รายการ)</span>
        </div>
        <a href="{{ route('spare-parts.index', ['low_stock' => 1]) }}" class="btn btn-warning btn-sm">
            <span>ตรวจสอบคลังอะไหล่</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body" style="padding: 12px 24px;">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            @foreach($lowStockParts as $part)
            <div style="background: white; border: 1px solid #fde68a; border-radius: 8px; padding: 6px 14px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <strong>{{ $part->name }}</strong>:
                <span class="badge badge-danger">คงเหลือ {{ $part->stock_quantity }} {{ $part->unit }}</span>
                <span style="color: #94a3b8; font-size: 11px;">(เกณฑ์ต่ำสุด {{ $part->minimum_quantity }})</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Charts Section -->
<div class="charts-grid">
    <!-- Trend Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                <span>สถิติการแจ้งซ่อมย้อนหลัง 6 เดือน</span>
            </div>
            <span style="font-size: 12.5px; color: var(--text-muted);">จำนวนงานซ่อมรายเดือน</span>
        </div>
        <div class="card-body">
            <div style="height: 280px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Breakdown Donut -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-pie-chart-fill text-info"></i>
                <span>สัดส่วนสถานะงานซ่อม</span>
            </div>
        </div>
        <div class="card-body">
            <div style="height: 280px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Recent Repairs & Department Top 5 -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Repairs Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-clock-history text-primary"></i>
                <span>รายการแจ้งซ่อมล่าสุด</span>
            </div>
            <a href="{{ route('repairs.index') }}" class="btn btn-secondary btn-sm">
                <span>ดูทั้งหมด</span>
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัสแจ้งซ่อม</th>
                            <th>อาการ/ปัญหา</th>
                            <th>แผนก</th>
                            <th>ความเร่งด่วน</th>
                            <th>สถานะ</th>
                            <th>วันที่แจ้ง</th>
                            <th style="text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRepairs as $repair)
                        <tr>
                            <td>
                                <a href="{{ route('repairs.show', $repair) }}" style="font-weight: 600; color: var(--primary); text-decoration: none;">
                                    {{ $repair->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 500; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $repair->title }}">
                                    {{ $repair->title }}
                                </div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">
                                    ผู้แจ้ง: {{ $repair->requester_name }}
                                </div>
                            </td>
                            <td>{{ $repair->department?->name }}</td>
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
                            <td style="font-size: 12.5px; color: var(--text-muted); white-space: nowrap;">
                                {{ $repair->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                ยังไม่มีรายการแจ้งซ่อมในระบบ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top 5 Departments -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-building text-warning"></i>
                <span>แผนกที่แจ้งซ่อมบ่อย (Top 5)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 16px;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse($topDepartments as $index => $dept)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #f8fafc; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 24px; border-radius: 50%; background: {{ $index == 0 ? '#f59e0b' : '#cbd5e1' }}; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <div style="font-weight: 600; font-size: 13.5px;">{{ $dept->name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $dept->code }} &bull; {{ $dept->building }}</div>
                        </div>
                    </div>
                    <span class="badge badge-primary" style="font-size: 13px;">
                        {{ $dept->repairs_count }} งาน
                    </span>
                </div>
                @empty
                <div style="text-align: center; color: var(--text-muted); padding: 20px;">
                    ไม่มีข้อมูลแผนก
                </div>
                @endforelse
            </div>

            <!-- Quick Action Links -->
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">
                    ทางลัดการทำงาน (Quick Links)
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('repairs.create') }}" class="btn btn-primary" style="justify-content: flex-start;">
                        <i class="bi bi-plus-circle"></i>
                        <span>แจ้งซ่อมอุปกรณ์คอมพิวเตอร์</span>
                    </a>
                    <a href="{{ route('data-requests.create') }}" class="btn btn-secondary" style="justify-content: flex-start; color: #0f766e; border-color: #99f6e4; background: #f0fdfa;">
                        <i class="bi bi-file-earmark-plus"></i>
                        <span>ยื่นขอข้อมูลสารสนเทศ & HosXP</span>
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                        <i class="bi bi-search"></i>
                        <span>ค้นหาครุภัณฑ์ในระบบ</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('backups.index') }}" class="btn btn-secondary" style="justify-content: flex-start;">
                        <i class="bi bi-database-down"></i>
                        <span>สำรองฐานข้อมูลทันที</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Data Requests Card -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-file-earmark-bar-graph text-primary"></i>
            <span>คำขอข้อมูลสารสนเทศและสถิติล่าสุด</span>
        </div>
        <a href="{{ route('data-requests.index') }}" class="btn btn-secondary btn-sm">
            <span>ดูรายการทั้งหมด</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 140px;">รหัสคำขอ</th>
                    <th>หัวข้อข้อมูลที่ขอ</th>
                    <th style="width: 160px;">แผนก</th>
                    <th style="width: 150px;">ผู้ยื่นคำขอ</th>
                    <th style="width: 110px;">รูปแบบ</th>
                    <th style="width: 120px;">สถานะ</th>
                    <th style="width: 90px; text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDataRequests as $dr)
                <tr>
                    <td>
                        <a href="{{ route('data-requests.show', $dr) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $dr->request_no }}
                        </a>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $dr->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600;">
                            <a href="{{ route('data-requests.show', $dr) }}" style="color: inherit; text-decoration: none;">
                                {{ $dr->title }}
                            </a>
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                            {{ $dr->objective_label }}
                        </div>
                    </td>
                    <td>{{ $dr->department?->name ?? 'ไม่ระบุ' }}</td>
                    <td>{{ $dr->user?->name ?? '-' }}</td>
                    <td><span style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ $dr->file_format }}</span></td>
                    <td><span class="badge {{ $dr->status_badge }}">{{ $dr->status_label }}</span></td>
                    <td style="text-align: center;">
                        <a href="{{ route('data-requests.show', $dr) }}" class="btn btn-secondary btn-sm" title="ดูรายละเอียด">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 25px;">
                        ยังไม่มีคำขอข้อมูลสารสนเทศในระบบ
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Monthly Trend Line/Bar Chart
        const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'จำนวนงานแจ้งซ่อม',
                    data: {!! json_encode($monthlyCounts) !!},
                    backgroundColor: 'rgba(13, 148, 136, 0.75)',
                    borderColor: '#0f766e',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Status Donut Chart
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['รอรับเรื่อง', 'กำลังซ่อม', 'รออะไหล่', 'เสร็จสิ้น', 'ส่งซ่อมภายนอก', 'ยกเลิก'],
                datasets: [{
                    data: [
                        {{ $statusCounts['pending'] }},
                        {{ $statusCounts['in_progress'] }},
                        {{ $statusCounts['waiting_parts'] }},
                        {{ $statusCounts['completed'] }},
                        {{ $statusCounts['external'] }},
                        {{ $statusCounts['cancelled'] }}
                    ],
                    backgroundColor: [
                        '#f59e0b', // pending yellow
                        '#0284c7', // in_progress blue
                        '#ea580c', // waiting_parts orange
                        '#10b981', // completed green
                        '#a855f7', // external purple
                        '#94a3b8'  // cancelled gray
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { family: 'Prompt', size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    });
</script>
@endpush
