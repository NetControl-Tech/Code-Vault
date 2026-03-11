<?php

namespace App\Models;

use App\Enums\BlocklistCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlocklistDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'category',
    ];

    protected $casts = [
        'category' => BlocklistCategory::class,
    ];

    public function scopeFamily(Builder $query): Builder
    {
        return $query->where('category', BlocklistCategory::Family->value);
    }

    public function scopeSocial(Builder $query): Builder
    {
        return $query->where('category', BlocklistCategory::Social->value);
    }

    public function scopeAds(Builder $query): Builder
    {
        return $query->where('category', BlocklistCategory::Ads->value);
    }

    public function scopePrivacy(Builder $query): Builder
    {
        return $query->where('category', BlocklistCategory::Privacy->value);
    }
}
