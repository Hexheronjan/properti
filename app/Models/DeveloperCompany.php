<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pic_name',
        'pic_nik',
        'pic_address',
        'pic_phone',
        'entity_type',
        'business_type',
        'company_name',
        'company_initials',
        'nib',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'signature_image',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Operator user associated with this company
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Super Admin who approved this company
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Housing projects owned by this company
     */
    public function housingProjects()
    {
        return $this->hasMany(HousingProject::class, 'company_id');
    }

    /**
     * Housing units owned by this company
     */
    public function housingUnits()
    {
        return $this->hasMany(HousingUnit::class, 'company_id');
    }

    /**
     * Bank accounts owned by this company
     */
    public function bankAccounts()
    {
        return $this->hasMany(DeveloperBankAccount::class);
    }

    public function activeBankAccounts()
    {
        return $this->hasMany(DeveloperBankAccount::class)->where('is_active', true);
    }

    public function brokers()
    {
        return $this->hasMany(Broker::class, 'company_id');
    }

    public function getBusinessTypeLabelAttribute(): string
    {
        if ($this->entity_type === 'perorangan') {
            return 'Perorangan';
        }

        $bt = strtolower($this->business_type ?? '');
        return match($bt) {
            'pt' => 'PT (Perseroan Terbatas)',
            'cv' => 'CV (Persekutuan Komanditer)',
            '' => 'Badan Usaha',
            default => strtoupper($this->business_type),
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
