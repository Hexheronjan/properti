<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 
    'email', 
    'password',
    'role',
    'phone',
    'google_id',
    'avatar',
    'address',
    'nik',
    'birth_date',
    'occupation',
    'company_id',
    'approval_status'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /**
     * Get the NUP registrations for the user.
     */
    public function nupRegistrations()
    {
        return $this->hasMany(\App\Models\NupRegistration::class);
    }

    /**
     * Get the unit selections for the user.
     */
    public function unitSelections()
    {
        return $this->hasMany(\App\Models\UnitSelection::class);
    }

    /**
     * Get the KPR screenings for the user.
     */
    public function kprScreenings()
    {
        return $this->hasMany(\App\Models\KprScreening::class);
    }

    /**
     * Get the SPR documents for the user.
     */
    public function sprDocuments()
    {
        return $this->hasMany(\App\Models\SprDocument::class);
    }

    /**
     * Get the payment transactions for the user.
     */
    public function paymentTransactions()
    {
        return $this->hasMany(\App\Models\PaymentTransaction::class);
    }

    /**
     * Check if user has unpaid KPR
     */
    public function hasUnpaidKpr(): bool
    {
        // Check if user has any KPR SPR that is not fully paid
        $unpaidSpr = $this->sprDocuments()
            ->where('payment_scheme', 'kpr')
            ->where('status', 'issued')
            ->get()
            ->filter(function ($spr) {
                return !$spr->isKprFullyPaid();
            })
            ->isNotEmpty();

        return $unpaidSpr;
    }

    /**
     * Check if user has any house that is not fully paid (all schemes: kpr, cash, installment)
     * Digunakan untuk memblokir pemesanan unit baru
     */
    public function hasUnpaidHouse(): bool
    {
        return $this->sprDocuments()
            ->where('status', 'issued')
            ->get()
            ->filter(function ($spr) {
                return !$spr->isFullyPaid();
            })
            ->isNotEmpty();
    }

    /**
     * Check if user has any pending KPR payments
     */
    public function hasPendingKprPayments(): bool
    {
        return $this->kprScreenings()
            ->whereHas('sprDocuments', function($query) {
                $query->where('payment_scheme', 'kpr')
                    ->where('status', 'issued');
            })
            ->whereHas('sprDocuments.kprPayments', function($query) {
                $query->where('status', 'pending');
            })
            ->exists();
    }

    /**
     * Check if user can apply for new KPR
     */
    public function canApplyForKpr(): bool
    {
        return !$this->hasUnpaidKpr() && !$this->hasPendingKprPayments();
    }

    /**
     * Get an active paid NUP registration that is currently available
     * (1 NUP = 1 Unit Rumah: belum digunakan untuk memilih unit yang aktif).
     */
    public function getAvailableNupForSelection(): ?\App\Models\NupRegistration
    {
        return $this->nupRegistrations()
            ->where('payment_status', 'paid')
            ->where('is_forfeited', false)
            ->where('is_expired', false)
            ->whereDoesntHave('unitSelections', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->where(function ($query) {
                // Jangan sertakan NUP KPR yang screening-nya ditolak/dibatalkan tanpa alih skema
                $query->where('payment_method', '!=', 'kpr_bank_flpp')
                    ->orWhereDoesntHave('kprScreening', function ($sq) {
                        $sq->where('status', 'rejected')
                            ->orWhere('participant_decision', 'not_agree');
                    });
            })
            ->latest()
            ->first();
    }

    /**
     * Get the WhatsApp notifications for the user.
     */
    public function whatsappNotifications()
    {
        return $this->hasMany(\App\Models\WhatsappNotification::class);
    }

    /**
     * Get the user documents for the user.
     */
    public function userDocuments()
    {
        return $this->hasMany(\App\Models\UserDocument::class);
    }

    /**
     * Get the developer company associated with this operator user.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\DeveloperCompany::class, 'company_id');
    }

    /**
     * Get the developer company managed by this user (if PIC/owner)
     */
    public function managedCompany()
    {
        return $this->hasOne(\App\Models\DeveloperCompany::class, 'user_id');
    }

    /**
     * Get the developer company ID for the operator (returns null for Super Admin).
     */
    public function getOperatorCompanyId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->isOperator()) {
            $id = $this->company_id ?? $this->managedCompany?->id;
            return $id ? (int) $id : null;
        }

        return null;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin marketing (mapped to operator/super_admin)
     */
    public function isAdminMarketing(): bool
    {
        return $this->role === 'operator' || $this->role === 'super_admin';
    }

    /**
     * Check if user is admin keuangan (mapped to operator/super_admin)
     */
    public function isAdminKeuangan(): bool
    {
        return $this->role === 'operator' || $this->role === 'super_admin';
    }

    /**
     * Check if user is admin screening (mapped to pihak_bank/super_admin)
     */
    public function isAdminScreening(): bool
    {
        return $this->role === 'pihak_bank' || $this->role === 'super_admin';
    }

    /**
     * Check if user is peserta
     */
    public function isPeserta(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if user is member
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if user is pihak bank
     */
    public function isPihakBank(): bool
    {
        return $this->role === 'pihak_bank';
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Check if user can post housing units
     */
    public function canPostUnit(): bool
    {
        return in_array($this->role, [
            'super_admin',
            'member',
            'pihak_bank',
            'operator'
        ]);
    }

    /**
     * Check if user is broker / agen pemasaran
     */
    public function isBroker(): bool
    {
        return $this->role === 'broker';
    }

    /**
     * Get broker record if user is broker
     */
    public function broker()
    {
        return $this->hasOne(Broker::class, 'user_id');
    }

    /**
     * Get all broker partnerships if user is broker
     */
    public function brokers()
    {
        return $this->hasMany(Broker::class, 'user_id');
    }

    /**
     * Check if user has any admin role
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [
            'admin',
            'superadmin',
            'super_admin',
            'operator',
            'pihak_bank'
        ]);
    }
}

