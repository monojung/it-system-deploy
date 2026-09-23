<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommand extends Model
{
    use HasFactory;

    protected $table = 'it_agent_commands';

    protected $fillable = [
        'command',
        'batch_id',
        'target_type',
        'target_hardware_id',
        'target_hostname',
        'target_audit_id',
        'target_asset_id',
        'status',
        'requested_by',
        'dispatched_at',
        'executed_at',
        'ip_address',
        'parameters',
        'result_summary',
    ];

    protected $casts = [
        'parameters' => 'array',
        'dispatched_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function targetAudit(): BelongsTo
    {
        return $this->belongsTo(HardwareAudit::class, 'target_audit_id');
    }

    public function targetAsset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'target_asset_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Mark command as processing by client
     */
    public function markAsDispatched(string $ip = null): self
    {
        $this->status = 'processing';
        $this->dispatched_at = now();
        if ($ip) {
            $this->ip_address = $ip;
        }
        $this->save();
        return $this;
    }

    /**
     * Mark command as successfully executed
     */
    public function markAsCompleted(string $summary = null, string $ip = null): self
    {
        $this->status = 'completed';
        $this->executed_at = now();
        if ($summary) {
            $this->result_summary = $summary;
        }
        if ($ip) {
            $this->ip_address = $ip;
        }
        $this->save();
        return $this;
    }

    /**
     * Mark command as failed
     */
    public function markAsFailed(string $error): self
    {
        $this->status = 'failed';
        $this->executed_at = now();
        $this->result_summary = $error;
        $this->save();
        return $this;
    }
}
