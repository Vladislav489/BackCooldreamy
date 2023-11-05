<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatTextMessage extends Model {
    use HasFactory;

    protected $fillable = ['text'];

    public function chat_message() {
        return $this->morphOne(ChatMessage::class, 'chat_messageable');
    }

    public function get_price() {
        $setting = ChatSetting::firstorfail();
        return $setting->send_message_price;
    }

    public function sticker() {
        return $this->belongsTo(Sticker::class);
    }

    public function gifts() {
        return $this->belongsToMany(Gift::class, 'gifts_in_chat_gift_messages', 'chat_gift_message_id', 'gift_id');
    }
}
