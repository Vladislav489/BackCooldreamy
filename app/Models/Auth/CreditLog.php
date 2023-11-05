<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'credit_logs';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'real_credits',
        'credits',
        'credit_type',
        'other_user_id',
        'action_type'
    ];
}
