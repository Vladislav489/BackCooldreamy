<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorChatLimitActions extends Model
{
    use HasFactory;

    protected $fillable = [
        'limits',
        'action',
    ];
}
