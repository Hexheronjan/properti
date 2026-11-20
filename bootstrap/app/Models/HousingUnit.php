<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_code',
        'block',
        'number',
        'type',
        'price',
        'building_area',
        'land_area',
        'bedrooms',
        'bathrooms',
        'description',
        'status',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the unit selections for the housing unit.
     */
    public function unitSelections()
    {
        return $this->hasMany(UnitSelection::class);
    }

    /**
     * Check if unit is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Get full unit identifier
     */
    public function getFullIdentifierAttribute(): string
    {
        return "Blok {$this->block} No. {$this->number}";
    }

    /**
     * Scope a query to only include available units.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by block.
     */
    public function scopeByBlock($query, $block)
    {
        return $query->where('block', $block);
    }
}