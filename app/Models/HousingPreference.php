<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'nup_registration_id',
        'housing_name',
        'broker_name',
        'marketing_name',
        'marketing_contact',
        'purchase_plan',
        'priority_order',
    ];

    /**
     * Get the NUP registration that owns the housing preference.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Relasi ke proyek perumahan berdasarkan nama
     */
    public function housingProject()
    {
        return $this->belongsTo(HousingProject::class, 'housing_name', 'name');
    }

    /**
     * Label human-friendly untuk rencana pembelian
     */
    public function getPurchasePlanLabelAttribute(): string
    {
        return match($this->purchase_plan) {
            'kpr_bank_flpp', 'kpr_bank' => 'KPR Bank (FLPP / Komersial)',
            'inhouse', 'installment' => 'Tunai Bertahap / In-House',
            'tunai', 'cash' => 'Tunai Keras (Cash Lunas)',
            'kredit_mitra_pintar', 'mitra_pintar' => 'Kredit Mitra Pintar',
            default => ucfirst(str_replace('_', ' ', $this->purchase_plan ?? '-')),
        };
    }
}
