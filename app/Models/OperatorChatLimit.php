<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorChatLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'man_id',
        'girl_id',
        'limits',
        'chat_id'
    ];

    public function girl()
    {
        return $this->belongsTo(User::class, 'girl_id');
    }

    public function man()
    {
        return $this->belongsTo(User::class, 'man_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
