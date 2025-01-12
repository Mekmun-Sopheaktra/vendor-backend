<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Basket extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'product_id',
        'count',
        'price',
        'status',
    ];

    public function Product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
