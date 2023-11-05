<?php

namespace App\Events;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Http\Controllers\API\V1\OperatorLetterLimitController;
use App\Models\Auth\CreditLog;
use App\Models\LetterMessage;
use App\Models\Operator\OperatorLetterLimit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewWOperatorLettersEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $letter_message, $letter_list_item;
    private $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($user_id, $letterMessage, $letterListItem)
    {
        $this->user_id = $user_id;
        $letterListItem->model_type = 'letter';
        $this->letter_message = $letterMessage;
        $this->letter_list_item = $letterListItem;
        $letterMessage->letter->updated_at = now();
        $letterMessage->letter->save();
//        Log::info(json_encode($letterListItem));
    }

    /**
     * @return array
     */
    public function broadcastWith()
    {

        $limit = OperatorLetterLimitController::getById($this->letter_list_item->id);

        $this->letter_message->available_limit = $limit ? (float)$limit->limits : 0;
        $this->letter_message->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $this->letter_message->sender_user->id)->where('other_user_id', $this->letter_message->recepient_user->id)->sum('credits');

        $userAvatar = $this->letter_message->sender_user->avatar_url;
        $this->letter_message->sender_user->user_avatar_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

        $userAvatar = $this->letter_message->sender_user->avatar_url_thumbnail;
        $this->letter_message->sender_user->user_thumbnail_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

        $userAvatar = $this->letter_message->recepient_user->avatar_url;
        $this->letter_message->recepient_user->user_avatar_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

        $userAvatar = $this->letter_message->recepient_user->avatar_url_thumbnail;
        $this->letter_message->recepient_user->user_thumbnail_url = $userAvatar ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $userAvatar) : config('app.url') . '/empty-avatar.png';

        $this->letter_list_item->type_of_model = 'letter';
        return [
            'letter_message' => $this->letter_message,
            'letter_list_item' => $this->letter_list_item
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
        return 'operator-new-letter-message-event';
    }
}
