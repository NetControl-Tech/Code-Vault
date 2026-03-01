<?php

namespace App\Models;

use App\Enums\LicenseCodeStatus;
use Illuminate\Database\Eloquent\Model;

class LicenseCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial',
        'pin_hash',
        'status',
        'device_id',
        'activated_at',
        'redeemed_at',
        'expires_at',
        'duration_days',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activated_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'duration_days' => 'integer',
        'serial' => 'integer',
        'status' => LicenseCodeStatus::class,
    ];

    /**
     * Get the device that owns the license code.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
