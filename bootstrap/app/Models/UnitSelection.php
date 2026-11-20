<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'nup_registration_id',
        'housing_unit_id',
        'user_id',
        'selection_order',
        'selection_date',
        'status',
        'notes',
        'utk_amount',
        'utk_payment_status',
        'utk_payment_date',
        'utk_payment_reference',
        'utk_payment_gateway_response',
        'utk_payment_deadline',
    ];

    protected $casts = [
        'selection_date' => 'datetime',
        'utk_amount' => 'decimal:2',
        'utk_payment_date' => 'datetime',
        'utk_payment_deadline' => 'datetime',
    ];

    /**
     * Get the NUP registration for the unit selection.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the housing unit for the unit selection.
     */
    public function housingUnit()
    {
        return $this->belongsTo(HousingUnit::class);
    }

    /**
     * Get the user for the unit selection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the KPR screening for the unit selection.
     */
    public function kprScreening()
    {
        return $this->hasMany(KprScreening::class);
    }

    /**
     * Get the SPR documents for the unit selection.
     */
    public function sprDocuments()
    {
        return $this->hasMany(SprDocument::class);
    }

    /**
     * Check if selection is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if selection is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Scope a query to only include confirmed selections.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to order by selection order.
     */
    public function scopeBySelectionOrder($query)
    {
        return $query->orderBy('selection_order', 'asc');
    }

    /**
     * Get the UTK payment for this unit selection
     */
    public function utkPayment()
    {
        return $this->hasOne(UtkPayment::class);
    }

    /**
     * Check if UTK payment is paid
     */
    public function isUtkPaid(): bool
    {
        return $this->utk_payment_status === 'paid';
    }

    /**
     * Check if UTK payment is pending
     */
    public function isUtkPending(): bool
    {
        return $this->utk_payment_status === 'pending';
    }

    /**
     * Set UTK payment deadline (7 days after unit selection)
     */
    public function setUtkPaymentDeadline(): void
    {
        $this->utk_payment_deadline = now()->addDays(7);
        $this->save();
    }

    /**
     * Check if UTK payment deadline has passed
     */
    public function isUtkPaymentDeadlinePassed(): bool
    {
        return $this->utk_payment_deadline && now()->gt($this->utk_payment_deadline);
    }
}