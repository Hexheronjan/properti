<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'payable_amount',
        'applicable_to',
        'usage_limit',
        'used_count',
        'allowed_email',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'payable_amount' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
