<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumList extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'premium_lists';

    /**
     * @var string[]
     */
    protected $fillable = [
        'duration',
        'discount',
        'price',
        'old_price'
    ];
}
