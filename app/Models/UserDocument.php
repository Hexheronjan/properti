<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kpr_screening_id',
        'document_type',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'rejection_reason',
        'uploaded_at',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the user for the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the KPR screening for the document.
     */
    public function kprScreening()
    {
        return $this->belongsTo(KprScreening::class);
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if document is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if document is verified
     */
    public function isVerified(): bool
    {
        return !empty($this->verified_at) && $this->status === 'approved';
    }

    /**
     * Scope a query to only include approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected documents.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by document type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}