<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'name', 'path', 'size', 'type'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'metadata->image', 'uuid');
    }
}
