<?php

namespace App\Events;

use App\Http\Controllers\API\V1\ChatController;
use App\Models\Chat;
use App\Models\ChatImageMessage;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ObjectNewChatEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat_message, $chat_list_item;
    private $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($user_id, ChatMessage $chatMessage, $chatListItem)
    {
        $this->user_id = $user_id;
        $chatListItem['max_free_images'] = Chat::COUNT_FREE_IMAGES;
        if (isset($chatListItem['chat']) && isset($chatListItem['chat']['id'])) {
            $chatListItem['countImages'] = ChatMessage::query()->where('chat_id', $chatListItem['chat']['id'])->where('chat_messageable_type', ChatImageMessage::class)->count();
        } else {
            $chatListItem['unread_messages'] = $chatListItem->chat_messages()->where('recepient_user_id', $user_id)->where('is_read_by_recepient', 0)->count();
        }
        $this->chat_message = $chatMessage;
        $this->chat_list_item = $chatListItem;
        $this->chat_list_item->val = 'asd';
        $chatMessage->chat->updated_at = now();
        $chatMessage->chat->save();
    }

    public function broadcastWith()
    {

        return [
            'chat_message' => $this->chatMessage
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
        return 'new-chat-message-event';
    }
}
