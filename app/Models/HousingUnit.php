<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
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
        'project_id',
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
     * Get the developer company that owns this unit.
     */
    public function company()
    {
        return $this->belongsTo(DeveloperCompany::class, 'company_id');
    }

    /**
     * Get the project that owns the housing unit.
     */
    public function project()
    {
        return $this->belongsTo(HousingProject::class);
    }

    /**
     * Alias for project relationship
     */
    public function housingProject()
    {
        return $this->project();
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
     * Get human-friendly formatted type
     */
    public function getFormattedTypeAttribute(): string
    {
        if (empty($this->type)) {
            return '-';
        }
        if (str_starts_with($this->type, 'type_')) {
            return config('nup.unit_types.' . $this->type, ucfirst(str_replace('type_', 'Tipe ', $this->type)));
        }
        return $this->type;
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

    /**
     * Scope a query to filter by developer company ID.
     */
    public function scopeForCompany($query, ?int $companyId)
    {
        if (!$companyId) {
            return $query;
        }

        return $query->where(function ($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhereHas('project', fn ($pq) => $pq->where('company_id', $companyId));
        });
    }

    /**
     * Check whether this housing unit belongs to the given developer company ID.
     */
    public function belongsToCompany(?int $companyId): bool
    {
        if (!$companyId) {
            return true;
        }

        $unitCompanyId = $this->company_id ?: $this->project?->company_id;
        return (int) $unitCompanyId === (int) $companyId;
    }
}