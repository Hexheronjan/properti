<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KprPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'spr_document_id',
        'user_id',
        'payment_number',
        'amount',
        'payment_type',
        'installment_number',
        'total_installments',
        'principal_amount',
        'interest_amount',
        'remaining_principal',
        'payment_method',
        'status',
        'transaction_id',
        'payment_response',
        'payment_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'remaining_principal' => 'decimal:2',
        'payment_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Get the SPR document for this payment.
     */
    public function sprDocument()
    {
        return $this->belongsTo(SprDocument::class);
    }

    /**
     * Get the user for this payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        $prefix = 'KPR-PAY-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total paid amount for an SPR
     */
    public static function getTotalPaid($sprDocumentId): float
    {
        return self::where('spr_document_id', $sprDocumentId)
            ->where('status', 'success')
            ->sum('amount');
    }

    /**
     * Calculate remaining amount to be paid
     */
    public static function getRemainingAmount($sprDocumentId): float
    {
        $spr = SprDocument::find($sprDocumentId);
        if (!$spr) return 0;

        $totalPaid = self::getTotalPaid($sprDocumentId);
        return $spr->remaining_amount - $totalPaid;
    }

    /**
     * Check if KPR is fully paid
     */
    public static function isFullyPaid($sprDocumentId): bool
    {
        $remaining = self::getRemainingAmount($sprDocumentId);
        return $remaining <= 0;
    }
}
