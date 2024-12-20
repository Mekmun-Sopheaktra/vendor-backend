<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $table = 'revenue';

    protected $fillable = [
        'date',
        'revenue',
        'monthly_subscription_fee',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
        'monthly_subscription_fee' => 'decimal:2',
    ];

    public $timestamps = true;
}
