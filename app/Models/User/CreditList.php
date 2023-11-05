<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditList extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'credit_lists';

    /**
     * @var string[]
     */
    protected $fillable = [
        'is_one_time',
        'credits',
        'discount',
        'price',
        'old_price'
    ];
}
