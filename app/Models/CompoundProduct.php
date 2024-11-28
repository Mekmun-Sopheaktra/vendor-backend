<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompoundProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'compound_id',
        'products_id',
        'inventory',
    ];
}
