<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NupRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'nup_registration_id',
        'user_id',
        'refund_number',
        'refund_amount',
        'refund_reason',
        'refund_notes',
        'status',
        'requested_at',
        'approved_at',
        'rejected_at',
        'processed_at',
        'approved_by',
        'rejected_by',
        'rejection_reason',
        'payment_reference',
        'payment_gateway_response',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the NUP registration for the refund.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the user for the refund.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if refund is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if refund is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if refund is processed
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if refund is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Generate refund number
     */
    public static function generateRefundNumber(): string
    {
        $prefix = 'REF-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include pending refunds.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved refunds.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include processed refunds.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
