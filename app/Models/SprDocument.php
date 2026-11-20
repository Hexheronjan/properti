<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'buyer_name',
        'buyer_nik',
        'buyer_phone',
        'buyer_address',
        'nup_registration_id',
        'unit_selection_id',
        'kpr_screening_id',
        'spr_number',
        'spr_date',
        'unit_price',
        'down_payment',
        'remaining_amount',
        'payment_scheme',
        'payment_schedule',
        'due_date',
        'tenor_months',
        'penalty_percent',
        'status',
        'argo_deadline',
        'bank_submission_status',
        'sp3k_number',
        'sp3k_file',
        'sp3k_approved_amount',
        'sp3k_notes',
        'akad_date',
        'akad_location',
        'akad_notary',
        'akad_notes',
        'akad_checklist',
        'is_akad_completed',
        'bast_date',
        'bast_location',
        'bast_file',
        'bast_notes',
        'terms_conditions',
        'document_file',
        'issued_date',
        'issued_by',
        'ematerai_status',
        'ematerai_id',
        'ematerai_url',
        'ematerai_qr_code',
    ];

    protected $casts = [
        'spr_date' => 'date',
        'due_date' => 'date',
        'tenor_months' => 'integer',
        'penalty_percent' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'sp3k_approved_amount' => 'decimal:2',
        'argo_deadline' => 'datetime',
        'akad_date' => 'datetime',
        'akad_checklist' => 'array',
        'bast_date' => 'datetime',
        'is_akad_completed' => 'boolean',
        'issued_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($spr) {
            if ($spr->status === 'paid') {
                if ($spr->unitSelection?->housingUnit && $spr->unitSelection->housingUnit->status !== 'sold') {
                    $spr->unitSelection->housingUnit->update(['status' => 'sold']);
                }
            }
        });
    }

    /**
     * Cek apakah argo 7 hari KPR sudah expired
     */
    public function isArgoExpired(): bool
    {
        return $this->argo_deadline && now()->greaterThan($this->argo_deadline);
    }

    /**
     * Hitung sisa waktu argo KPR
     */
    public function getArgoRemainingText(): string
    {
        if (!$this->argo_deadline) return '-';
        if ($this->isArgoExpired()) return 'LEWAT DEADLINE';

        $diff = now()->diff($this->argo_deadline);
        if ($diff->days > 0) {
            return "{$diff->days} hari {$diff->h} jam lagi";
        }
        return "{$diff->h} jam {$diff->i} menit lagi";
    }

    public function isSp3kApproved(): bool
    {
        return $this->bank_submission_status === 'sp3k_approved';
    }

    public function isSp3kRejected(): bool
    {
        return $this->bank_submission_status === 'sp3k_rejected';
    }

    /**
     * Get the user for the SPR document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the NUP registration for the SPR document.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the unit selection for the SPR document.
     */
    public function unitSelection()
    {
        return $this->belongsTo(UnitSelection::class);
    }

    /**
     * Snapshot identitas pemesan untuk lembar SPR resmi
     */
    public function getBuyerNameAttribute(): string
    {
        return $this->attributes['buyer_name'] 
            ?? ($this->nupRegistration?->full_name ?? ($this->user?->name ?? 'Pemesan'));
    }

    public function getBuyerNikAttribute(): string
    {
        return $this->attributes['buyer_nik'] 
            ?? ($this->nupRegistration?->nik ?? ($this->user?->nik ?? '-'));
    }

    public function getBuyerPhoneAttribute(): string
    {
        return $this->attributes['buyer_phone'] 
            ?? ($this->nupRegistration?->phone ?? ($this->user?->phone ?? '-'));
    }

    public function getBuyerAddressAttribute(): string
    {
        return $this->attributes['buyer_address'] 
            ?? ($this->nupRegistration?->address ?? ($this->user?->address ?? '-'));
    }

    /**
     * Get the KPR screening for the SPR document.
     */
    public function kprScreening()
    {
        return $this->belongsTo(KprScreening::class);
    }

    /**
     * Dapatkan data broker/marketing yang menangani pemesanan ini
     */
    public function getBroker(): ?Broker
    {
        $nup = $this->nupRegistration;
        if (!$nup) {
            return null;
        }

        if ($nup->relationLoaded('broker') && $nup->broker) {
            return $nup->broker;
        }

        if ($nup->broker_id) {
            return Broker::find($nup->broker_id);
        }

        if (!empty($nup->agent_name)) {
            $matched = Broker::where('name', 'LIKE', '%' . trim($nup->agent_name) . '%')->first();
            if ($matched) {
                return $matched;
            }
        }

        return Broker::where('is_active', true)->first();
    }

    /**
     * Check if SPR is issued
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Dapatkan data Developer Company dari unit rumah yang dipesan
     */
    public function getCompany(): ?DeveloperCompany
    {
        $company = $this->unitSelection?->housingUnit?->housingProject?->company;
        if ($company) {
            return $company;
        }

        $project = $this->nupRegistration?->housingProject;
        if ($project && $project->company) {
            return $project->company;
        }

        return null;
    }

    /**
     * Check if SPR is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Konversi angka bulan ke format Romawi (I, II, III, dst)
     */
    public static function romanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }

    /**
     * Generate SPR number dengan format No. 001/SPR/PT/IV/THN
     */
    public static function generateSprNumber(?string $companyCode = null): string
    {
        $year = now()->year;
        $romanMonth = self::romanMonth((int) now()->month);
        $code = strtoupper(trim($companyCode ?: 'PT'));

        $count = self::whereYear('created_at', $year)->count() + 1;
        do {
            $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);
            $candidate = "{$sequence}/SPR/{$code}/{$romanMonth}/{$year}";
            $count++;
        } while (self::where('spr_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Dapatkan label skema pembiayaan (misal KPR diganti In-House jika inhouse)
     */
    public function getPaymentSchemeLabelAttribute(): string
    {
        $scheme = strtolower($this->payment_scheme ?? '');
        return match ($scheme) {
            'cash', 'tunai', 'tunai_keras' => 'Tunai Keras (Lunas)',
            'tunai_bertahap' => 'Tunai Bertahap (Termin Hari)',
            'installment', 'inhouse', 'in_house' => 'In-House (Cicilan Pengembang)',
            'mitra_pintar' => 'Kredit Mitra Pintar',
            default => 'KPR Bank',
        };
    }

    /**
     * Scope a query to only include issued SPR documents.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include draft SPR documents.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the KPR payments for this SPR document.
     */
    public function kprPayments()
    {
        return $this->hasMany(KprPayment::class);
    }

    /**
     * Check if KPR is fully paid
     */
    public function isKprFullyPaid(): bool
    {
        if ($this->status === 'paid' || $this->is_akad_completed) {
            return true;
        }

        $totalPaid = $this->kprPayments()->where('status', 'success')->sum('amount');
        return $totalPaid >= $this->remaining_amount;
    }

    /**
     * Get total paid amount
     */
    public function getTotalPaid(): float
    {
        return $this->kprPayments()->where('status', 'success')->sum('amount');
    }

    /**
     * Get total paid amount (accessor for attribute access)
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->getTotalPaid();
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmount(): float
    {
        return $this->getSisaPembayaran();
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgress(): float
    {
        return $this->getTotalPaymentProgress();
    }

    /**
     * Get total amount already paid (hanya pembayaran yang telah sukses diverifikasi)
     */
    public function getTotalSudahDibayar(): float
    {
        $successfulPayments = (float) $this->kprPayments()->where('status', 'success')->sum('amount');

        // Jika status SPR sudah ditandai 'paid' (lunas total)
        if ($this->status === 'paid') {
            return max((float) $this->unit_price, (float) $this->down_payment + $successfulPayments);
        }

        return $successfulPayments;
    }

    /**
     * Get remaining amount based on full house price (unit_price - total paid)
     */
    public function getSisaPembayaran(): float
    {
        if ($this->payment_scheme === 'kpr' && ($this->status === 'paid' || $this->is_akad_completed)) {
            return 0;
        }

        return max(0, (float) $this->unit_price - $this->getTotalSudahDibayar());
    }

    /**
     * Get total payment progress percentage based on full house price
     */
    public function getTotalPaymentProgress(): float
    {
        if ($this->unit_price <= 0) return 0;
        if ($this->payment_scheme === 'kpr' && ($this->status === 'paid' || $this->is_akad_completed)) {
            return 100;
        }
        $progress = ($this->getTotalSudahDibayar() / $this->unit_price) * 100;
        return min($progress, 100);
    }

    /**
     * Get payment status label: Lunas or Belum Lunas
     */
    public function getPaymentStatus(): string
    {
        return $this->isFullyPaid() ? 'Lunas' : 'Belum Lunas';
    }

    /**
     * Check if house payment is fully paid (all schemes: kpr, cash, installment)
     */
    public function isFullyPaid(): bool
    {
        if ($this->status === 'paid') {
            return true;
        }

        if ($this->payment_scheme === 'kpr' && $this->is_akad_completed) {
            return true;
        }

        return $this->getSisaPembayaran() <= 0;
    }

    /**
     * Check if down payment (DP) is fully paid or zero (bebas DP)
     */
    public function isDpPaid(): bool
    {
        if ($this->down_payment <= 0) {
            return true;
        }

        if ($this->status === 'paid') {
            return true;
        }

        $dpPaid = (float) $this->kprPayments()
            ->where('status', 'success')
            ->where(function($q) {
                $q->where('payment_type', 'dp')
                  ->orWhere('payment_type', 'down_payment');
            })
            ->sum('amount');

        if ($dpPaid >= $this->down_payment) {
            return true;
        }

        return $this->getTotalSudahDibayar() >= $this->down_payment;
    }

    /**
     * Check if there is a pending down payment waiting for verification
     */
    public function hasPendingDpPayment(): bool
    {
        return $this->kprPayments()
            ->where('status', 'pending')
            ->where(function($q) {
                $q->where('payment_type', 'dp')
                  ->orWhere('payment_type', 'down_payment');
            })
            ->exists();
    }

    /**
     * Dapatkan tanggal pelunasan (dari transaksi pembayaran terakhir atau updated_at)
     */
    public function getPaidDateAttribute(): ?\Carbon\Carbon
    {
        if ($this->status !== 'paid' && !$this->isFullyPaid()) {
            return null;
        }

        $lastPayment = $this->kprPayments()->where('status', 'success')->latest('payment_date')->first();
        if ($lastPayment) {
            return $lastPayment->payment_date ? \Carbon\Carbon::parse($lastPayment->payment_date) : $lastPayment->created_at;
        }

        return $this->updated_at;
    }

    /**
     * Dapatkan data project perumahan
     */
    public function getProject(): ?HousingProject
    {
        return $this->unitSelection?->housingUnit?->housingProject
            ?: $this->nupRegistration?->housingProject;
    }

    /**
     * Hitung tanggal jatuh tempo pembayaran berikutnya / batas termin
     */
    public function getCalculatedDueDate(): ?\Carbon\Carbon
    {
        if ($this->due_date) {
            return \Carbon\Carbon::parse($this->due_date);
        }

        $project = $this->getProject();
        $baseDate = $this->issued_date ?: ($this->spr_date ? \Carbon\Carbon::parse($this->spr_date) : $this->created_at);

        if ($this->payment_scheme === 'tunai_bertahap') {
            $days = (int) ($project?->cash_due_days ?? 30);
            return $baseDate ? $baseDate->copy()->addDays($days) : now()->addDays(30);
        }

        if (in_array($this->payment_scheme, ['inhouse', 'installment'])) {
            $paidCount = $this->kprPayments()->where('status', 'success')->where('payment_type', 'installment')->count();
            $dueDay = (int) ($project?->inhouse_due_day ?? 10);
            $target = ($baseDate ? $baseDate->copy() : now())->addMonths($paidCount + 1);
            $safeDay = min(28, max(1, $dueDay));
            return $target->setDay($safeDay);
        }

        return $baseDate ? $baseDate->copy()->addDays(30) : now()->addDays(30);
    }

    /**
     * Cek apakah pembayaran SPR telah melewati tanggal jatuh tempo
     */
    public function isOverdue(): bool
    {
        if ($this->isFullyPaid()) {
            return false;
        }

        $dueDate = $this->getCalculatedDueDate();
        return $dueDate && now()->greaterThan($dueDate);
    }

    /**
     * Dapatkan detail kalkulasi denda otomatis jika jatuh tempo terlewat
     */
    public function getOverduePenaltyDetails(?float $targetAmount = null, string $paymentType = 'installment'): array
    {
        $dueDate = $this->getCalculatedDueDate();
        $isOverdue = $this->isOverdue();
        $project = $this->getProject();
        $isCash = in_array($this->payment_scheme, ['cash', 'tunai_bertahap']);

        // Default persentase denda per hari (jika tunai) atau per bulan (jika in-house)
        $penaltyPercent = (float) ($this->penalty_percent ?: (
            $isCash
                ? ($project?->cash_penalty_percent ?? 0.10)
                : ($project?->inhouse_penalty_percent ?? 1.00)
        ));

        // Tentukan nominal pokok yang menjadi dasar perhitungan
        $baseAmount = $targetAmount;
        if (!$baseAmount || $baseAmount <= 0) {
            if ($paymentType === 'dp') {
                $baseAmount = (float) $this->down_payment;
            } elseif ($paymentType === 'full_payment' || $isCash) {
                $baseAmount = (float) $this->getSisaPembayaran();
            } else {
                // In-House per bulan
                $tenor = max(1, (int) ($this->tenor_months ?: ($project?->inhouse_max_tenor_months ?? 12)));
                $baseAmount = round((float) $this->remaining_amount / $tenor);
            }
        }

        if (!$isOverdue || !$dueDate) {
            return [
                'is_overdue'       => false,
                'due_date'         => $dueDate,
                'days_overdue'     => 0,
                'months_overdue'   => 0,
                'penalty_percent'  => $penaltyPercent,
                'penalty_unit'     => $isCash ? 'hari' : 'bulan',
                'penalty_amount'   => 0.0,
                'base_amount'      => (float) $baseAmount,
                'total_amount'     => (float) $baseAmount,
            ];
        }

        // Hitung hari dan bulan keterlambatan
        $daysOverdue = max(1, (int) $dueDate->diffInDays(now(), false));
        $monthsOverdue = max(1, (int) ceil($daysOverdue / 30));

        // Denda Tunai = Pokok * (Persen / 100) * Hari Keterlambatan
        // Denda In-House = Pokok * (Persen / 100) * Bulan Keterlambatan
        if ($isCash) {
            $penaltyAmount = round($baseAmount * ($penaltyPercent / 100) * $daysOverdue);
        } else {
            $penaltyAmount = round($baseAmount * ($penaltyPercent / 100) * $monthsOverdue);
        }

        return [
            'is_overdue'       => true,
            'due_date'         => $dueDate,
            'days_overdue'     => $daysOverdue,
            'months_overdue'   => $monthsOverdue,
            'penalty_percent'  => $penaltyPercent,
            'penalty_unit'     => $isCash ? 'hari' : 'bulan',
            'penalty_amount'   => (float) $penaltyAmount,
            'base_amount'      => (float) $baseAmount,
            'total_amount'     => (float) ($baseAmount + $penaltyAmount),
        ];
    }

    /**
     * Dapatkan nominal angsuran cicilan bulanan in-house
     */
    public function getMonthlyInstallmentAmount(): float
    {
        if (in_array($this->payment_scheme, ['cash', 'tunai_bertahap'])) {
            return 0.0;
        }

        $sisa = (float) $this->getSisaPembayaran();
        if ($this->tenor_months <= 0) {
            return $sisa;
        }

        $project = $this->getProject();
        $tenor = max(1, (int) ($this->tenor_months ?: ($project?->inhouse_max_tenor_months ?? 12)));
        $monthly = round((float) $this->remaining_amount / $tenor);

        return min($monthly, $sisa);
    }
}