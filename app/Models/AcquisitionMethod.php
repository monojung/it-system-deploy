<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcquisitionMethod extends Model
{
    use HasFactory;

    protected $table = 'it_acquisition_methods';

    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'acquisition_method_id');
    }
}
