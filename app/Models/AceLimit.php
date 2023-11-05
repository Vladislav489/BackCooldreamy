<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_random_second',
        'random_second_from',
        'random_second_to',
        'ace_limit',
        'is_regular',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
