<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compound extends Model
{
    use HasFactory;

     protected $table = 'compounds';
    protected $fillable = [
        'user_id',
        'vendor_id',
        'product_id',
        'title',
        'slug',
        'image',
        'price',
        'description',
        'volume',
        'product_code',
        'manufacturing_date',
        'fragrance_family',
        'expire_date',
        'gender',
        'discount',
        'priority',
        'status',
    ];
    protected $with = ['products'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'compound_products')
            ->withPivot('inventory')
            ->withTimestamps();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }
}
