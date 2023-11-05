<?php

namespace App\Models\Operator;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorLog extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'operator_logs';

    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'anket_id',
        'man_id',
        'log'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function anket()
    {
        return $this->belongsTo(User::class, 'anket_id', 'id');
    }

    public function man()
    {
        return $this->belongsTo(User::class, 'man_id');
    }
}
