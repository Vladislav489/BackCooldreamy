<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatWinkMessage extends Model {
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
    ];

    public function chat_message() {
        return $this->morphOne(ChatMessage::class, 'chat_messageable', null, null, '');
    }

    public function sticker() {
        return $this->belongsTo(Sticker::class);
    }

    public function gifts() {
        return $this->belongsToMany(Gift::class, 'gifts_in_chat_gift_messages', 'chat_gift_message_id', 'gift_id');
    }
}
