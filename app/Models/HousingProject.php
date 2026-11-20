<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'broker_id',
        'name',
        'location',
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'full_address',
        'description',
        'image',
        'site_plan',
        'kkpr_number',
        'doc_kkpr',
        'doc_permit_other',
        'supported_payment_schemes',
        'is_active',
        'commitment_fee',
        'commitment_deadline_hours',
        'nup_deadline_days',
        'screening_fee',
        'cash_due_days',
        'cash_penalty_percent',
        'cash_allow_installments',
        'inhouse_max_tenor_months',
        'inhouse_interest_rate',
        'inhouse_due_day',
        'inhouse_penalty_percent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commitment_fee' => 'decimal:2',
        'commitment_deadline_hours' => 'integer',
        'nup_deadline_days' => 'integer',
        'screening_fee' => 'decimal:2',
        'supported_payment_schemes' => 'array',
        'cash_due_days' => 'integer',
        'cash_penalty_percent' => 'decimal:2',
        'cash_allow_installments' => 'boolean',
        'inhouse_max_tenor_months' => 'integer',
        'inhouse_interest_rate' => 'decimal:2',
        'inhouse_due_day' => 'integer',
        'inhouse_penalty_percent' => 'decimal:2',
    ];

    /**
     * Biaya pokok UTK yang diatur operator
     */
    public function getUtkBaseAmount(): float
    {
        return (float) ($this->commitment_fee ?? 5000000);
    }

    /**
     * Nilai PPN 11% dari UTK
     */
    public function getUtkPpnAmount(): float
    {
        return round($this->getUtkBaseAmount() * 0.11, 2);
    }

    /**
     * Nilai VE 20% dari UTK
     */
    public function getUtkVeAmount(): float
    {
        return round($this->getUtkBaseAmount() * 0.20, 2);
    }

    /**
     * Total UTK yang ditagihkan = Pokok + PPN 11% + VE 20%
     */
    public function getTotalUtkAmount(): float
    {
        $base = $this->getUtkBaseAmount();
        return $base + round($base * 0.11, 2) + round($base * 0.20, 2);
    }

    /**
     * Biaya pokok Screening KPR yang diatur operator (default: 150000)
     */
    public function getScreeningBaseAmount(): float
    {
        return (float) ($this->screening_fee ?? 150000);
    }

    /**
     * Nilai PPN 11% dari biaya pokok Screening
     */
    public function getScreeningPpnAmount(): float
    {
        return round($this->getScreeningBaseAmount() * 0.11, 2);
    }

    /**
     * Nilai VE 20% dari biaya pokok Screening
     */
    public function getScreeningVeAmount(): float
    {
        return round($this->getScreeningBaseAmount() * 0.20, 2);
    }

    /**
     * Total biaya Screening yang ditagihkan = Pokok + PPN 11% + VE 20%
     */
    public function getTotalScreeningAmount(): float
    {
        $base = $this->getScreeningBaseAmount();
        return $base + round($base * 0.11, 2) + round($base * 0.20, 2);
    }

    /**
     * Skema pembayaran standar yang tersedia
     */
    public static function availablePaymentSchemes(): array
    {
        return [
            'cash' => [
                'label' => 'Tunai (Lunas Langsung / Termin Hari)',
                'icon' => '💵',
                'desc' => 'Pembayaran lunas langsung atau termin bertahap per jangka hari'
            ],
            'inhouse' => [
                'label' => 'In-House Developer',
                'icon' => '🏢',
                'desc' => 'Cicilan langsung ke developer per bulan'
            ],
            'kpr_bank' => [
                'label' => 'KPR Bank (FLPP / Komersial)',
                'icon' => '🏦',
                'desc' => 'Kredit pemilikan rumah via bank rekanan'
            ],
            'mitra_pintar' => [
                'label' => 'Kredit Mitra Pintar',
                'icon' => '🤝',
                'desc' => 'Pembiayaan khusus mitra perumahan'
            ],
        ];
    }

    /**
     * Cek apakah proyek ini mendukung skema pembayaran tertentu
     */
    public function supportsScheme(string $scheme): bool
    {
        $schemes = $this->supported_payment_schemes ?? ['cash', 'kpr_bank'];
        if (in_array($scheme, $schemes, true)) {
            return true;
        }
        // Kompatibilitas alias Tunai (Cash Keras)
        if (in_array($scheme, ['cash', 'tunai', 'tunai_keras'], true) && in_array('cash', $schemes, true)) {
            return true;
        }
        // Kompatibilitas alias In-House
        if (in_array($scheme, ['inhouse', 'installment'], true) && (in_array('inhouse', $schemes, true) || in_array('installment', $schemes, true))) {
            return true;
        }
        return false;
    }

    /**
     * Dapatkan label alamat lengkap
     */
    public function getFullAddressFormattedAttribute(): string
    {
        $parts = array_filter([
            $this->full_address,
            $this->village ? 'Desa/Kel. ' . $this->village : null,
            $this->district ? 'Kec. ' . $this->district : null,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return !empty($parts) ? implode(', ', $parts) : ($this->location ?? '-');
    }

    public function company()
    {
        return $this->belongsTo(DeveloperCompany::class, 'company_id');
    }

    public function broker()
    {
        return $this->belongsTo(Broker::class, 'broker_id');
    }

    public function housingUnits()
    {
        return $this->hasMany(HousingUnit::class, 'project_id');
    }

    public function units()
    {
        return $this->housingUnits();
    }

    /**
     * Scope proyek yang memiliki minimal 1 unit berstatus available
     */
    public function scopeHasAvailableUnits($query)
    {
        return $query->whereHas('housingUnits', function ($q) {
            $q->where('status', 'available');
        });
    }

    public function sprClauses()
    {
        return $this->hasMany(SprClause::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
