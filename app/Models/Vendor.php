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
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
