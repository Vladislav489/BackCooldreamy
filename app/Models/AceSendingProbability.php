<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceSendingProbability extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_type_id',
        'probability',
        'user_group'
    ];
}
