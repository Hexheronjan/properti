<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NupRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_number',
        'payment_method',
        'payment_status',
        'queue_number',
        'queue_issue_date',
        'payment_date',
        'nup_fee',
        'payment_gateway_reference',
        'payment_gateway_response',
        'unit_selection_deadline',
        'is_expired',
        'is_forfeited',
        'forfeited_at',
        'forfeit_reason',
        'refund_requested',
        'refund_processed',
        'refund_amount',
        'refund_processed_at',
        'refund_reason',
        'original_payment_method',
        'payment_method_switched_at',
        'payment_method_switch_reason',
    ];

    protected $casts = [
        'queue_issue_date' => 'datetime',
        'payment_date' => 'datetime',
        'nup_fee' => 'decimal:2',
        'unit_selection_deadline' => 'datetime',
        'forfeited_at' => 'datetime',
        'refund_processed_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'payment_method_switched_at' => 'datetime',
    ];

    /**
     * Get the user that owns the NUP registration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit selections for the NUP registration.
     */
    public function unitSelections()
    {
        return $this->hasMany(UnitSelection::class);
    }

    /**
     * Get the KPR screening for the NUP registration.
     */
    public function kprScreening()
    {
        return $this->hasOne(KprScreening::class);
    }

    /**
     * Get the SPR documents for the NUP registration.
     */
    public function sprDocuments()
    {
        return $this->hasMany(SprDocument::class);
    }

    /**
     * Get the payment transactions for the NUP registration.
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if payment is successful
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if queue number has been issued
     */
    public function hasQueueNumber(): bool
    {
        return !empty($this->queue_number) && !empty($this->queue_issue_date);
    }

    /**
     * Generate registration number
     */
    public static function generateRegistrationNumber(): string
    {
        $prefix = config('nup.registration_prefix');
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate queue number
     */
    public static function generateQueueNumber(): string
    {
        $prefix = config('nup.queue_number_prefix');
        $date = now()->format('Ymd');
        $count = self::where('payment_status', 'paid')->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if registration is expired
     */
    public function isExpired(): bool
    {
        return $this->is_expired || ($this->unit_selection_deadline && now()->gt($this->unit_selection_deadline));
    }

    /**
     * Check if registration is forfeited
     */
    public function isForfeited(): bool
    {
        return $this->is_forfeited;
    }

    /**
     * Check if refund is requested
     */
    public function isRefundRequested(): bool
    {
        return $this->refund_requested;
    }

    /**
     * Check if refund is processed
     */
    public function isRefundProcessed(): bool
    {
        return $this->refund_processed;
    }

    /**
     * Set unit selection deadline based on payment method
     */
    public function setUnitSelectionDeadline(): void
    {
        if ($this->payment_method === 'tunai_mitra_pintar') {
            // 7 days for cash lane (from config)
            $hours = config('nup.deadlines.unit_selection_cash', 168);
            $this->unit_selection_deadline = now()->addHours($hours);
        } elseif ($this->payment_method === 'kpr_bank_flpp') {
            // For KPR lane, deadline will be set after screening approval
            $this->unit_selection_deadline = null;
        }
        $this->save();
    }

    /**
     * Get the refund for this registration
     */
    public function refund()
    {
        return $this->hasOne(NupRefund::class);
    }

    /**
     * Mark as forfeited
     */
    public function markAsForfeited(string $reason): void
    {
        $this->update([
            'is_forfeited' => true,
            'forfeited_at' => now(),
            'forfeit_reason' => $reason,
        ]);
    }

    /**
     * Switch payment method
     */
    public function switchPaymentMethod(string $newMethod, string $reason): void
    {
        $this->update([
            'original_payment_method' => $this->payment_method,
            'payment_method' => $newMethod,
            'payment_method_switched_at' => now(),
            'payment_method_switch_reason' => $reason,
        ]);
    }
}