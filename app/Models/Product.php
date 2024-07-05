<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;

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
}
