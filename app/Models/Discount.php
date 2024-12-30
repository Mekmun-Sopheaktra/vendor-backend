<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discount';

    protected $fillable = [
        'title',
        'description',
        'discount',
        'start_date',
        'end_date',
        'status',
        'priority',
        'type',
        'product_id',
        'vendor_id',
        'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class); // Define the inverse relationship
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
