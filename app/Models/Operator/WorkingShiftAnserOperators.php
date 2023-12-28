<?php

namespace App\Models\Operator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingShiftAnserOperators extends Model {
    use HasFactory;
    /** @var string */
    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'ancet_id',
        'chat_id',
        'man_id',
        'message_id',
    ];

    protected $table = 'working_shift_anser_operators';
}

