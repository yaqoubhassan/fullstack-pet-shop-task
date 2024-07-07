<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Models\File;
use App\Models\Category;
use App\Models\Brand;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'category_uuid', 'title', 'price', 'description', 'metadata'];

    protected $casts = [
        'price' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->uuid = (string) Str::uuid();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_uuid', 'uuid');
    }

    // public function brand(): BelongsTo
    // {
    //     return $this->belongsTo(Brand::class, 'metadata->0->brand', 'uuid');
    // }

    // public function getBrandAttribute()
    // {
    //     $metadata = $this->metadata;

    //     if (isset($metadata[0]['brand'])) {
    //         return Brand::where('uuid', $metadata[0]['brand'])->first();
    //     }

    //     return null;
    // }

    // public function image(): BelongsTo
    // {
    //     return $this->belongsTo(File::class, 'metadata->image', 'uuid');
    // }

    public function scopeWhereMetadataBrand($query, $brand)
    {
        return $query->where(function ($q) use ($brand) {
            $q->where('metadata->0->brand', $brand)
            ->orWhereRaw("JSON_CONTAINS(metadata, ?)", ['{"brand": "' . $brand . '"}'])
            ->orWhereRaw("JSON_CONTAINS(metadata, ?)", ['[{"brand": "' . $brand . '"}]']);
        });
    }

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

        if (isset($filters['title'])) {
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($filters['title']) . '%']);
        }

        if (isset($filters['category'])) {
            $query->where('category_uuid', $filters['category']);
        }

        if (isset($filters['price'])) {
            $query->where('price', $filters['price']);
        }

        if (isset($filters['brand'])) {
            $query->whereMetadataBrand($filters['brand']);
        }

        return $query;
    }
}
