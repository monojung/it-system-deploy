<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceType extends Model
{
    use HasFactory;

    protected $table = 'it_device_types';

    protected $fillable = [
        'name',
        'code',
        'icon',
        'description',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'device_type_id');
    }
}
