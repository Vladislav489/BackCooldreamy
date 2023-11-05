<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'status'
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
