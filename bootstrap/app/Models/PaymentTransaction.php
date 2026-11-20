<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nup_registration_id',
        'transaction_number',
        'payment_gateway',
        'payment_method',
        'payment_channel',
        'amount',
        'status',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_request_payload',
        'gateway_response_payload',
        'transaction_date',
        'expiry_date',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'expiry_date' => 'datetime',
        'gateway_request_payload' => 'array',
        'gateway_response_payload' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user for the payment transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the NUP registration for the payment transaction.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'TRX-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by payment gateway.
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }
}