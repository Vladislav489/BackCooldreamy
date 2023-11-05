<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterImageMessage
{
    use HasFactory;

    protected $fillable = ['image_url', 'thumbnail_url'];

    public function letter_message()
    {
        return $this->morphOne(LetterMessage::class, 'letter_messageable');
    }


    public function sticker()
    {
        return $this->belongsTo(Sticker::class);
    }

    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'gifts_in_letter_gift_messages', 'letter_gift_message_id', 'gift_id');
    }
}
