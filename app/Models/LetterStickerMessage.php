<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterStickerMessage extends Model {
    use HasFactory;

    protected $fillable = [
        'sticker_id',
    ];

    public function letter_message() {
        return $this->morphOne(ChatMessage::class, 'letter_messageable');
    }

    public function sticker()
    {
        return $this->belongsTo(Sticker::class);
    }

    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'gifts_in_letter_gift_messages', 'letter_gift_message_id', 'gift_id');
    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'images_in_letters', 'letter_text_message_id', 'image_id')
            ->withPivot('is_payed');
    }
}
