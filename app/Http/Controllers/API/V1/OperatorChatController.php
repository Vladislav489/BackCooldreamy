<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Events\ObjectNewChatEvent;
use App\Events\LetChatMessageNewReadEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\OperatorChatResource;
use App\Mail\MessageUserMail;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatMessageLogic;
use App\Models\AceLog;
use App\Models\Auth\CreditLog;
use App\Models\Chat;
use App\Models\ChatImageMessage;
use App\Models\ChatMessage;
use App\Models\ChatTextMessage;
use App\Models\FavoriteProfile;
use App\Models\Image;
use App\Models\LetterMessage;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use App\Repositories\File\ImageRepository;
use App\Repositories\File\VideoRepository;
use App\Repositories\Operator\ChatRepository;
use App\Repositories\Operator\GifRepository;
use App\Repositories\Operator\LetterRepository;
use App\Repositories\Operator\OperatorRepository;
use App\Repositories\Operator\StickerRepository;
use App\Repositories\User\FavoriteRepository;
use App\Services\FireBase\FireBaseService;
use App\Services\OneSignal\OneSignalService;
use App\Services\Operator\LimitService;
use App\Services\Operator\WorkingShiftService;
use App\Services\Rating\RatingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperatorChatController extends Controller
{
    /** @var RatingService */
    private RatingService $ratingService;

    /** @var ChatRepository */
    private ChatRepository $chatRepository;

    /** @var StickerRepository */
    private StickerRepository $stickerRepository;

    /** @var GifRepository */
    private GifRepository $gifRepository;

    /** @var LimitService */
    private LimitService $limitService;

    /** @var FavoriteRepository */
    private FavoriteRepository $favoriteRepository;

    /** @var LetterRepository */
    private LetterRepository $letterRepository;

    /** @var ImageRepository */
    private ImageRepository $imageRepository;

    /** @var VideoRepository */
    private VideoRepository $videoRepository;

    private   WorkingShiftService $workingShiftService;

    public function __construct(
        RatingService $ratingService,
        ChatRepository $chatRepository,
        FavoriteRepository $favoriteRepository,
        StickerRepository $stickerRepository,
        LimitService $limitService,
        GifRepository $gifRepository,
        LetterRepository $letterRepository,
        ImageRepository $imageRepository,
        VideoRepository $videoRepository,
        WorkingShiftService $workingShiftService
    )
    {
        $this->ratingService = $ratingService;
        $this->chatRepository = $chatRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->stickerRepository = $stickerRepository;
        $this->gifRepository = $gifRepository;
        $this->limitService = $limitService;
        $this->letterRepository = $letterRepository;
        $this->imageRepository = $imageRepository;
        $this->videoRepository = $videoRepository;
        $this->workingShiftService = $workingShiftService;
    }

    /**
     * @param Collection $users
     * @return Collection
     */
    private function getUserData(Collection $users): Collection
    {
        return $users->map(function($user) {
            $user['statistic'] = $this->ratingService->getUserRating($user);
            return $user;
        });
    }


    public function index(Request $request){

        $operator = Auth::user();
        $join = ['firstUser', 'secondUser','lastMessage'];
        $params = [
            'page' => $request->get('page'),
            'pageSize' =>(string)30, //$request->get('per_page'),
            "search" => $request->get('search'),
            'filter_type' => $request->get('filter_type'),
        ];

        if($request->get('chat_limit')) {
            $params['limit_more'] = '1';
        }else{
            $params['limit_more'] = '0';
        }


        if($operator->getRoleNames()->toArray()[0] != 'admin') {
            $params['operator_id'] = (string)$operator->id;
            $params['deleted_by_first_user'] = '0';
            $params['deleted_by_second_user'] = '0';
        }

        if(isset($params['search'])) {
            $params['real'] = '1';
            $params['search_id'] = $params['search'];
        }

        if(isset($params['search_message']))
            $params['text_message'] = "%".$params['search_message']."%";

        if(isset($params['filter_type'])){
            switch ($params['filter_type']){
                case "online":
                    $params['online'] = '1';
                    break;
                case "premium":
                    $params['real'] = '1';
                    $params['payed_more'] ='1';
//                    $params['premium_more'] ='1';
                    break;
                case "subscription":
                    $params['real'] = '1';
                    $params['subscription_more'] ='1';
                    break;
                case "payed":
                    $params['real'] = '1';
//                    $params['payed_more'] ='1';
                    $params['favorite'] = '1';
                    break;
            }
        }
        $select = ['id','first_user_id','second_user_id','disabled','updated_at','is_answered_by_operator'];

        $group=[];
        if(isset($params['operator_id'])) {
            $select[] = DB::raw("IF(OperatorWork.operator_work = 1,OperatorWork.operator_id , '" . Auth::user()->id . "'  ) as operator_id");
            $select[] = DB::raw("IF(OperatorWork.operator_work = 1,(SELECT name FROM users WHERE id = OperatorWork.operator_id),'" . Auth::user()->name . "') as operator_name");
        }else{
           // $select[] = DB::raw("GROUP_CONCAT(IF(OperatorWork.operator_work = 1,OperatorWork.operator_id , '" . Auth::user()->id . "'  )) as operator_id");
           // $select[] = DB::raw("GROUP_CONCAT(IF(OperatorWork.operator_work = 1,(SELECT name FROM users WHERE id = OperatorWork.operator_id),'" . Auth::user()->name . "')) as operator_name");
            //  $select[] = DB::raw("OperatorWork.operator_work");
            $group[]  = 'id';

        }
        $select[] = DB::raw(" ChatLimit.limits as 'limit'");
        $select[] = DB::raw(" CEIL(ChatLimit.limits) as 'available_limit'");

        $select[] = DB::raw("(SELECT SUM(credits) FROM credit_logs
        WHERE credit_type = '".CreditLogTypeEnum::OUTCOME."' AND ((user_id = first_user_id AND other_user_id = second_user_id) || (user_id = second_user_id AND other_user_id = first_user_id) )) as max_limit");
        $chat = new ChatLogic($params,$select);
        $chat->setModel((new Chat()))->offPagination()->order('desc','updated_at')->setJoin(['OperatorWork'])->setGroupBy($group);
        if($operator->getRoleNames()->toArray()[0] == 'admin'){
            $chat->getQueryLink()->groupBy(['chats.id', 'ChatLimit.limits']);
        }
//        if ($params['filter_type'] == 'payed') {
//            $favUsers = DB::table('favorite_profiles')->where([['user_id', Auth::id()], ['disabled', 0]])->pluck('favorite_user_id')->toArray();
//            $o = implode(', ', $favUsers);
//            $chat->getQueryLink()->whereRaw("first_user_id IN ($o) OR second_user_id IN ($o)");
//        }
        $chat->getQueryLink()->with($join);
        $chats = $chat->getList();
        return response()->json(['data'=>$chats['result']]);
    }


   public function index1(Request $request){
         $operator = Auth::user();
           $filter = [
                'per_page' => $request->get('per_page'),
                "search" => $request->get('search'),
                'filter_type' => $request->get('filter_type'),
           ];

           if($request->get('chat_limit'))
               $filter['chat_limit'] = $request->get('chat_limit');

           if($operator->getRoleNames()->toArray()[0] != 'admin') {
               $filter['operator_id'] = (string)$operator->id;
             //  $filter['operator_work'] = '1';
           }
           $anketChats = $this->chatRepository->index($filter);

           $anketChats->getCollection()->each(function ($item) use($operator) {
               $item->available_limit = $item->limit ? floor($item->limit->limits) : 0;
               $item->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $item->otherUser->id)->where('other_user_id', $item->selfUser->id)->sum('credits');
               return $item;
           });
        return $anketChats;
    }
//20606
    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {

        /** @var User $user */
        $user = Auth::user();
        $per_page = \request()->get('per_page');
        if(is_null($per_page)){
            $per_page = 10;
        }
        /** @var Chat|JsonResponse $chat */
        $chat = $this->chatRepository->findForAnket($user, $id, ['firstUser', 'secondUser']);
        if ($chat instanceof JsonResponse) {
            return $chat;
        }

        $chatMessages = $this->chatRepository->getChatMessages($chat,$per_page);

        $chat->chat_messages = $chatMessages;
        $chat->chat_id = $chat->id;

        $chat->other_user->count_chats = ChatMessage::query()->where('chat_id', $chat->id)->where('sender_user_id', $chat->other_user->id)->count();
        $chat->other_user->count_letters = LetterMessage::query()->where('sender_user_id', $chat->other_user->id)->where('recepient_user_id', $chat->self_user->id)->count();
//        $chat->other_user->load('userGeo');
        //        $chat->other_user->count_images = $this->imageRepository->getUserImagesCount($chat->other_user);
        $chat->other_user->credits = CreditLog::query()->where('user_id', $chat->other_user->id)->where('other_user_id', $chat->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
//        $chat->self_user->count_images = $this->imageRepository->getUserImagesCount($chat->self_user);
//        $chat->self_user->count_public = $this->imageRepository->getUserImagesCount($chat->self_user, 2);
//        $chat->self_user->count_eighteen = $this->imageRepository->getUserImagesCount($chat->self_user, 4);
//        $chat->self_user->count_videos = $this->videoRepository->getUserVideoCount($chat->self_user);
        $this->updateChatSelfUser($chat);
        return response()->json($chat);
    }


    public function updateChatSelfUser($chat)
    {
        if ($user = $chat->self_user) {
            $user->updated_at = now();
            $user->online = true;
            $user->save();
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function searchChatMessage(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        /** @var User $user */
        $user = Auth::user();

        /** @var Chat $chat */
        $chat = $this->chatRepository->findForAnket($user, $id);

        if ($chat instanceof JsonResponse) {
            return $chat;
        }

        /** @var LengthAwarePaginator $messages */
        $messages = $this->chatRepository->searchChatMessages($chat, $request->text);
        $this->updateChatSelfUser($chat);

        return response()->json($messages);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    function sendMessage(Request $request, $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'text' => 'required|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $sender = User::find($chat->user_id);
        $recepient = User::find($chat->recepient_id);
        $chatMessage = $this->chatRepository->saveChatMessage($chat, $request->text);
        if ($recepient->is_real == 1 && !is_null($recepient->onesignal_token)) {
            OneSignalService::sendNotification($recepient->onesignal_token, 'CoolDreamy', "{$sender->name} sent you a message.", $sender->avatar_url);
        }
        if (!$recepient->online && $recepient->is_real == 1 &&  $recepient->email_verified_at != null  && $recepient->gender == 'male') {
            try {
                Mail::to($recepient)->send(new MessageUserMail($recepient, $sender));
            }catch (\Throwable $e){

            }
        }

        if (User::find($recepient->id)->credits <= 0) {
            if ($request->get('new_message')) {
                $this->workingShiftService->operatorSendAnsver($user->id, $chat->user_id, $chat->recepient_id, $chat->id, $chatMessage->id, 1);
            } else {
                $this->workingShiftService->operatorSendAnsver($user->id, $chat->user_id, $chat->recepient_id, $chat->id, $chatMessage->id);
            }
        }

        FireBaseService::sendPushFireBase($chat->recepient_id,"СoolDreamy","{$sender->name}, sent you a message.",$sender->avatar_url ?? null);
//       TODO добавть
//        if (AceLog::where('chat_id', $chat->id)->exists()) {
//            if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
//                OperatorLimitController::removeLimits($chat->recepient_id, 1);
//            } else {
//                OperatorLimitController::removeLimits($chat->recepient_id, 2);
//            }
//        }


        $this->updateChatSelfUser($chat);

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function ignore($id): JsonResponse
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == $chat->user_id) {
            Chat::query()->where('id', $chat->id)->update(['is_ignored_by_first_user' => true]);
        } else {
            Chat::query()->where('id', $chat->id)->update(['is_ignored_by_second_user' => true]);
        }
        $this->updateChatSelfUser($chat);

        return response()->json(['message' => 'success']);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function favorite($id)
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == $chat->user_id) {
            Chat::query()->where('id', $chat->id)->update(['is_anket_favorite_by_first_user' => !$chat->is_anket_favorite_by_first_user]);
        } else {
            Chat::query()->where('id', $chat->id)->update(['is_anket_favorite_by_second_user' => !$chat->is_anket_favorite_by_second_user]);
        }
        $this->updateChatSelfUser($chat);

        return response()->json(['message' => 'success']);
    }

    public function addUserToFavoritesForOperator($id)
    {
        try {
            $user = Auth::user();
            $chat = $this->chatRepository->findForAnket($user, $id);

            if ($chat->first_user_id == $chat->user_id) {
                $profile = FavoriteProfile::where([['user_id', $user->id], ['favorite_user_id', $chat->second_user_id]])->first();
                if ($profile) {
                    $profile->disabled = !$profile->disabled;
                    $profile->save();
                } else {
                    FavoriteProfile::create(['user_id' => $user->id, 'favorite_user_id' => $chat->second_user_id]);
                }
            } else {
                $profile = FavoriteProfile::where([['user_id', $user->id], ['favorite_user_id', $chat->first_user_id]])->first();
                if ($profile) {
                    $profile->disabled = !$profile->disabled;
                    $profile->save();
                } else {
                    FavoriteProfile::create(['user_id' => $user->id, 'favorite_user_id' => $chat->first_user_id]);
                }
            }
            $this->updateChatSelfUser($chat);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * @param $id
     * @return Chat|JsonResponse
     */
    public function media($id)
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!$chat instanceof Chat) {
            return $chat;
        }
        $this->updateChatSelfUser($chat);

        return response()->json(
            ChatImageMessage::whereHas('chat_message', function ($query) use ($id) {
                $query->where('chat_id', $id);
            })->paginate(5)
        );
    }

    public function anketMedia(Request $request, $id)
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!$chat instanceof Chat) {
            return $chat;
        }

//         $publicCategoryId = 2;
//        $privateCategoryId = 3;
//        $intimCategoryId = 4;

        $categoryId = $request->get('category_id');

        $chat->first_user_id = $user->id ? $chat->second_user_id : $chat->first_user_id;
        $query = Image::where('user_id',$user->id );

        if ($request->has('category_id')) {
            $query->where('category_id', $categoryId);
        }
        $this->updateChatSelfUser($chat);

        return $query->paginate(10);
    }

    /**
     * @param $id
     * @param $message
     * @return JsonResponse
     */
    public function read($id, $message): JsonResponse
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!$chat instanceof Chat) {
            return $chat;
        }

        $chatMessage = $this->chatRepository->findChatMessage($chat, $message);
        if ($chatMessage) {
            if ($chatMessage->recepient_user_id !== $chat->user_id) {
                return response()->json(['error' => 'You are not authorized for this action.'], 401);
            }
            $chatMessage->is_read_by_recepient = true;
            $chatMessage->save();
           // LetChatMessageNewReadEvent::dispatch($chatMessage->sender_user_id, $chatMessage->chat_id, $chatMessage->id);

//            if (AceLog::where('chat_message_id', $chat_message->id)->exists()) {
//                OperatorLimitController::addChatLimits($chat_message->recepient_user_id, 7);
//            }
        } else {
            return response()->json(['message' => 'not found'], 200);
        }
        $this->updateChatSelfUser($chat);

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * @param $id
     * @param $sticker
     * @return JsonResponse
     */
    public function sendSticker($id, $sticker): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $chat = $this->chatRepository->findForAnket($user, $id);

        $sticker = $this->stickerRepository->find($sticker);
        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $chatMessage = $this->chatRepository->saveChatSticker($chat, $sticker);
        $request = \request();
        if($request->get('new_message')){
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id,1);
        }else{
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id);
        }
//       TODO добавть
//        if (AceLog::where('chat_id', $chat->id)->exists()) {
//            if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
//                OperatorLimitController::removeLimits($chat->recepient_id, 1);
//            } else {
//                OperatorLimitController::removeLimits($chat->recepient_id, 2);
//            }
//        }
        $this->updateChatSelfUser($chat);

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function storeChat(Request $request)
    {
        $anketId = $request->get('anket_id');
        $manId = $request->get('man_id');
        $operatorChatLimit = $request->get('operator_chat_limit_id');

        $anket = User::findOrFail($anketId);
        $man = User::findOrFail($manId);

        $operatorChatLimit = OperatorChatLimit::query()->where('man_id', $manId)->where('girl_id', $anket->id)->findOrFail($operatorChatLimit);

        $chat = Chat::query()->where(function ($query) use ($anket, $man) {
            $query->where(function ($query) use ($anket, $man) {
                $query->where('first_user_id', $anket->id);
                $query->where('second_user_id', $man->id);
            })->orWhere(function ($query) use ($anket, $man) {
                $query->where('first_user_id', $man->id);
                $query->where('second_user_id', $anket->id);
            });
        })->first();

        if ($chat) {
            $chat->load(['limit', 'firstUser', 'secondUser']);

            $operatorChatLimit->chat_id = $chat->id;

            $operatorChatLimit->save();
            $chat->available_limit = round($operatorChatLimit->limits);
            // тут выводим кредиты
            $chat->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $chat->otherUser->id)->where('other_user_id', $chat->selfUser->id)->sum('credits');


            return $chat;
        }

        $chat = Chat::create([
            'first_user_id' => $anket->id,
            'second_user_id' => $man->id,
            'is_ignored_by_first_user' => false,
            'is_ignored_by_second_user' => false,
            'disabled' => false,
            'deleted_by_first_user' => false,
            'deleted_by_second_user' => false,
            'uuid' => Str::uuid()
        ]);
        $chat->load(['limit', 'firstUser', 'secondUser']);

        $operatorChatLimit->chat_id = $chat->id;

        $operatorChatLimit->save();
        $chat->available_limit = round($operatorChatLimit->limits);
        // тут выводим кредиты
        $chat->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $chat->otherUser->id)->where('other_user_id', $chat->selfUser->id)->sum('credits');
        $this->updateChatSelfUser($chat);

        return response()->json($chat);
    }

    /**
     * @param $id
     * @param $gift
     * @return JsonResponse
     */
    public function sendGift($id, $gift): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $chat = $this->chatRepository->findForAnket($user, $id);

        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $gift = $this->gifRepository->find($gift);
        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $chatMessage = $this->chatRepository->saveChatGift($chat, $gift);
        $request = \request();
        if($request->get('new_message')){
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id,1);
        }else{
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id);
        }

        $this->updateChatSelfUser($chat);

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function sendImage(Request $request, $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'thumbnail_url' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
            'category_id' => 'required|exists:image_categories,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $chatMessage = $this->chatRepository->saveChatImage($chat, $request->get('image_url'), $request->get('thumbnail_url'), $request->get('category_id'));
        $request = \request();
        if($request->get('new_message')){
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id,1);
        }else{
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id);
        }
        $this->updateChatSelfUser($chat);

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }

    public function sendVideo(Request $request, $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'video_url' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $chat = $this->chatRepository->findForAnket($user, $id);
        if (!OperatorLimitController::spendLimitsByOperator($chat->user_id, $chat->recepient_id, $chat->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $chatMessage = $this->chatRepository->saveChatVideo($chat, $request->get('video_url'));
        $request = \request();
        if($request->get('new_message')){
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id,1);
        }else{
            $this->workingShiftService->operatorSendAnsver($user->id,$chat->user_id,$chat->recepient_id,$chat->id,$chatMessage->id);
        }
        $this->updateChatSelfUser($chat);

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $chat = $this->chatRepository->findForAnket($user, $id);

        if ($chat->first_user_id == $chat->user_id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            Chat::where('id', $chat->id)->update(['deleted_by_first_user' => true]);
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            Chat::where('id', $chat->id)->update(['deleted_by_second_user' => true]);
        }

        $this->chatRepository->saveChatMessage($chat, trans('user.chat.user_close_chat'));
        $this->updateChatSelfUser($chat);

        return response()->json(['message' => 'success'], 200);
    }
}
