<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'email', 'password'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
