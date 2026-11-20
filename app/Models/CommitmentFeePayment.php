<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitmentFeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpr_screening_id',
        'housing_unit_id',
        'housing_project_id',
        'payment_number',
        'amount',
        'payment_gateway',
        'payment_method',
        'status',
        'transaction_id',
        'payment_response',
        'payment_date',
        'deadline',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'deadline' => 'datetime',
        'payment_response' => 'array',
    ];

    /**
     * Get the user for this payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the KPR screening for this payment.
     */
    public function kprScreening()
    {
        return $this->belongsTo(KprScreening::class);
    }

    /**
     * Get the housing unit for this payment.
     */
    public function housingUnit()
    {
        return $this->belongsTo(HousingUnit::class);
    }

    /**
     * Get the housing project for this payment.
     */
    public function housingProject()
    {
        return $this->belongsTo(HousingProject::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment deadline has passed
     */
    public function isDeadlinePassed(): bool
    {
        return $this->deadline && now()->gt($this->deadline);
    }

    /**
     * Scope a query to only include successful payments.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Generate payment number
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'CF-';
        do {
            $number = $prefix . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('payment_number', $number)->exists());

        return $number;
    }
}
