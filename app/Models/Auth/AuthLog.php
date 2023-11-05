<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthLog extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'auth_logs';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'log_type'
    ];
}
