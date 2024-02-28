<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatVideoMessage extends Model
{
    use HasFactory;

    protected $fillable = ['video_url', 'is_payed'];

    public function chat_message()
    {
        return $this->morphOne(ChatMessage::class, 'chat_messageable');
    }

    public function sticker() {
        return $this->belongsTo(Sticker::class);
    }

    public function gifts() {
        return $this->belongsToMany(Gift::class, 'gifts_in_chat_gift_messages', 'chat_gift_message_id', 'gift_id');
    }
}
