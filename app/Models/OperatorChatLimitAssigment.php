<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorChatLimitAssigment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'anket_count_from',
        'anket_count_to',
        'limit_from',
        'limit_to',
    ];

    public function type()
    {
        return $this->belongsTo(ProfileType::class);
    }
}
