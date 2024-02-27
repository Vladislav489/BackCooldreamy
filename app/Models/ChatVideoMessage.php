<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatVideoMessage extends Model
{
    use HasFactory;

    protected $fillable = ['video_url', 'is_payed'];

    public function video()
    {
        return $this->morphOne(ChatMessage::class, 'chat_messageable');
    }
}
