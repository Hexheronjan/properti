<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'notification_type',
        'message',
        'status',
        'gateway_message_id',
        'gateway_response',
        'sent_at',
        'delivered_at',
        'retry_count',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user for the WhatsApp notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification is sent
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if notification is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if notification is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if notification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope a query to only include sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by notification type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }
}