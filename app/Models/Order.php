<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vendor_id',
        'user_id',
        'status',
        'phone',
        'address',
        'transaction_method',
        'transaction_id',
        'amount'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    //user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
