<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperatorLetterMessageReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private $user_id;
    public $letter_id;
    public $letter_messsage_id;

    public function __construct($user_id, $letter_id, $letter_messsage_id) {
        $this->user_id = $user_id;
        $this->letter_id = $letter_id;
        $this->letter_messsage_id = $letter_messsage_id;
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
        return 'operator-letter-message-read-event';
    }
}
