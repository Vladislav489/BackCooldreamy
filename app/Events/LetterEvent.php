<?php

namespace App\Events;

use App\Models\Letter;
use App\Models\LetterMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;

class LetterEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $letter_message, $letter_list_item;
    private $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($user_id, LetterMessage $letterMessage, $letterListItem)
    {
        $this->user_id = $user_id;
        if (isset($letterListItem['letter']) && isset($letterListItem['letter']['id'])) {
            $chatListItem['countImages'] = LetterMessage::query()->where('letter_id', $letterListItem['letter']['id']);
        } else {
            $letterListItem['unread_messages'] = $letterListItem->letter_messages()->where('recepient_user_id', $user_id)
                ->where('is_read_by_recepient', 0)->count();
        }
        $this->letter_message = $letterMessage;
        $this->letter_list_item = $letterListItem;
        $letterMessage->letter->updated_at = now();
        $letterMessage->letter->save();
//        Log::info(json_encode($letterListItem));
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
        return 'new-letter-message-event';
    }
}
