<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterMessage extends Model {
    use HasFactory;

    protected $fillable = [
        'letter_id',
        'sender_user_id',
        'recepient_user_id',
        'letter_messageable_id',
        'letter_messageable_type',
        'is_read_by_recepient',
        'disabled',
    ];

    protected $with = [
        'sender_user', 'recepient_user'
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }

    public function sender_user()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recepient_user()
    {
        return $this->belongsTo(User::class, 'recepient_user_id');
    }

    public function letter_messageable() {
        return $this->morphTo();
    }
}
