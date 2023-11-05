<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'status',
        'list_id',
        "price",
        'list_type',
        'payment_id',
        'payment_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
