<?php

namespace App\Models\Ace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceLimitAssignment extends Model
{
    use HasFactory;

    /** @var string[] */
    protected $fillable = [
        'limit',
        'second_from',
        'second_to'
    ];
}
