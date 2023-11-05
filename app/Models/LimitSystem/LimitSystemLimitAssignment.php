<?php


namespace App\Models\LimitSystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimitSystemLimitAssignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'sort',
        'step_from',
        'step_to',
    ];
}

