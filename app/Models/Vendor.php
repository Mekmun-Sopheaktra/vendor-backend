<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'address',
        'description',
        'purpose',
        'logo',
        'email',
        'banner',
        'status',
        'paypal_client_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function compounds()
    {
        return $this->hasMany(Compound::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    //logo
    public function getLogoAttribute($value)
    {
        return secure_asset('storage/'.$value);
    }

    //
}
