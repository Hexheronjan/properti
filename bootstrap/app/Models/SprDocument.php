<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nup_registration_id',
        'unit_selection_id',
        'kpr_screening_id',
        'spr_number',
        'spr_date',
        'unit_price',
        'down_payment',
        'remaining_amount',
        'payment_scheme',
        'payment_schedule',
        'status',
        'terms_conditions',
        'document_file',
        'issued_date',
        'issued_by',
    ];

    protected $casts = [
        'spr_date' => 'date',
        'unit_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'issued_date' => 'datetime',
    ];

    /**
     * Get the user for the SPR document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the NUP registration for the SPR document.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the unit selection for the SPR document.
     */
    public function unitSelection()
    {
        return $this->belongsTo(UnitSelection::class);
    }

    /**
     * Get the KPR screening for the SPR document.
     */
    public function kprScreening()
    {
        return $this->belongsTo(KprScreening::class);
    }

    /**
     * Check if SPR is issued
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Check if SPR is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generate SPR number
     */
    public static function generateSprNumber(): string
    {
        $prefix = 'SPR-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include issued SPR documents.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include draft SPR documents.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if UTK payment is completed before issuing SPR
     */
    public function canBeIssued(): bool
    {
        if ($this->unitSelection) {
            return $this->unitSelection->isUtkPaid();
        }
        return false;
    }
}