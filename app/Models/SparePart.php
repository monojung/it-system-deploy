<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class SparePart extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_spare_parts';

    protected $fillable = [
        'part_code',
        'name',
        'category',
        'unit',
        'stock_quantity',
        'minimum_quantity',
        'unit_price',
        'location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'minimum_quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function repairParts(): HasMany
    {
        return $this->hasMany(RepairPart::class, 'spare_part_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_quantity;
    }
}
