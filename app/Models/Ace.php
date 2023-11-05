<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ace extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_type_id',
        'text',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
