<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUpdate extends Model
{
    use HasFactory;

    protected $table = 'it_system_updates';

    protected $fillable = [
        'version',
        'commit_hash',
        'previous_commit',
        'commits_count',
        'changelog',
        'status',
        'backup_file',
        'output_log',
        'error_message',
        'duration_seconds',
        'triggered_by',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration_seconds' => 'integer',
            'commits_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function getShortCommitAttribute(): string
    {
        return $this->commit_hash ? substr($this->commit_hash, 0, 7) : '-';
    }

    public function getShortPreviousCommitAttribute(): string
    {
        return $this->previous_commit ? substr($this->previous_commit, 0, 7) : '-';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'success' => [
                'label' => 'สำเร็จ',
                'bg' => '#ecfdf5',
                'color' => '#059669',
                'border' => '#a7f3d0',
                'icon' => 'bi-check-circle-fill'
            ],
            'failed' => [
                'label' => 'ล้มเหลว',
                'bg' => '#fef2f2',
                'color' => '#dc2626',
                'border' => '#fecaca',
                'icon' => 'bi-x-circle-fill'
            ],
            'running' => [
                'label' => 'กำลังดำเนินการ',
                'bg' => '#eff6ff',
                'color' => '#2563eb',
                'border' => '#bfdbfe',
                'icon' => 'bi-arrow-repeat spin'
            ],
            default => [
                'label' => 'รอดำเนินการ',
                'bg' => '#f8fafc',
                'color' => '#64748b',
                'border' => '#e2e8f0',
                'icon' => 'bi-clock'
            ],
        };
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_seconds <= 0) {
            return '-';
        }
        if ($this->duration_seconds < 60) {
            return "{$this->duration_seconds} วินาที";
        }
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return "{$minutes} นาที {$seconds} วินาที";
    }
}
