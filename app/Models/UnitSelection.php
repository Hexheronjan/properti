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
        'payment_scheme',
        'mitra_institution_name',
        'mitra_document',
        'mitra_status',
        'mitra_rejection_reason',
        'mitra_verified_at',
        'mitra_verified_by',
        'user_id',
        'selection_order',
        'selection_date',
        'status',
        'notes',
        'utk_payment_status',
        'utk_payment_date',
        'utk_amount',
        'utk_base_amount',
        'utk_ppn_amount',
        'utk_ve_amount',
        'utk_payment_deadline',
    ];

    protected $casts = [
        'selection_date' => 'datetime',
        'utk_payment_date' => 'datetime',
        'utk_payment_deadline' => 'datetime',
        'mitra_verified_at' => 'datetime',
        'utk_amount' => 'decimal:2',
        'utk_base_amount' => 'decimal:2',
        'utk_ppn_amount' => 'decimal:2',
        'utk_ve_amount' => 'decimal:2',
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
     * Get the latest KPR screening for the unit selection.
     */
    public function kprScreening()
    {
        return $this->hasOne(KprScreening::class)->latestOfMany();
    }

    /**
     * Get all KPR screenings for the unit selection.
     */
    public function kprScreenings()
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
     * Get the UTK payments for the unit selection.
     */
    public function utkPayments()
    {
        return $this->hasMany(UtkPayment::class);
    }

    /**
     * User yang memverifikasi dokumen Mitra Pintar
     */
    public function mitraVerifier()
    {
        return $this->belongsTo(User::class, 'mitra_verified_by');
    }

    /**
     * Cek apakah skema pembayaran adalah Kredit Mitra Pintar
     */
    public function isMitraPintar(): bool
    {
        return $this->payment_scheme === 'mitra_pintar';
    }

    /**
     * Cek apakah dokumen Mitra Pintar sudah disetujui
     */
    public function isMitraApproved(): bool
    {
        if (!$this->isMitraPintar()) {
            return true;
        }
        return $this->mitra_status === 'approved';
    }

    /**
     * Dapatkan data Developer Company dari unit yang dipilih
     */
    public function getCompany(): ?\App\Models\DeveloperCompany
    {
        return $this->housingUnit?->housingProject?->company 
            ?? $this->nupRegistration?->housingProject?->company;
    }

    /**
     * Cek apakah dokumen Mitra Pintar masih pending verifikasi
     */
    public function isMitraPending(): bool
    {
        return $this->isMitraPintar() && ($this->mitra_status === 'pending' || empty($this->mitra_status));
    }

    /**
     * Cek apakah dokumen Mitra Pintar ditolak
     */
    public function isMitraRejected(): bool
    {
        return $this->isMitraPintar() && $this->mitra_status === 'rejected';
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
     * Cek apakah skema pembayaran adalah Tunai (Cash Keras)
     */
    public function isCash(): bool
    {
        if ($this->payment_scheme) {
            return in_array($this->payment_scheme, ['cash', 'tunai', 'tunai_keras'], true);
        }
        $nupMethod = $this->nupRegistration?->payment_method;
        return in_array($nupMethod, ['tunai', 'cash', 'tunai_keras'], true);
    }

    /**
     * Cek apakah skema pembayaran adalah Tunai Bertahap (Termin Hari)
     */
    public function isTunaiBertahap(): bool
    {
        return $this->payment_scheme === 'tunai_bertahap';
    }

    /**
     * Cek apakah skema pembayaran adalah In-House Developer (Cicilan Bulanan)
     */
    public function isInHouse(): bool
    {
        return in_array($this->payment_scheme, ['inhouse', 'installment'], true);
    }

    /**
     * Cek apakah skema pembayaran adalah KPR Bank
     */
    public function isKpr(): bool
    {
        if ($this->payment_scheme) {
            return in_array($this->payment_scheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp'], true);
        }
        $nupMethod = $this->nupRegistration?->payment_method;
        return in_array($nupMethod, ['kpr', 'kpr_bank', 'kpr_bank_flpp'], true);
    }

    /**
     * Cek apakah UTK dihapuskan (khusus skema Tunai)
     */
    public function isUtkWaived(): bool
    {
        return $this->isCash();
    }

    /**
     * Check if UTK is paid or waived for cash
     */
    public function isUtkPaid(): bool
    {
        // Khusus tunai: UTK dihapus, otomatis dianggap selesai
        if ($this->isUtkWaived()) {
            return true;
        }
        return $this->utkPayments()->where('status', 'paid')->exists() || $this->utk_payment_status === 'paid';
    }

    /**
     * Check if UTK payment deadline has passed
     */
    public function isUtkPaymentDeadlinePassed(): bool
    {
        $utkPayment = $this->getLatestUtkPayment();
        if (!$utkPayment || $utkPayment->status === 'paid') {
            return false;
        }
        return $utkPayment->expired_at && now()->gt($utkPayment->expired_at);
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
     * Get UTK payment status
     */
    public function getUtkPaymentStatus(): string
    {
        $utkPayment = $this->utkPayments()->latest()->first();
        if (!$utkPayment) {
            return 'unpaid';
        }
        return $utkPayment->status;
    }

    /**
     * Get latest UTK payment
     */
    public function getLatestUtkPayment()
    {
        return $this->utkPayments()->latest()->first();
    }
}