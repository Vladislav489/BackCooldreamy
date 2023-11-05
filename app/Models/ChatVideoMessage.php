<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatVideoMessage extends Model
{
    use HasFactory;

    protected $fillable = ['video_url'];

    public function chat_message()
    {
        return $this->morphOne(ChatMessage::class, 'chat_messageable');
    }
}
