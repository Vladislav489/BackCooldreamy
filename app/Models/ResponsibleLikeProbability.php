<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsibleLikeProbability extends Model
{
    use HasFactory;

    protected $fillable = [
        'like_count',
        'probability',
    ];
}
