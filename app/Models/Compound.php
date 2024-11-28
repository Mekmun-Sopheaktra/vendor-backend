<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compound extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description'];
    protected $with = ['products'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'compound_products')
            ->withPivot('inventory')
            ->withTimestamps();
    }
}
