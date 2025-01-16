<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'icon',
        'description',
        'parent',
        'slug'
    ];

    public function child(): HasMany
    {
        return $this->hasMany(Category::class, 'parent', 'id');
    }

    // Helper
    public function getIconAttribute($value): string
    {
        if (!$value) {
            return '';
        }

        if (strpos($value, 'http') === 0 || strpos($value, 'https') === 0) {
            return $value;
        }

        return env('APP_URL').'/storage/'.$value;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
