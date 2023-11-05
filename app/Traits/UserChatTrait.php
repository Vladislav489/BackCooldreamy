<?php

namespace App\Traits;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserChatTrait
{
    /**
     * @return int
     */
    public function chatMessagesCount(): int
    {
        return ChatMessage::where('sender_user_id', $this->id)->orWhere('recepient_user_id', $this->id)->count();
    }

    /**
     * @return HasMany
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'first_user_id', 'id')
            ->orWhere('second_user_id', $this->id);
    }

    public function chatSendedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_user_id');
    }

    public function chatReceivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'recepient_user_id');
    }
}
