<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model {
    use HasFactory;

    protected $table = 'chat_settings';
    protected $fillable = [
        'send_message_price',
        'send_sticker_price',
        'send_file_price',
    ];

}
