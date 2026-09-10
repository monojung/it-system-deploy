<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Department extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_departments';

    protected $fillable = [
        'name',
        'code',
        'building',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'department_id');
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class, 'department_id');
    }
}
