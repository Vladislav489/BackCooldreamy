<?php

namespace App\Services\Online;

use App\Enum\User\OnlineActivitiesEnum;
use App\Events\LetChatMessageNewReadEvent;
use App\Jobs\Online\SetOnlineJob;
use App\Models\ChatMessage;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class OnlineService
{
    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var LoggerInterface  */
    protected LoggerInterface $log;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        // TODO в будующем убрать
        $this->log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/online/online.log')
        ]);
    }

    /**
     * @param User $user
     * @param int $action
     */
    public function setOnline(User $user, int $action)
    {
        if ($user->is_real || $user->gender != 'female') {
            abort(403);
        }

        $this->setQueueTimeout($user, $action);
    }

    /**
     * @param User $user
     * @param int $action
     */
    public function setQueueTimeout(User $user, int $action)
    {
        $timeout = now()->addSeconds(rand(30, 60));

        if ($action == OnlineActivitiesEnum::MAN_SEND_MESSAGE_FIRST) {
            $timeout = now()->addSeconds(0);
        } else  if ($action == OnlineActivitiesEnum::MAN_SEND_MESSAGE_TO_EXISTS_CHAT) {
            $timeout = now()->addSeconds(rand(180, 360));
        }

        $this->log->info("Пользователю {$user->id} пришло сообщение от мужчины ");
        //SetOnlineJob::dispatch($user)->onQueue('queue_online')->delay($timeout);
    }

    /**
     * @param ChatMessage $chatMessage
     * @return ChatMessage
     */
    public function setIsRead(ChatMessage $chatMessage): ChatMessage
    {
        $chatMessage->is_read_by_recepient = true;
        $chatMessage->save();
       // LetChatMessageNewReadEvent::dispatch($chatMessage->sender_user_id, $chatMessage->chat_id, $chatMessage->id);

        return $chatMessage;
    }
}
