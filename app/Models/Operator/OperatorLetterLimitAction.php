<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorLetterLimitAction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'limits',
        'action',
    ];
}
