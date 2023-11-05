<?php

namespace App\Models\Operator;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorWork extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'operator_works';

    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'date_from',
        'date_to',
        'is_finished'
    ];


    /**
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }
}
