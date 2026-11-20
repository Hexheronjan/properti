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
        'utk_base_amount',
        'utk_ppn_amount',
        'utk_ve_amount',
        'payment_method',
        'developer_bank_account_id',
        'status',
        'proof_of_transfer',
        'sender_bank',
        'sender_name',
        'sender_account_number',
        'transfer_date',
        'payment_gateway_reference',
        'payment_gateway_response',
        'payment_date',
        'expired_at',
        'snap_token',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'utk_base_amount' => 'decimal:2',
        'utk_ppn_amount' => 'decimal:2',
        'utk_ve_amount' => 'decimal:2',
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
     * Get the developer bank account for the payment.
     */
    public function developerBankAccount()
    {
        return $this->belongsTo(DeveloperBankAccount::class);
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
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
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
        do {
            $number = $prefix . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('payment_number', $number)->exists());

        return $number;
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
