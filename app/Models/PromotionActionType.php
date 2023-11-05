<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionActionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'info',
        'id',
        'system_enum'
    ];

    const NEW_REGISTER = 'NEW_REGISTER';

    const FIRST_MESSAGES = 'FIRST_MESSAGES';
}
