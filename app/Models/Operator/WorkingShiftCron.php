<?php
namespace App\Models\Operator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingShiftCron  extends Model {
    use HasFactory;
    /** @var string */
    /** @var string[] */
    protected $fillable = [
        'user_id',
    ];
}

