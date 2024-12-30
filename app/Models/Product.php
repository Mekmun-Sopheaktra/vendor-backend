<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'title',
        'slug',
        'description',
        'price',
        'image',
        'volume',
        'product_code',
        'manufacturing_date',
        'fragrance_family',
        'expire_date',
        'gender',
        'inventory',
        'view_count',
        'is_compound_product',
        'discount',
        'priority',
        'category_id',
        'status',
    ];

    // You can use this code for many-to-many relation
     public function categories()
     {
         return $this->belongsToMany(Category::class , 'category_products')->withPivot('product_id', 'category_id');
     }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProductGallery::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function vendors()
    {
        //one vendors to many products
        return $this->belongsTo(Vendor::class);
    }

    // In Product.php (Product Model)
    public function priorities()
    {
        return $this->belongsToMany(Priority::class, 'category_products', 'product_id', 'priority_id');
    }

    public function compounds()
    {
        return $this->belongsToMany(Compound::class, 'compound_products')
            ->withPivot('inventory')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(OrderProduct::class);
    }

    //get image attribute
    public function getImageAttribute($value)
    {
        return asset('storage/' . $value);
    }
    public function discount()
    {
        return $this->hasMany(Discount::class);
    }
}
