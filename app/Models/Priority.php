<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'index']; // Define fillable fields as necessary

    // Define the relationship to Product
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_products', 'priority_id', 'product_id');
    }
}
