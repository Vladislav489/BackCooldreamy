<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SympathyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type, $user_data;
    private $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($user_id, $type, $userData)
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('/logs/sympathy/sympathy.log')
        ]);

        $log->info('[SympathyEvent::__construct] Send Event to user: ' . $user_id);

        $this->type = $type;
        $this->user_id = $user_id;
        $this->user_data = $userData;
    }

    /**
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.User.' . $this->user_id);
    }

    /**
     * @return string
     */
    public function broadcastAs() {
        return 'sympathy-event';
    }
}
