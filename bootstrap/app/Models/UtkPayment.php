<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtkPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_selection_id',
        'nup_registration_id',
        'user_id',
        'payment_number',
        'amount',
        'payment_method',
        'status',
        'payment_gateway_reference',
        'payment_gateway_response',
        'payment_date',
        'expired_at',
        'snap_token',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the unit selection for the UTK payment.
     */
    public function unitSelection()
    {
        return $this->belongsTo(UnitSelection::class);
    }

    /**
     * Get the NUP registration for the UTK payment.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the user for the UTK payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is successful
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Generate payment number
     */
    public static function generatePaymentNumber(): string
    {
        $prefix = 'UTK-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
