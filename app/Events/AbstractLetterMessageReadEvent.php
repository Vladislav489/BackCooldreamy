<?php

namespace App\Events;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\Models\Auth\CreditLog;
use App\Models\Letter;
use App\Models\LetterMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbstractLetterMessageReadEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private $user_id;
    public $letter_id;
    public $letter_messsage_id;

    private $letter_message;
    private $letter_list_item;

    public function __construct($user_id, $letter_id, $letter_messsage_id) {
        $this->user_id = $user_id;
        $this->letter_id = $letter_id;
        $this->letter_messsage_id = $letter_messsage_id;
        $this->letter_message = LetterMessage::find($letter_messsage_id);
        $this->letter_list_item = Letter::find($letter_id);
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {

        return [
            'letter_message' => $this->letter_message,
            'letter_list_item' => $this->letter_list_item,
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
        return 'letter-message-read-event';
    }
}
