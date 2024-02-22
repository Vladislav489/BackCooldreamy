<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPayedMessagesToOperators extends Model
{
    use HasFactory;

    protected $table = 'users_payed_messages_to_operators';

    protected $fillable = ['user_id', 'ancet_id', 'operator_id', 'credits',];
}
