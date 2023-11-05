<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftsInСhatGiftMessage extends Model {
    use HasFactory;
    protected $table = 'gifts_in_chat_gift_messages';
    protected $fillable = [
        'gift_id',
        'chat_gift_message_id',
    ];

}
