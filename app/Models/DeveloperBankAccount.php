<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'developer_company_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'branch',
        'qr_code_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function developerCompany()
    {
        return $this->belongsTo(DeveloperCompany::class);
    }

    public function getFormattedLabelAttribute(): string
    {
        return "{$this->bank_name} - {$this->account_number} (a.n. {$this->account_holder_name})";
    }
}
