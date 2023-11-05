<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGeo extends Model
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'ip',
        'city',
        'state',
        'country',
        'country_code',
        'continent',
        'continent_code'
    ];
}
