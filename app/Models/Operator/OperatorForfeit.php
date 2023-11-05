<?php


namespace App\Models\Operator;


use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorForfeit extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'operator_forfeits';

    /** @var string[] */
    protected $fillable = [
        'operator_id',
        'operator_id',
        'message_id',
        'chat_id'
    ];


}
