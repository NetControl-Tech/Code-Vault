<?php

namespace App\Models;

use App\Enums\BlocklistCategory;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportedUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'domain',
        'device_id',
        'status',
        'approved_category',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'approved_category' => BlocklistCategory::class,
        'reviewed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
