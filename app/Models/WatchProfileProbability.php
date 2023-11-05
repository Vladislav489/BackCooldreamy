<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchProfileProbability extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_type_id',
        'probability'
    ];
}
