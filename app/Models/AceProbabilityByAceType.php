<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceProbabilityByAceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'ice_type',
        'profile_type_id',
        'probability',
        'comment',
    ];
}
