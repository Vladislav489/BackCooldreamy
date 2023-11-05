<?php

namespace App\Models\Operator;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorDelay extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'operator_delays';

    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'time',
        'delay'
    ];

    /**
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }
}
