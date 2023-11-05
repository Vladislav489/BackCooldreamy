<?php

namespace App\Models\Operator;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorReport extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'operator_reports';

    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'anket_id',
        'man_id',
        'text',
        'date_time',
        'is_important'
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
