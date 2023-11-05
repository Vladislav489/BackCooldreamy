<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingShiftLog extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'working_shift_logs';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'status'
    ];
}
