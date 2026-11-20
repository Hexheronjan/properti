<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KprScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nup_registration_id',
        'unit_selection_id',
        'screening_number',
        'bank',
        'bank_other',
        'loan_amount',
        'tenor_years',
        'monthly_income',
        'monthly_expense',
        'employment_status',
        'employment_duration_years',
        'employer_name',
        'status',
        'rejection_reason',
        'submission_date',
        'approval_date',
        'approved_by',
        'notes',
        'unit_selection_deadline',
        'participant_decision',
        'decision_made_at',
        'decision_notes',
        'notification_sent_at',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'monthly_income' => 'decimal:2',
        'monthly_expense' => 'decimal:2',
        'submission_date' => 'datetime',
        'approval_date' => 'datetime',
        'unit_selection_deadline' => 'datetime',
        'decision_made_at' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    /**
     * Get the user for the KPR screening.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the NUP registration for the KPR screening.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the unit selection for the KPR screening.
     */
    public function unitSelection()
    {
        return $this->belongsTo(UnitSelection::class);
    }

    /**
     * Get the user documents for the KPR screening.
     */
    public function userDocuments()
    {
        return $this->hasMany(UserDocument::class);
    }

    /**
     * Get the SPR documents for the KPR screening.
     */
    public function sprDocuments()
    {
        return $this->hasMany(SprDocument::class);
    }

    /**
     * Check if screening is approved (layak_proses)
     */
    public function isApproved(): bool
    {
        return $this->status === 'layak_proses' || $this->status === 'approved';
    }

    /**
     * Check if screening is rejected (tidak_layak)
     */
    public function isRejected(): bool
    {
        return $this->status === 'tidak_layak' || $this->status === 'rejected';
    }

    /**
     * Check if screening is layak_proses (fully eligible)
     */
    public function isLayakProses(): bool
    {
        return $this->status === 'layak_proses';
    }

    /**
     * Check if screening is layak_bersyarat (conditionally eligible)
     */
    public function isLayakBersyarat(): bool
    {
        return $this->status === 'layak_bersyarat';
    }

    /**
     * Check if screening is tidak_layak (not eligible)
     */
    public function isTidakLayak(): bool
    {
        return $this->status === 'tidak_layak';
    }

    /**
     * Check if screening is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Generate screening number
     */
    public static function generateScreeningNumber(): string
    {
        $prefix = 'SCR-';
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query to only include approved screenings.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending screenings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing screenings.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Set unit selection deadline (48 hours after approval/layak_bersyarat)
     */
    public function setUnitSelectionDeadline(): void
    {
        $hours = config('nup.deadlines.unit_selection_kpr', 48);
        $this->unit_selection_deadline = now()->addHours($hours);
        $this->notification_sent_at = now();
        $this->save();
    }

    /**
     * Check if unit selection deadline has passed
     */
    public function isUnitSelectionDeadlinePassed(): bool
    {
        return $this->unit_selection_deadline && now()->gt($this->unit_selection_deadline);
    }

    /**
     * Record participant decision
     */
    public function recordParticipantDecision(string $decision, ?string $notes = null): void
    {
        $this->update([
            'participant_decision' => $decision,
            'decision_made_at' => now(),
            'decision_notes' => $notes,
        ]);
    }

    /**
     * Check if participant has made a decision
     */
    public function hasParticipantDecision(): bool
    {
        return !empty($this->participant_decision) && $this->participant_decision !== 'pending';
    }
}