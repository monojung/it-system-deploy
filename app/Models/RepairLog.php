<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairLog extends Model
{
    use HasFactory;

    protected $table = 'it_repair_logs';

    public $timestamps = false;

    protected $fillable = [
        'repair_id',
        'user_id',
        'action',
        'previous_status',
        'new_status',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class, 'repair_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
