<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'activation_type_id',
        'hours',
        'credits',
        'status',
        'benefit',
        'subscription_id',
        'premium_id',
        'price'
    ];
}
