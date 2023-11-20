<?php


namespace App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model {
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_user_id',
        'recepient_user_id',
        'chat_messageable_id',
        'chat_messageable_type',
        'is_read_by_recepient',
        'is_payed',
        'is_ace',
        'disabled',
        'operator_get_ansver',
    ];

}
