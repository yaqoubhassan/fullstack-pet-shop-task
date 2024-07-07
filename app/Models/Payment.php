<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'type', 'details'];

    protected $casts = [
        'details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->uuid) {
                $payment->uuid = (string) Str::uuid();
            }
        });
    }

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
