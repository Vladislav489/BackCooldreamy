<?php

namespace App\Models;

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

    protected $with = [
        'sender_user', 'recepient_user'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender_user()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function operator_ansver()
    {
        return $this->belongsTo(User::class, 'operator_get_ansver');
    }

    public function recepient_user()
    {
        return $this->belongsTo(User::class, 'recepient_user_id');
    }

    public function fakeRecepient()
    {
        return $this->belongsTo(User::class, 'recepient_user_id')->where([['gender', 'female'], ['is_real', 0]]);
    }

    public function chat_messageable() {
        return $this->morphTo();
    }
}
