<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class DataRequest extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_data_requests';

    protected $fillable = [
        'request_no',
        'user_id',
        'department_id',
        'title',
        'objective_type',
        'objective_detail',
        'data_start_date',
        'data_end_date',
        'criteria_detail',
        'sample_file',
        'sample_filename',
        'file_format',
        'urgency',
        'pdpa_consent',
        'status',
        'handler_id',
        're_request_from_id',
        'sql_query',
        'result_file',
        'download_count',
        'admin_notes',
        'completed_at',
    ];

    protected $casts = [
        'data_start_date' => 'date',
        'data_end_date' => 'date',
        'pdpa_consent' => 'boolean',
        'download_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function reRequestFrom()
    {
        return $this->belongsTo(DataRequest::class, 're_request_from_id');
    }

    public function reRequests()
    {
        return $this->hasMany(DataRequest::class, 're_request_from_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รอพิจารณา',
            'approved' => 'อนุมัติแล้ว',
            'in_progress' => 'กำลังประมวลผล',
            'completed' => 'เสร็จสิ้น',
            'rejected' => 'ปฏิเสธคำขอ',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getObjectiveLabelAttribute(): string
    {
        return match ($this->objective_type) {
            'ha_quality' => 'พัฒนาคุณภาพโรงพยาบาล (HA/QA)',
            'research' => 'งานวิจัย / วิทยานิพนธ์',
            'executive' => 'รายงานผู้บริหาร / ประชุม',
            'external' => 'ตอบแบบสำรวจ สสจ./สปสช.',
            default => 'วัตถุประสงค์อื่นๆ',
        };
    }

    public function getUrgencyLabelAttribute(): string
    {
        return match ($this->urgency) {
            'normal' => 'ปกติ',
            'urgent' => 'ด่วน',
            'very_urgent' => 'ด่วนที่สุด',
            default => 'ปกติ',
        };
    }

    public function getUrgencyBadgeAttribute(): string
    {
        return match ($this->urgency) {
            'normal' => 'badge-secondary',
            'urgent' => 'badge-warning',
            'very_urgent' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public static function generateRequestNo(): string
    {
        $prefix = 'REQ-' . date('Ym') . '-';
        $last = self::where('request_no', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        if ($last) {
            $lastNum = (int) substr($last->request_no, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }
        return $prefix . $nextNum;
    }
}
