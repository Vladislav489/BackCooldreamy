<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Action\ActionEnum;
use App\Enum\Rating\RatingAssignmentEnum;
use App\Http\Controllers\Controller;
use App\Mail\MessageUserMail;
use App\Mail\MyVerificationMail;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatMessageLogic;
use App\Models\AceLog;
use App\Models\Chat;
use App\Models\ChatGiftMessage;
use App\Models\ChatMessage;
use App\Models\ChatStickerMessage;
use App\Models\ChatTextMessage;
use App\Models\ChatImageMessage;
use App\Models\ChatVideoMessage;
use App\Models\FavoriteProfile;
use App\Models\Gift;
use App\Models\LetterMessage;
use App\Models\Operator\OperatorReport;
use App\Models\ServicePrices;
use App\Models\User;
use App\Models\UserPayedMessagesToOperators;
use App\Repositories\Auth\CreditLogRepository;
use App\Repositories\Operator\ChatRepository;
use App\Services\FireBase\FireBaseService;
use App\Services\Rating\RatingService;
use App\Traits\UserSubscriptionTrait;
use App\Traits\VideoStoreTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ChatController extends Controller
{
    use VideoStoreTrait;
    /** @var RatingService */
    private RatingService $ratingService;

    /** @var UserSubscriptionTrait */
    private UserSubscriptionTrait $userSubscriptionTrait;

    /** @var CreditLogRepository */
    private CreditLogRepository $creditLogRepository;

    public function __construct(
        RatingService $ratingService,
        UserSubscriptionTrait $userSubscriptionTrait,
        CreditLogRepository $creditLogRepository
    ){
        $this->ratingService = $ratingService;
        $this->userSubscriptionTrait = $userSubscriptionTrait;
        $this->creditLogRepository = $creditLogRepository;
    }

    public function payForImage(Request $request, $chatMessage){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'service_id' => [
                'required', 'integer',
                Rule::exists('service_prices', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $service = ServicePrices::find($request->service_id);
        $chatMessage = ChatMessage::findOrFail($chatMessage);
        if ( $user->check_payment_man($service->price,$request->service_id,'chat',$chatMessage->sender_user_id)) {
            $chatMessage->is_payed = true;
            $chatMessage->save();
            return response()->json(['message' => 'success']);
        } else {
            return response()->json(['error' => 'You dont have enough credits!']);
        }
    }

    public function sendTest()
    {
        try {
          //  Mail::to("ryzhakovalexeynicol@gmail.com")->queue(new MyVerificationMail('23', 123));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public static function get_current_chat_list_item($chat_id,$user = null, $its_event = false, $its_ace = false, $girl = null)
    {
        $chat = Chat::findOrFail($chat_id);
        if(is_null($user)){$user = Auth::user();}
        $last_message = ChatMessage::with('chat_messageable.gifts', 'chat_messageable.sticker')
            ->where('chat_id', $chat_id)->latest()->first();
        $chat->last_message = $last_message;
        $chat->last_message->chat_messageable = $last_message->chat_messageable;
        if (!$its_ace) {
            if ($its_event) {
                $chat->another_user = $user;
                $recepient_id = ($chat->first_user_id == $user->id)?$chat->second_user_id: $chat->first_user_id;
                $favorite_users = FavoriteProfile::where('user_id', $recepient_id)->where('disabled', false)->pluck('favorite_user_id');
            } else {
                $chat->another_user = $chat->another_user;
                $favorite_users = FavoriteProfile::where('user_id', $user->id)->where('disabled', false)->pluck('favorite_user_id');
            }
        } else {
            $chat->another_user = $girl;
            $recepient_id =  ($chat->first_user_id == $chat->another_user->id)?$chat->second_user_id:$chat->first_user_id;
            $favorite_users = FavoriteProfile::where('user_id', $recepient_id)
                ->where('disabled', false)
                ->pluck('favorite_user_id');
        }

        $chat->favorite = ($favorite_users->contains($chat->first_user_id) || $favorite_users->contains($chat->second_user_id)) ? 1 : 0;
        $item = [];
        $chat->updated_at = now();
        $item['chat'] = $chat;
        return $item;
    }

    public function get_my_chat_list(Request $request){
        $perPage = 10;
        if (isset($request->per_page))
            $perPage = $request->per_page;
        $user_id = Auth::id();
        $chat_list = (new ChatLogic())->getListChatUser($user_id,$request);
        $chat_id = [];
        $chatBuffer = [];
        foreach ($chat_list as $item) {
            array_push($chat_id, (string)$item['id']);
            $chatBuffer[$item['id']] = $item;
        }
        $temp = [];
        $chatNotRead = (new ChatMessageLogic())->getChatNotReadUser($user_id,$chat_id);
        foreach ($chatNotRead as $item) $temp[$item['chat_id']] = $item;
        $chatNotRead = $temp;
        $temp = [];
        $lastMessage = (new ChatMessageLogic())->getChatLastMessage($user_id,$chat_id);
        foreach ($lastMessage as $item){
            if($item['sender_user_id'] == $user_id){
                $item['sender_user'] = $chatBuffer[$item['chat_id']]['my_self_user'];
                $item['recepient_user'] = $chatBuffer[$item['chat_id']]['another_user'];
            }
             if($item['recepient_user_id'] == $user_id){
                 $item['recepient_user'] = $chatBuffer[$item['chat_id']]['my_self_user'];
                 $item['sender_user'] = $chatBuffer[$item['chat_id']]['another_user'];
             }
            $temp[$item['chat_id']] = $item;
        }
        $lastMessage = $temp;

        foreach ($chat_list as &$item){
            $item['unread_messages_count'] = (isset($chatNotRead[$item['id']]))?$chatNotRead[$item['id']]['unread_messages_count']:0;
            $item['last_message'] = $lastMessage[$item['id']];
        }

        return response(['data'=>$chat_list]);
    }



    public function get_my_chat_list1(Request $request){
        $perPage = 10;
        if (isset($request->per_page))
            $perPage = $request->per_page;

        $user = Auth::user();
        $user_id = $user->id;
        $favorite_users = FavoriteProfile::where('user_id', $user_id)->where('disabled', false)->pluck('favorite_user_id');

        $chat_list = Chat::query()->withCount(['unreadMessages' => function($query) use ($user) {
            $query->where('recepient_user_id', $user->id);
        }])->where(function ($query) use ($user_id) {
            $query->where(function (Builder $builder) use ($user_id) {
                $builder->where('first_user_id', $user_id)
                    ->where('deleted_by_first_user', false);
            })->orWhere(function (Builder $builder) use ($user_id) {
                $builder->where('second_user_id', $user_id)
                    ->where('deleted_by_second_user', false);
            });
        })->when($request->input('filter'), function ($query) use ($user, $request, $favorite_users) {


                if ($request->filter == 'unread') {
                    $query->whereHas('chat_messages', function ($q) use ($user) {
                        $q->where('recepient_user_id', $user->id)->where('is_read_by_recepient', false);
                    });
                }


                if ($request->filter == 'favorite') {
                    $query->where(function ($query) use ($favorite_users, $user) {
                        $query->where(function ($query) use ($favorite_users, $user) {
                            $query->where('first_user_id', $user->id)
                                ->whereIn('second_user_id', $favorite_users);
                        });
                        $query->orWhere(function ($query) use ($favorite_users, $user) {
                            $query->where('second_user_id', $user->id)
                                ->whereIn('first_user_id', $favorite_users);
                        });
                    });
                }

                if ($request->filter == 'ignored') {
                    $query->where(function ($query) {
                        $query->where('is_ignored_by_first_user', true)
                            ->orWhere('is_ignored_by_second_user', true);
                    });
                }
            })
            ->whereHas('chat_messages')
            ->orderBy('updated_at', 'desc');





        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $chat_list->where(function ($query) use ($user_id, $search) {
                $query->where(function (Builder $builder) use ($user_id, $search) {
                    $builder->where('first_user_id', $user_id)
                        ->whereHas('secondUser', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                })->orWhere(function (Builder $builder) use ($user_id, $search) {
                    $builder->where('second_user_id', $user_id)
                        ->whereHas('', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                });
            });
        }

        $chat_list = $chat_list->paginate($perPage);

        foreach ($chat_list as &$chat) {
            $last_message = ChatMessage::with('chat_messageable.gifts', 'chat_messageable.sticker')->where('chat_id', $chat->id)->latest()->first();
            $chat->last_message = $last_message;
            if (isset ($chat->last_message->chat_messageable)) {
                $chat->last_message->chat_messageable = $last_message->chat_messageable;
            }
            $chat->favorite = ($favorite_users->contains($chat->first_user_id) || $favorite_users->contains($chat->second_user_id)) ? 1 : 0;
            $chat->another_user = $chat->another_user;
            $chat->self_user = $chat->my_self_user;
        }
        return response($chat_list);
    }

    public function unread()
    {
        $user = Auth::user();

        return response()->json([
            'count_chat_messages' => ChatMessage::query()->whereHas('chat', function ($query) {
                $query->where('deleted_by_first_user', false)->where('deleted_by_second_user', false);
            })->where('recepient_user_id', $user->id)->where('is_read_by_recepient', false)->count(),
            'count_letter_messages' => LetterMessage::query()->where('recepient_user_id', $user->id)->where('is_read_by_recepient', false)->count(),
        ]);
    }

    public function get_chat_statistics()
    {
        $user = Auth::user();
        $data = [];
        $user_id = $user->id;
        $favorite_users = FavoriteProfile::where('user_id', $user_id)
            ->where('disabled', false)
            ->pluck('favorite_user_id');
        $data['favorites'] = Chat::whereIn('first_user_id', $favorite_users)
            ->orWhereIn('second_user_id', $favorite_users)
            ->orderBy('updated_at', 'desc')
            ->count();

        $data['ignored'] = Chat::query()->where(function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->where('first_user_id', $user->id)->where('is_ignored_by_first_user', true);
            })->orWhere(function ($query) use ($user) {
                $query->orWhere('second_user_id', $user->id)->where('is_ignored_by_second_user', true);
            });
        })->orderBy('updated_at', 'desc')->count();

        return response()->json($data);
    }

    public function get_my_favorite_chat_list()
    {
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }

        $user = Auth::user();

        $user_id = $user->id;
        $favorite_users = FavoriteProfile::where('user_id', $user_id)
            ->where('disabled', false)
            ->pluck('favorite_user_id');
        $chat_list = Chat::whereIn('first_user_id', $favorite_users)
            ->orWhereIn('second_user_id', $favorite_users)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
        foreach ($chat_list as $chat) {
            $last_message = ChatMessage::with('chat_messageable.gifts', 'chat_messageable.sticker')->where('chat_id', $chat->id)->latest()->first();
            $chat->last_message = $last_message;
            if (isset ($chat->last_message->chat_messageable)) {
                $chat->last_message->chat_messageable = $last_message->chat_messageable;
            }
            $chat->favorite = ($favorite_users->contains($chat->first_user_id) || $favorite_users->contains($chat->second_user_id)) ? 1 : 0;
            $chat->another_user = $chat->another_user;
        }
        return response($chat_list);
    }

    public function get_chat_with_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
        ]);
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $target_user_id = $request->user_id;
        $user_id = Auth::user()->id;

        $chat = $this->getChat($user_id, $target_user_id);
        $chat_messages = $this->getChat_messages($chat, $perPage);
        $resp = new \stdClass();
        $resp->chat_messages = $chat_messages;
        $resp->chat_id = $chat->id;
        $resp->another_user = $chat->another_user;
        return response(json_encode($resp));
    }

    public function get_current_chat1(Request $request){
        $validator = Validator::make($request->all(), ['chat_id' => ['required',Rule::exists('chats', 'id')]]);
        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 500);

        $user_id = Auth::id();
        $chat = (new ChatLogic())->getListChatUser($user_id,$request)[0];
        if(count($chat) == 0)
            return response()->json(['error' => 'You deleted the chat.'], 404);

        if ($chat['first_user_id'] !== $user_id && $chat['second_user_id'] !== $user_id)
            return response()->json(['error' => 'You are not authorized to open this chat.'], 401);

        $chatMessage = new ChatMessageLogic(['chat_id'=>(string)$chat['id']], ['id','sender_user_id','recepient_user_id','chat_id','chat_messageable_id',
            'chat_messageable_type','is_read_by_recepient', 'disabled', 'created_at', 'updated_at',
             'is_payed' , 'is_ace']);
        $Message = $chatMessage->offPagination()
            ->order('desc','chat_messages.updated_at')
            ->setJoin(['TextMessageSub','WinkMessageSub', 'ImageMessageSub','GiftMessageSub','StickerMessageSub'])
            ->getList()['result'];
       // dd($chat);
        foreach ($Message as &$item){
                if ($item['sender_user_id'] == $user_id) {
                    $item['sender_user'] = $chat['first_user'];
                    $item['recepient_user'] = $chat['second_user'];
                } else if ($item['recepient_user_id'] == $user_id) {
                    $item['sender_user'] = $chat['second_user'];
                    $item['recepient_user'] = $chat['first_user'];
                }
        }
        $resp = new \stdClass();
        $resp->chat_messages = $Message;
        $resp->chat_id = $chat['id'];
        $resp->another_user = $chat['another_user'];
        return response(json_encode($resp));
    }

    public function get_current_chat(Request $request){
        $validator = Validator::make($request->all(), [
            'chat_id' => [
                'required',
                Rule::exists('chats', 'id'),
            ],
        ]);

        $user = Auth::user();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $user_id = $user->id;
        $chat = Chat::findorfail($request->chat_id);
        if ($chat->first_user_id !== $user_id && $chat->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to open this chat.'], 401);
        }
        if ($chat->first_user_id == $user_id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'You deleted the chat.'], 404);
            }
        }

        if ($chat->second_user_id == $user_id) {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'You deleted the chat.'], 404);
            }
        }

        $chat_messages = $this->getChat_messages($chat, $perPage);
        $resp = new \stdClass();
        $resp->chat_messages = $chat_messages;
        $resp->chat_id = $chat->id;
        $resp->another_user = $chat->another_user;

        return response(json_encode($resp));
    }

    public function getServicePrice($serviceId)
    {
        return ServicePrices::where('id', $serviceId)->first()->price;
    }

    public function send_chat_text_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => [
                'required', 'integer',
                Rule::exists('chats', 'id'),
            ],
            'text' => 'required|max:300',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();
        if(!User::query()->from('prompt_career_user')->where("user_id","=",$user->id)->exists()){
            return response()->json(['error' => 'You need to fill in information about yoursel'], 403);
        }


        $chat = Chat::findorfail($request->chat_id);
        if ($chat->first_user_id !== $user->id && $chat->second_user_id !== $user->id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }

        if ($chat->first_user_id == $user->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(1,ActionEnum::SEND_MESSAGE,$chat->second_user_id == $user->id ? $chat->first_user_id: $chat->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        [$sender_user_id, $recepient_user_id] = $this->extracted($chat, $user->id);

        $chat_text_message = ChatTextMessage::create(['text' => $request->text]);

        $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'operator_get_ansver' => $operator
        ]);

        // Send email notification when $recepient offline
        $sender = User::find($sender_user_id);
        $recepient = User::find($recepient_user_id);
        if (!$recepient->online && $recepient->is_real == 1 && $recepient->email_verified_at != null && $recepient->gender == 'male') {
            try {
                Mail::to(User::find($recepient))->send(new MessageUserMail($recepient, $sender));
            }catch (\Throwable $e){

            }
        }
        if ($recepient->is_real == 0 && $sender->credits <= 0) {
            UserPayedMessagesToOperators::create([
                'user_id' => $sender->id,
                'ancet_id' => $recepient->id,
                'operator_id' => $operator,
                'credits' => $this->getServicePrice(1),
                'message_type' => 1
            ]);
        }

        $chat_text_message->chat_message()->save($chat_message);
        //You have received 1 new message on the site
        $chat_message->chat_messageable = $chat_message->chat_messageable;
        FireBaseService::sendPushFireBase($recepient,"СoolDreamy","{$sender->name}  sent you a message.",$sender->avatar_url);
        $chatListItem = self::get_current_chat_list_item($request->chat_id,$user, true);

       // ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
        $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);

        if ($user->gender == 'male') {
            if ($this->userSubscriptionTrait->checkUserExistsSubscription($user)) {
                $this->ratingService->saveUserRating($user, RatingAssignmentEnum::PREMIUM_MESSAGE);
            } else {
                $this->ratingService->saveUserRating($user, RatingAssignmentEnum::CREDIT_MESSAGE);
            }
        }

//        if (AceLog::where('chat_id', $chat->id)->exists()) {
        if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
            OperatorLimitController::addChatLimits($recepient_user_id, 1, $chat->id);
        } else {
            OperatorLimitController::addChatLimits($recepient_user_id, 2, $chat->id);
        }
//        }

        return (self::get_current_chat_list_item($request->chat_id,$user));
//        return response($chat_text_message);
    }

    private function setChatAnswered(Chat $chat)
    {
        $chat->is_answered_by_operator = false;
        $chat->updated_at = now();
        $chat->save();
    }

    public function send_chat_video_message(Request $request)
    {
        dump('scvm1');
        $validator = Validator::make($request->all(), [
            'video' => [
                'required',
                File::types(['mp4', 'wmv', 'avi', 'webm', 'mov'])->max(20 * 1024)
            ],
            'chat_id' => [
                'required', 'integer',
                Rule::exists('chats', 'id'),
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        dump('scvm2');
        $chat = Chat::find($request->get('chat_id'));
        if ($chat) {
            if ($chat->first_user_id == Auth::user()->id) {
                if ($chat->deleted_by_first_user) {
                    return response()->json(['error' => 'chat is deleted'], 404);
                }
            } else {
                if ($chat->deleted_by_second_user) {
                    return response()->json(['error' => 'chat is deleted'], 404);
                }
            }
            $user = Auth::user();

            $user_id = $user->id;
            if ($chat->first_user_id !== $user_id && $chat->second_user_id !== $user_id) {
                return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
            }
            dump('scvm3');
            $credits = new CreditsController();
            $resultCheckPayment = $credits->check_payment(4, ActionEnum::SEND_VIDEO_IN_CHAT, $chat->second_user_id == $user->id ? $chat->first_user_id : $chat->second_user_id);
            dump('scvm4');
            if (is_object($resultCheckPayment)) {
                return $resultCheckPayment;
            }
            dump('scvm5');
            $video = self::store_video_content($user, $request->video, $user->gender);
            if (!$video) {
                dump('scvm - error saving video');
                $userCredits = User\CreditsReals::where('user_id', $user_id)->first();
                $serviceCost = $this->getServicePrice(4);
                $userCredits->credits += $serviceCost;
                $userCredits->save();
                return response()->json(['error' => 'unable to save video'], 500);
            }
            dump('scvm6');
            [$sender_user_id, $recepient_user_id] = $this->extracted($chat, $user_id);
            $chat_video_message = ChatVideoMessage::create(['video_url' => $video->video_url, 'is_payed' => true]);
            $isPayed = true;
            $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
            dump('scvm7');
            $chat_message = new ChatMessage([
                'chat_id' => $chat->id,
                'sender_user_id' => $sender_user_id,
                'recepient_user_id' => $recepient_user_id,
                'is_payed' => $isPayed,
                'operator_get_ansver' => $operator
            ]);
            dump('scvm8');
            $chat_video_message->chat_message()->save($chat_message);
            dump('scvm9');
            $sender = User::find($sender_user_id);
            $recepient = User::find($recepient_user_id);
            if ($recepient->is_real == 0) {
                UserPayedMessagesToOperators::create([
                    'user_id' => $sender->id,
                    'ancet_id' => $recepient->id,
                    'operator_id' => $operator,
                    'credits' => $this->getServicePrice(4),
                    'message_type' => 3
                ]);
            }
            dump('scvm10');
            $chat_message->chat_messageable = $chat_message->chat_messageable;
            dump('scvm11');
            $chatListItem = self::get_current_chat_list_item($request->chat_id, $user, true);
            dump('scvm12');
            //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
            $this->setChatAnswered($chat);
            dump('scvm13');
            $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);
            dump('scvm14');

            return (self::get_current_chat_list_item($request->chat_id, $user));
        }
        return response()->json(['error' => 'chat not found'], 404);
    }

    public function send_chat_image_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => [
                'required', 'integer',
                Rule::exists('chats', 'id'),
            ],
            'thumbnail_url' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $user_id = $user->id;
        $chat = Chat::findorfail($request->chat_id);
        if ($chat->first_user_id !== $user_id && $chat->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }
        if ($chat->first_user_id == Auth::user()->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(3,ActionEnum::VIEWING_PHOTO_IN_CHAT,$chat->second_user_id == $user->id ? $chat->first_user_id: $chat->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        [$sender_user_id, $recepient_user_id] = $this->extracted($chat, $user_id);

        $chat_image_message = ChatImageMessage::create([
            'image_url' => $request->image_url,
            'thumbnail_url' => $request->thumbnail_url,
            'is_payed' => true
        ]);
        $countImages = ChatMessage::query()->where('chat_id', $chat->id)->where('chat_messageable_type', ChatImageMessage::class)->count();
        if ($user->gender == 'female' && $countImages >= Chat::COUNT_FREE_IMAGES) {
            $isPayed = false;
        } else {
            $isPayed = true;
        }
        $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'is_payed' => $isPayed,
            'operator_get_ansver' => $operator
        ]);
        $chat_image_message->chat_message()->save($chat_message);

        $sender = User::find($sender_user_id);
        $recepient = User::find($recepient_user_id);
        if ($recepient->is_real == 0) {
            UserPayedMessagesToOperators::create([
                'user_id' => $sender->id,
                'ancet_id' => $recepient->id,
                'operator_id' => $operator,
                'credits' => $this->getServicePrice(3),
                'message_type' => 2
            ]);
        }

        $chat_message->chat_messageable = $chat_message->chat_messageable;
        $chatListItem = self::get_current_chat_list_item($request->chat_id, $user,true);
        //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
        $this->setChatAnswered($chat);
        $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);
        OperatorLimitController::addChatLimits($recepient_user_id, 9, $chat->id);
        return (self::get_current_chat_list_item($request->chat_id,$user));
//        return response($chat_image_message);
    }

    /**
     * @param $recepient_user_id
     * @param $chat_message
     * @param $chatListItem
     */
    private function sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem)
    {
        $recepient = User::findOrFail($recepient_user_id);

        if ($recepient->is_real == false) {
            if ($recepient->operator) {
                $operator = $recepient->operator->operator;
                if ($operator) {
                   //ObjectOperatorChatEvent::dispatch($operator->id, $chat_message, $chatListItem);
                    $this->setChatAnswered($chat_message->chat);
                }
            }
        }
    }

    public function send_chat_sticker_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => [
                'required', 'integer',
                Rule::exists('chats', 'id'),
            ],
            'sticker_id' => [
                'required', 'integer',
                Rule::exists('stickers', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $user_id = $user->id;
        $chat = Chat::findorfail($request->chat_id);
        if ($chat->first_user_id !== $user_id && $chat->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }
        if ($chat->first_user_id == Auth::user()->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(7,ActionEnum::SEND_STICKER_IN_CHAT,$chat->second_user_id == $user->id ? $chat->first_user_id: $chat->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        [$sender_user_id, $recepient_user_id] = $this->extracted($chat, $user_id);

        $chat_sticker_message = ChatStickerMessage::create(['sticker_id' => $request->sticker_id]);
        $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'operator_get_ansver' => $operator
        ]);
        $chat_sticker_message->chat_message()->save($chat_message);

        $sender = User::find($sender_user_id);
        $recepient = User::find($recepient_user_id);
        if ($recepient->is_real == 0) {
            UserPayedMessagesToOperators::create([
                'user_id' => $sender->id,
                'ancet_id' => $recepient->id,
                'operator_id' => $operator,
                'credits' => $this->getServicePrice(7),
                'message_type' => 4
            ]);
        }

        $chat_message->chat_messageable->sticker = $chat_message->chat_messageable->sticker;
        $chatListItem = self::get_current_chat_list_item($request->chat_id,$user, true);
        //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
        $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);
        OperatorLimitController::addChatLimits($recepient_user_id, 8, $chat->id);
        return (self::get_current_chat_list_item($request->chat_id,$user));
//        return response($chat_sticker_message);
    }

    // получае счёт пользователя
    public function get_my_credits()
    {
        // получаем пользователя, а затем возвращаем баланс его счёта
        $user = Auth::user();
        return response($user->getCreditsAttribute());
    }


    public function send_chat_gift_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required'
            ],
            'gifts' => [
                'required',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        if(!is_null($request->get('chat_id'))){
            $chat = Chat::findorfail($request->chat_id);
            $user = User::find($request->user_id);
        }else{
            $list = (new ChatLogic(['chat_by_first_sec_user'=>[Auth::user()->id,$request->user_id]]))->getOne();
            if(count($list) == 0) {
                $chat = new Chat();
                $uuid = Str::uuid();
                $chat->setRawAttributes([
                    'first_user_id' => Auth::user()->id,
                    'second_user_id' => $request->user_id,
                    'uuid' => $uuid,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $chat->save();
                $chat = Chat::query()->where('uuid', $uuid)->get()->first();
            }else{
                $chat = Chat::findorfail($list['id']);
            }
            $user = User::find(Auth::user()->id);
        }
        if ($chat->first_user_id !== $user->id && $chat->second_user_id !== $user->id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }
        if ($chat->first_user_id == $user->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        if(is_string($request->input('gifts'))){
            $gifts = json_decode($request->input('gifts'),true);
        }else{
            $gifts = $request->input('gifts');
        }

        if (!isset($gifts)) {
            return response()->json(['error' => 'Not correct gifts id array'], 500);
        }

        $allCredits = 0;
        foreach ($gifts as $item) {
            $gift = Gift::findorfail($item);
            $credits = $gift->credits;
            $allCredits += $credits;
        }
        //int(60) int(126693) int(81422)
        //int(60) int(126693) int(81422)

        [$sender_user_id, $recepient_user_id] = $this->extracted($chat, Auth::user()->id);
        if (!$user->check_payment($allCredits,13,ActionEnum::SEND_GIFT_IN_CHAT,$recepient_user_id)) {
            return response()->json(['error' => "The purchase price exceeds the amount in the user's account!", "acquiring" => 1], 500);
        }

            //это из профайла
            //https://api2.cooldreamy.com/api/chats/send_chat_gift_message
            /*
             * {gifts: [12], user_id: 81422}
                gifts: [12]
                user_id: 81422
             */
            // это из чата
            //https://api2.cooldreamy.com/api/chats/send_chat_gift_message
            /*
             * chat_id: "34583"
             * gifts: [12]
             * user_id: "126693"
             *
             */

        $chat_gift_message = ChatGiftMessage::create();
        foreach ($gifts as $item) {
            $gift = Gift::findorfail($item);
            $chat_gift_message->gifts()->attach($gift);
        }
        $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'operator_get_ansver' => $operator
        ]);
        $chat_gift_message->chat_message()->save($chat_message);

        $sender = User::find($sender_user_id);
        $recepient = User::find($recepient_user_id);
        if ($recepient->is_real == 0) {
            UserPayedMessagesToOperators::create([
                'user_id' => $sender->id,
                'ancet_id' => $recepient->id,
                'operator_id' => $operator,
                'credits' => $allCredits,
                'message_type' => 5
            ]);
        }

        $chat_message->chat_messageable->gifts = $chat_message->chat_messageable->gifts;
        $chatListItem = self::get_current_chat_list_item($chat->id,$user, true);



        //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
        $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);
        $another_user = User::find($recepient_user_id);
        if (!$another_user->is_real) {
            OperatorLimitController::addChatLimits($recepient_user_id, 6, $chat->id);
        }
        return (self::get_current_chat_list_item($chat->id,$user,$user));
    }

    public function set_chat_message_is_read(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_message_id' => [
                'required', 'integer',
                Rule::exists('chat_messages', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $user_id = $user->id;
        $chat_message = ChatMessage::where('id', $request->chat_message_id)->where('is_read_by_recepient', false)->first();
        if ($chat_message) {
            if ($chat_message->recepient_user_id !== $user_id) {
                return response()->json(['error' => 'You are not authorized for this action.'], 401);
            }
            $chat_message->is_read_by_recepient = true;
            $chat_message->save();
            //LetChatMessageNewReadEvent::dispatch($chat_message->sender_user_id, $chat_message->chat_id, $chat_message->id);
            $sender = User::findOrFail($chat_message->sender_user_id);
            if ($sender->is_real == false) {
                if ($sender->operator) {
                    $operator = $sender->operator->operator;
                    //if ($operator) {
                    //    TestOperatorChatReadEvent::dispatch($operator->id, $chat_message->chat_id, $chat_message->id);
                  //  }
                }
            }

            if (AceLog::where('chat_message_id', $chat_message->id)->exists()) {
                OperatorLimitController::addChatLimits($chat_message->recepient_user_id, 7, $chat_message->chat_id);
            }
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function getChat_messages(Chat $chat, $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $chat_messages = $chat->chat_messages()
            ->with(['sender_user' => function ($query) {
                $query->select('id', 'name', 'avatar_url_thumbnail');
            }, 'chat_messageable.sticker', 'chat_messageable.gifts'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return $chat_messages;
    }

    public static function getChat($user_id, $target_user_id)
    {
        $chat = (new ChatLogic(['chat_by_first_sec_user'=>[$user_id, $target_user_id]]))->offPagination()->getFullQuery()->get()->first();
        if (is_null($chat)) {
            $chat = Chat::create([
                'first_user_id' => $user_id,
                'second_user_id' => $target_user_id,
                'uuid' => Str::uuid()
            ]);
        }
        return $chat;
    }

    public static function extracted($chat, $user_id): array
    {
        if ($chat->first_user_id == $user_id) {
            $sender_user_id = $user_id;
            $recepient_user_id = $chat->second_user_id;
        } else {
            $sender_user_id = $user_id;
            $recepient_user_id = $chat->first_user_id;
        }
        return array($sender_user_id, $recepient_user_id);
    }

    public function searchChatMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => [
                'required', 'integer',
                Rule::exists('chats', 'id'),
            ],
            'text' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }

        $user = Auth::user();

        $chat = Chat::where('id', $request->chat_id)
            ->where(function ($query) use ($user) {
                $query->where('first_user_id', $user->id)
                    ->orWhere('second_user_id', $user->id);
            })->first();
        if (!$chat) {

            return response()->json(['error' => 'not found chat'], 500);
        }
        $messages = $chat->chat_messages()->whereHasMorph('chat_messageable', [ChatTextMessage::class], function ($q) use ($request) {
            $q->where('text', 'LIKE', '%' . $request->text . '%');
        })->with('chat_messageable')->orderBy('created_at', 'desc')->paginate($perPage);

        return response(json_encode($messages));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    private function findChat($id)
    {
        $chat = Chat::where('id', $id)
            ->where(function ($query) {
                $query->where('first_user_id', Auth::user()->id)
                    ->orWhere('second_user_id', Auth::user()->id);
            })->first();

        if (!$chat) {
            return response()->json(['error' => 'not found chat'], 404);
        }

        if ($chat->first_user_id == Auth::user()->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        return $chat;
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $chat = Chat::where('id', $id)
            ->where(function ($query) {
                $query->where('first_user_id', Auth::user()->id)
                    ->orWhere('second_user_id', Auth::user()->id);
            })->first();

        if (!$chat) {
            return response()->json(['error' => 'not found chat'], 404);
        }

        if ($chat->first_user_id == Auth::user()->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            $chat->deleted_by_first_user = true;
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            $chat->deleted_by_second_user = true;
        }

        $user = Auth::user();
        [$sender_user_id, $recepient_user_id] = $this->extracted($chat,$user->id );
        $chat_text_message = ChatTextMessage::create(['text' => trans('user.chat.user_close_chat')]);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
        ]);
        $chat_text_message->chat_message()->save($chat_message);

        $chat_message->chat_messageable = $chat_message->chat_messageable;

        $chatListItem = self::get_current_chat_list_item($chat->id,$user, true);
        //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem['chat']);
        $this->sendOperatorEvent($recepient_user_id, $chat_message, $chatListItem['chat']);

        $chat->save();

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function ignore($id): JsonResponse
    {
        $chat = $this->findChat($id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == Auth::user()->id) {
            $chat->is_ignored_by_first_user = !$chat->is_ignored_by_first_user;
        } else {
            $chat->is_ignored_by_second_user = !$chat->is_ignored_by_second_user;
        }

        $chat->save();
        $chat->load('another_user');

        return response()->json($chat);
    }

    public function report(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'text' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $chat = $this->findChat($id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == Auth::id()) {
            $anket = $chat->secondUser;
        } else {
            $anket = $chat->firstUser;
        }

        if (OperatorReport::query()->where('man_id', Auth::id())->where('anket_id', $anket->id)->exists()) {
            return response()->json(['error' => 'Already exists'], 500);
        }

        OperatorReport::create([
            'man_id' => Auth::id(),
            'operator_id' => $anket->operator ? $anket->operator->id : null,
            'anket_id'  => $anket->id,
            'date_time' => Carbon::now(),
            'text' => $request->text,
            'is_important' => true
        ]);

        return response()->json(['message' => 'success']);
    }

    public function media($id)
    {
        $chat = Chat::where('id', $id)
            ->where(function ($query) {
                $query->where('first_user_id', Auth::user()->id)
                    ->orWhere('second_user_id', Auth::user()->id);
            })->first();

        if (!$chat) {
            return response()->json(['error' => 'not found chat'], 404);
        }

        if ($chat->first_user_id == Auth::user()->id) {
            if ($chat->deleted_by_first_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            $chat->deleted_by_first_user = true;
        } else {
            if ($chat->deleted_by_second_user) {
                return response()->json(['error' => 'chat already deleted'], 404);
            }

            $chat->deleted_by_second_user = true;
        }

        return response()->json(
            ChatImageMessage::whereHas('chat_message', function ($query) use ($id) {
                $query->where('chat_id', $id);
            })->paginate(5)
        );
    }

    public function payToView(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer|exists:chats,id',
            'message_id' => 'required',
            'chat_messageable_type' => 'required'
        ]);
        $chat = Chat::find($request->get('chat_id'));
        if ($chat) {
            $user = Auth::user();
            $action = 0;
            if ($request->get('chat_messageable_type') == 'App\Models\ChatImageMessage') {
                $action = ActionEnum::PAY_IMAGE_18;
            }
            if ($request->get('chat_messageable_type') == 'App\Models\ChatVideoMessage') {
                $action = ActionEnum::PAY_VIDEO_18;
            }
            if ($chat->first_user_id !== $user->id && $chat->second_user_id !== $user->id) {
                return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
            }

            if ($chat->first_user_id == $user->id) {
                if ($chat->deleted_by_first_user) {
                    return response()->json(['error' => 'chat is deleted'], 404);
                }
            } else {
                if ($chat->deleted_by_second_user) {
                    return response()->json(['error' => 'chat is deleted'], 404);
                }
            }

            $credits = new CreditsController();
            $resultCheckPayment = $credits->check_payment($action, $action, $chat->second_user_id == $user->id ? $chat->first_user_id : $chat->second_user_id);
            if ($resultCheckPayment) {
                $message = ChatMessage::find($request->get('message_id'));
                if ($message) {
                    $message->is_payed = 1;
                    $message->save();
                }
                [$sender_user_id, $recepient_user_id] = $this->extracted($chat, $user->id);

                $operator = ChatRepository::findHowWorkAnket($recepient_user_id);
                $sender = User::find($sender_user_id);
                $recepient = User::find($recepient_user_id);
                if ($recepient->is_real == 0) {
                    UserPayedMessagesToOperators::create([
                        'user_id' => $sender->id,
                        'ancet_id' => $recepient->id,
                        'operator_id' => $operator,
                        'credits' => $this->getServicePrice($action),
                    ]);
                }
                return (self::get_current_chat_list_item($request->chat_id, $user));
            }
            return response()->json(['error' => 'payment hasnt been executed'], 500);
        }
        return response()->json(['error' => 'no chat with this ID'], 500);
    }
}
