<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasModelHelpers
 * 
 * Provides reusable query helpers for models.
 * Can be used both statically and as instance methods.
 */
trait HasModelHelpers
{
    /**
     * Apply filters to a query builder.
     * 
     * Usage: 
     * - Static: Model::applyFilters($query, $filters)
     * - Scoped: Model::query()->applyFilters($filters)
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Handle different filter patterns
            match (true) {
                // Exact match for IDs and foreign keys
                str_ends_with($key, '_id') => $query->where($key, $value),

                // Date range filters
                str_ends_with($key, '_from') => $query->where(
                    str_replace('_from', '', $key),
                    '>=',
                    $value
                ),
                str_ends_with($key, '_to') => $query->where(
                    str_replace('_to', '', $key),
                    '<=',
                    $value
                ),

                // Boolean filters
                is_bool($value) || in_array($value, ['0', '1', 'true', 'false']) =>
                $query->where($key, filter_var($value, FILTER_VALIDATE_BOOLEAN)),

                // Partial match for text fields (number fields as strings)
                default => $query->where($key, 'LIKE', "%{$value}%"),
            };
        }

        return $query;
    }

    /**
     * Static method to get filtered results.
     * 
     * Usage: Model::filtered($filters)->get()
     *
     * @param array $filters
     * @return Builder
     */
    public static function filtered(array $filters): Builder
    {
        return static::query()->applyFilters($filters);
    }

    /**
     * Define default relations to load for this model.
     * Override this method in your model to specify relations.
     *
     * @return array
     */
    public function defaultRelations(): array
    {
        return [];
    }

    /**
     * Scope to load default relations.
     * 
     * Usage: Model::withDefaultRelations()->find($id)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithDefaultRelations(Builder $query): Builder
    {
        $relations = $this->defaultRelations();

        if (!empty($relations)) {
            return $query->with($relations);
        }

        return $query;
    }

    /**
     * Find a model by ID with default relations or fail.
     * 
     * Usage: Model::findOrFailWithRelations($id)
     *
     * @param int|string $id
     * @return Model
     */
    public static function findOrFailWithRelations(int|string $id): Model
    {
        return static::query()
            ->withDefaultRelations()
            ->findOrFail($id);
    }

    /**
     * Get paginated results with optional filters and relations.
     * 
     * Usage: Model::paginateWithFilters($filters, $perPage)
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function paginateWithFilters(array $filters = [], int $perPage = 15)
    {
        return static::query()
            ->withDefaultRelations()
            ->applyFilters($filters)
            ->latest()
            ->paginate($perPage);
    }
}
