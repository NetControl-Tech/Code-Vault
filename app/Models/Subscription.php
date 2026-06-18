<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'purchase_token',
        'product_id',
        'status',
        'starts_at',
        'expires_at',
        'latest_notification_type',
        'raw_payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'latest_notification_type' => 'integer',
        'raw_payload' => 'array',
    ];

    /**
     * Whether this subscription currently grants premium access.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && now()->lessThanOrEqualTo($this->expires_at);
    }
}
