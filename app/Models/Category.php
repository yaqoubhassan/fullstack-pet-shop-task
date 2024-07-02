<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'slug'
    ];

    /**
     * Scope a query to apply filters and sorting.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilterAndSort(Builder $query, array $filters): Builder
    {
        if (isset($filters['sortBy'])) {
            if ($filters['sortBy'] === 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sortBy'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        }

        if (isset($filters['desc'])) {
            if ($filters['desc'] === true || $filters['desc'] === 'true') {
                $query->orderBy('title', 'desc');
            } elseif ($filters['desc'] === false || $filters['desc'] === 'false') {
                $query->orderBy('title', 'asc');
            }
        }

        return $query;
    }
}
