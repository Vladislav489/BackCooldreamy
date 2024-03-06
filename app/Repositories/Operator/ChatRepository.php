<?php

namespace App\Repositories\Operator;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Events\ObjectNewChatEvent;
use App\Events\OperatorsEventMessage;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLinksUserLogic;
use App\Models\Auth\CreditLog;
use App\Models\Chat;
use App\Models\ChatGiftMessage;
use App\Models\ChatImageMessage;
use App\Models\ChatMessage;
use App\Models\ChatStickerMessage;
use App\Models\ChatTextMessage;
use App\Models\ChatVideoMessage;
use App\Models\Gift;
use App\Models\OperatorLinkUsers;
use App\Models\Sticker;
use App\Models\User;
use App\Repositories\User\FavoriteRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatRepository
{
    /** @var int */
    const PER_PAGE = 10;

    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var FavoriteRepository */
    private FavoriteRepository $favoriteRepository;

    public function __construct(
        OperatorRepository $operatorRepository,
        FavoriteRepository $favoriteRepository,
    )
    {
        $this->operatorRepository = $operatorRepository;
        $this->favoriteRepository = $favoriteRepository;
    }

    /**
     * @param array $requestData
     * @return int
     */
    public function getCountMessage(array $requestData = []): int
    {
        $query = ChatMessage::query()->where(function($query) {
            $query->whereHas('sender_user', function ($query) {
                $query->where('is_real',"=", 0);
            })->orWhereHas('recepient_user', function ($query) {
                $query->where('is_real', "=",0);
            });
        });

        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            $query->where('created_at', '<=', Carbon::now()->subMonths($lastMonth));
        }

        return $query->count();
    }

    /**
     * @param Chat $chat
     * @return ChatMessage|null
     */
    public function getChatLastMessage(Chat $chat): ?ChatMessage
    {
        return ChatMessage::with('chat_messageable.gifts', 'chat_messageable.sticker')
            ->where('chat_id', $chat->id)->latest()->first();
    }

    /**
     * @param Chat $chat
     * @param $messageId
     * @return ChatMessage|null
     */
    public function findChatMessage(Chat $chat, $messageId): ?ChatMessage
    {
        return ChatMessage::query()
            ->where('chat_id', $chat->id)
            ->where('id', $messageId)
            ->where('is_read_by_recepient', false)
            ->first();
    }

    /**
     * @param User $user
     * @param string $id
     * @param array $with
     * @return JsonResponse|Chat
     */


    public static function findHowWorkAnket($ancet_id){
       $ancet =  new  OperatorLinksUserLogic(['user'=>(string)$ancet_id,'work'=>'1'],["id","operator_id"]);
       $res = $ancet->setJoin(['User'])->offPagination()->setLimit(1)->getOne();
            return (count($res) > 0 && isset($res['operator_id']))?$res['operator_id']:User::find(124487)->id;
    }
    public function findForAnket(User $user, string $id, array $with = []): JsonResponse|Chat {
        $chat = (new ChatLogic(['id' => $id]))->getFullQuery()->with($with)->first();
        dd($chat);
        $params = ['user' => (string)$chat->first_user_id];
        if ($user->getRoleNames()->toArray()[0] != 'admin')
            $params['operator'] = (string)Auth::id();
        $result = (new OperatorLinksUserLogic($params))->Exist();
        if($result){
            $chat->user_id = $chat->first_user_id;
            $chat->recepient_id =$chat->second_user_id;

        }else{
            $chat->user_id = $chat->second_user_id;
            $chat->recepient_id = $chat->first_user_id;
        }
        if ($chat->deleted_by_first_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }

        if ($chat->deleted_by_second_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }
        return $chat;
    }

    /**
     * @param User $user
     * @param string $id
     * @param array $with
     * @return Chat|JsonResponse
     */
    public function findForAdminAnket(User $user, string $id, array $with = [])
    {
        // Берем анкеты пользователей
        $userIds = $user->adminAncets()->pluck('user_id');

        /** @var Chat $chat */
        $chat = Chat::query()->with($with)->where(function (Builder $builder) use ($userIds) {
            $builder->whereIn('first_user_id', $userIds)->orWhereIn('second_user_id', $userIds);
        })->where('id', $id)->firstOrFail();

        // Указываем пользователя анкеты и пользователя, получателя сообщения(для чата), чтобы не запутаться
        $chat->user_id = $this->operatorRepository->getOperatorByAncets($chat, $userIds->toArray());
        $chat->recepient_id = $this->operatorRepository->getRecepientUserByAncets($chat, $userIds->toArray());
//
//        if ($chat->deleted_by_first_user) {
//            return response()->json(['error' => 'User deleted the chat.'], 404);
//        }
//
//        if ($chat->deleted_by_second_user) {
//            return response()->json(['error' => 'User deleted the chat.'], 404);
//        }

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param string $text
     * @return LengthAwarePaginator
     */
    public function searchChatMessages(Chat $chat, string $text = ''): LengthAwarePaginator
    {
        return $chat->chat_messages()->whereHasMorph('chat_messageable', [ChatTextMessage::class], function ($q) use ($text) {
            $q->where('text', 'LIKE', '%' . $text . '%');
        })->with('chat_messageable')->orderBy('created_at', 'desc')->paginate(self::PER_PAGE);
    }

    /**
     * @param Chat $chat
     * @return LengthAwarePaginator
     */
    public function getChatMessages(Chat $chat,$per_page = null): LengthAwarePaginator
    {
        return $chat->chat_messages()
            ->with(['sender_user' => function ($query) {
                $query->select('id', 'name', 'avatar_url_thumbnail', 'avatar_url');
            }, 'chat_messageable.gifts', 'chat_messageable.sticker'])
            ->orderBy('created_at', 'desc')
            ->paginate((is_null($per_page))?self::PER_PAGE:$per_page);
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserChatsCount(User $user): int
    {
        return Chat::where(function ($builder) use ($user) {
            $builder->where('first_user_id', $user->id)
            ->where('deleted_by_first_user', false);
        })->orWhere(function ($builder) use ($user) {
            $builder->where('second_user_id', $user->id)
                ->where('deleted_by_second_user', false);
        })->count();
    }

    /**
     * @param Chat $chat
     * @param $ignoredUserid
     * @return User|null
     */
    public function getChatAnotherUser(Chat $chat, $ignoredUserid): ?User
    {
        $field = 'first_user_id';
        if ($chat->first_user_id == $ignoredUserid) {
            $field = 'second_user_id';
        }

        return User::query()->setEagerLoads([])->where('id', $chat->$field)->first();
    }

    /**
     * @param array $requestData
     * @return LengthAwarePaginator|Builder
     */
    public function index(array $requestData = []): LengthAwarePaginator|Builder{

        $params = $requestData;
        $array = ['firstUser', 'secondUser','lastMessage'];
        $join =[];
        if (Arr::get($requestData, 'chat_limit'))
            array_push($array, 'limit');
        $join[] = "OperatorWork";


        if(isset($params['operator_id'])){
            $params['operator_id'] = (string)$params['operator_id'];
            $params['deleted_by_first_user'] = '0';
            $params['deleted_by_second_user'] = '0';
        }
        if(isset($params['search'])){
            $params['real'] = '1';
            $params['search_id'] = $params['search'];
        }
        if(isset($params['chat_limit'])){
            $params['limit_more'] = '1';
        }
        if(isset($params['search_message'])){
            $params['text_message'] = "%".$params['search_message']."%";
        }
        if(isset($params['filter_type'])){
           switch ($params['filter_type']){
               case "online":
                   $params['online'] = '1';
                   break;
               case "premium":
                   $params['real'] = '1';
                   $params['premium_more'] ='1';
                   break;
               case "subscription":
                   $params['real'] = '1';
                   $params['subscription_more'] ='1';
                   break;
               case "payed":
                   $params['real'] = '1';
                   $params['payed_more'] ='1';
                   break;
           }
        }
        $select = [DB::raw("chats.*")];

        $chat = new ChatLogic($params);
        $group = [];
        if(isset($params['operator_id'])) {
            $select[] = DB::raw("'0' as operator_id");
            $select[] = DB::raw("'none' as operator_name");
        }else{
            $select[] = DB::raw("'0' as operator_id");
            $select[] = DB::raw("'name' as operator_name");
            $group[]  = 'id';
        }
        $chat->setSelect($select);
        //var_dump($chat->offPagination()->order(['desc','updated_at'])->setLimit(false)->setGroupBy($group)->setJoin($join)->getSqlToStr());
        $query = $chat->setModel((new Chat()))->offPagination()->order('desc','updated_at')->setLimit(false)->setGroupBy($group)
            ->setJoin($join)->getFullQuery()
            ->with($array);

        if (Arr::get($requestData, 'is_query')) {
            return $query;
        }

        return $query->paginate(Arr::get($requestData, 'per_page'));
    }

    /**
     * @param OperatorLinkUsers $anket
     * @param array $requestData
     * @return Builder[]|Collection
     */
    public function getAnketChats(OperatorLinkUsers $anket, array $requestData = [])
    {

        $query = Chat::query()->where(function ($query) use ($anket) {
            $query->where(function (Builder $builder) use ($anket) {
                $builder->where('first_user_id', $anket->user_id)
                    ->where('deleted_by_first_user', false);
            })->orWhere(function (Builder $builder) use ($anket) {
                $builder->where('second_user_id', $anket->user_id)
                    ->where('deleted_by_second_user', false);
            });
        })->orderBy('updated_at', 'desc');
        if ($search = Arr::get($requestData, 'search')) {
            $query->where(function ($query) use ($anket, $search) {
                $query->where(function (Builder $builder) use ($anket, $search) {
                    $builder->where('first_user_id', $anket->user_id)
                        ->whereHas('secondUser', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                })->orWhere(function (Builder $builder) use ($anket, $search) {
                    $builder->where('second_user_id', $anket->user_id)
                        ->whereHas('firstUser', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                });
            });
        }

        return $query->get();
    }

    /**
     * @param Collection $chats
     * @param SupportCollection $favoriteUsers
     * @return Collection
     */
    public function getChatMessagesForIndex(Collection $chats, SupportCollection $favoriteUsers): Collection
    {
        foreach ($chats as $chat) {
            $last_message = $this->getChatLastMessage($chat);
            $chat->last_message = $last_message;
            if (isset ($chat->last_message->chat_messageable)) {
                $chat->last_message->chat_messageable = $last_message->chat_messageable;
            }
            $chat->favorite = ($favoriteUsers->contains($chat->first_user_id) || $favoriteUsers->contains($chat->second_user_id)) ? 1 : 0;
        }

        return $chats;
    }

    /**
     * @param Chat $chat
     * @param bool $its_event
     * @return Chat
     */
    public function getCurrentChatList(Chat $chat, bool $its_event = false): Chat
    {
        // $chat->user_id - указываем когда получаем find
        /* @see ChatRepository::findForAnket() */
        $user = $this->operatorRepository->findUser($chat->user_id);
        $anotherUser = $this->operatorRepository->findUser($chat->recepient_id);

        /** @var ?ChatMessage $lastMessage */
        $lastMessage = $this->getChatLastMessage($chat);
        if ($lastMessage) {
            $chat->last_message = $lastMessage;
            $chat->last_message->chat_messageable = $lastMessage->chat_messageable;
        }

        if ($its_event) {
            $chat->another_user = $user;
            if ($chat->first_user_id == $user->id) {
                $recepient_id = $chat->second_user_id;
            } else {
                $recepient_id = $chat->first_user_id;
            }
            $favorite_users = $this->favoriteRepository->getUserFavoriteById($recepient_id);
        } else {
            $chat->another_user = $anotherUser;
            $favorite_users = $this->favoriteRepository->getUserFavoriteById($user->id);
        }

        $chat->favorite = ($favorite_users->contains($chat->first_user_id) || $favorite_users->contains($chat->second_user_id)) ? 1 : 0;
        $chat->updated_at = now();
        $limit = OperatorLimitController::getChatLimits($chat->user_id, $chat->recepient_id);
        $chat->last_message->available_limit = $limit ? $limit->limits : 0;
        $chat->last_message->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $chat->recepient_id)->where('other_user_id', $chat->user_id)->sum('credits');

        return $chat;
    }

    /**
     * @param Chat $chat
     * @param $text
     * @return ChatMessage
     */
    public function saveChatMessage(Chat $chat, $text): ChatMessage
    {
        $chatTextMessage = ChatTextMessage::create(['text' => $text]);

        return $this->saveMessage($chat, $chatTextMessage);
    }

    /**
     * @param Chat $chat
     * @param $chatMessageObj
     * @return ChatMessage
     */
    private function saveMessage(Chat $chat, $chatMessageObj): ChatMessage
    {
        if ($chatMessageObj instanceof ChatImageMessage || $chatMessageObj instanceof ChatVideoMessage) {
//            $countImages = ChatMessage::query()->where('chat_id', $chat->id)->where('chat_messageable_type', ChatImageMessage::class)->count();
//            if ($countImages >= Chat::COUNT_FREE_IMAGES) {
//                $isPayed = false;
//            } else {
//                $isPayed = true;
//            }
            $isPayed = $chatMessageObj->is_payed;
        } else {
            $isPayed = true;
        }
        $operator = ChatRepository::findHowWorkAnket($chat->sender_user_id);
        $chatMessage = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $chat->user_id,
            'recepient_user_id' => $chat->recepient_id,
            'is_read_by_recepient' => false,
            'is_payed' => $isPayed,
            'operator_get_ansver' => $operator
        ]);

        $chatMessageObj->chat_message()->save($chatMessage);

        $chatMessage->load(['chat_messageable']);
        $chatListItem = $this->getCurrentChatList($chat, true);
        //ObjectNewChatEvent::dispatch($chat->recepient_id, $chatMessage, $chatListItem);
        $this->updateChat($chat);
        return $chatMessage;
    }

    /**
     * @param Chat $chat
     */
    private function updateChat(Chat $chat)
    {
        $chat->load('firstUser', 'secondUser');
        if ($chat->selfUser && $chat->selfUser->operator) {
            $operator = $chat->selfUser->operator;
            if (!$chat->is_answered_by_operator) {
                OperatorsEventMessage::dispatch($operator->operator_id, $chat->id, 'chat');
            }
        }

        Chat::where('id', $chat->id)->update(['updated_at' => now(), 'is_answered_by_operator' => true]);
    }

    /**
     * @param Chat $chat
     * @param Sticker $sticker
     * @return ChatMessage
     */
    public function saveChatSticker(Chat $chat, Sticker $sticker): ChatMessage
    {
        $chatStickerMessage = ChatStickerMessage::create(['sticker_id' => $sticker->id]);

        return $this->saveMessage($chat, $chatStickerMessage);
    }

    /**
     * @param Chat $chat
     * @param Gift $gift
     * @return ChatMessage
     */
    public function saveChatGift(Chat $chat, Gift $gift): ChatMessage
    {
        $chatGiftMessage = ChatGiftMessage::create();
        $chatGiftMessage->gifts()->attach($gift);

        return $this->saveMessage($chat, $chatGiftMessage);
    }

    /**
     * @param Chat $chat
     * @param $imageUrl
     * @param $thumbnailUrl
     * @return ChatMessage
     */
    public function saveChatImage(Chat $chat, $imageUrl, $thumbnailUrl, $categoryId): ChatMessage
    {
        $categoryId == 4 ? $isPayed = 0 : $isPayed = 1;
        $chatImageMessage = ChatImageMessage::create([
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
            'is_payed' => $isPayed
        ]);

        return $this->saveMessage($chat, $chatImageMessage);
    }
    public function saveChatVideo(Chat $chat, $videoUrl): ChatMessage
    {
        $chatVideoMessage = ChatVideoMessage::create([
            'video_url' => $videoUrl,
            'is_payed' => false
        ]);

        return $this->saveMessage($chat, $chatVideoMessage);
    }
}
