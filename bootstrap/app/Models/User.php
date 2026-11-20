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
    'occupation'
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
     * Check if user has any admin role
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [
            'super_admin',
            'operator',
            'pihak_bank'
        ]);
    }
}

