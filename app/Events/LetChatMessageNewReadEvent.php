<?php

namespace App\Events;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\Models\Auth\CreditLog;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LetChatMessageNewReadEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private $user_id;
    private $chat_id;
    private $chat_messsage_id;

    private $chat_message, $chat_list_item;

    public function __construct($user_id, $chat_id, $chat_messsage_id) {
        $this->user_id = $user_id;
        $this->chat_id = $chat_id;
        $this->chat_messsage_id = $chat_messsage_id;
        $this->chat_list_item = Chat::find($chat_id);
        $this->chat_message = ChatMessage::find($chat_messsage_id);
    }


    /**
     * @return array
     */
    public function broadcastWith()
    {
        $limit = OperatorLimitController::getById($this->chat_list_item->id);
        $this->chat_message->available_limit = $limit ? (float)$limit->limits : 0;
        $this->chat_message->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $this->chat_message->sender_user->id)->where('other_user_id', $this->chat_message->recepient_user->id)->sum('credits');

        if ($this->chat_message && $this->chat_message->sender_user) {
            $userAvatar = $this->chat_message->sender_user->avatar_url;
            $this->chat_message->sender_user->user_avatar_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

            $userAvatar = $this->chat_message->sender_user->avatar_url_thumbnail;
            $this->chat_message->sender_user->user_thumbnail_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';
        }

        if ($this->chat_message && $this->chat_message->recepient_user) {
            $userAvatar = $this->chat_message->recepient_user->avatar_url;
            $this->chat_message->recepient_user->user_avatar_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

            $userAvatar = $this->chat_message->recepient_user->avatar_url_thumbnail;
            $this->chat_message->recepient_user->user_thumbnail_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';
        }

        $this->chat_message->chat_messageable = $this->chat_message->chat_messageable;

        $this->chat_list_item->type_of_model = 'chat';
        return [
            'chat_message' => $this->chat_message,
            'chat_list_item' => $this->chat_list_item
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {
        return new PrivateChannel('App.User.' . $this->user_id);
    }

    public function broadcastAs() {
        return 'chat-message-read-event';
    }
}
