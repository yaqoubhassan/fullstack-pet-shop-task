<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'uuid',
        'address',
        'phone_number',
        'is_marketing',
        'is_admin',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
        $query = $query->where('is_admin', false);
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

        if (isset($filters['created_at'])) {
            $query->whereDate('created_at', $filters['created_at']);
        }

        if (isset($filters['is_marketing'])) {
            $query->where('is_marketing', $filters['is_marketing']);
        }

        if (isset($filters['first_name'])) {
            $query->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($filters['first_name']) . '%']);
        }

        if (isset($filters['email'])) {
            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($filters['email']) . '%']);
        }

        if (isset($filters['phone_number'])) {
            $query->whereRaw('LOWER(phone_number) LIKE ?', ['%' . strtolower($filters['phone_number']) . '%']);
        }

        if (isset($filters['address'])) {
            $query->whereRaw('LOWER(address) LIKE ?', ['%' . strtolower($filters['address']) . '%']);
        }

        return $query;
    }
}
