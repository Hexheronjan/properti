<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'office_name',
        'contact',
        'address',
        'signature_image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function company()
    {
        return $this->belongsTo(DeveloperCompany::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function housingProjects()
    {
        return $this->hasMany(HousingProject::class, 'broker_id');
    }

    public function nupRegistrations()
    {
        return $this->hasMany(NupRegistration::class, 'broker_id');
    }
}
