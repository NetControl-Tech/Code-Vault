<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Filterable
 * Provides query filtering functionality for models
 */
trait Filterable
{
    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // Search by name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Filter by active status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     * @param string $sortBy
     * @param string $sortOrder
     * @return Builder
     */
    public function scopeSorted(Builder $query, string $sortBy = 'created_at', string $sortOrder = 'desc'): Builder
    {
        $allowedColumns = ['id', 'name', 'email', 'created_at', 'is_active'];

        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder);
    }
}
