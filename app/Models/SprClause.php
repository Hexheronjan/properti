<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprClause extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_project_id',
        'clause_number',
        'clause_title',
        'clause_content',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the housing project for this SPR clause.
     */
    public function housingProject()
    {
        return $this->belongsTo(HousingProject::class);
    }

    /**
     * Scope a query to only include active clauses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
