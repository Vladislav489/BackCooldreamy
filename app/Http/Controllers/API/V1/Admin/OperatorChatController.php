<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Events\LetChatMessageNewReadEvent;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatImageMessage;
use App\Models\OperatorChatLimit;
use App\Models\User;
use App\Repositories\File\ImageRepository;
use App\Repositories\File\VideoRepository;
use App\Repositories\Operator\ChatRepository;
use App\Repositories\Operator\GifRepository;
use App\Repositories\Operator\LetterRepository;
use App\Repositories\Operator\StickerRepository;
use App\Repositories\User\FavoriteRepository;
use App\Services\Operator\LimitService;
use App\Services\Rating\RatingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    }


    public function index(Request $request)
    {
        // TODO переписать + another_user иправить
        /** @var User $operator */
        $operator = Auth::user();

        /** @var Collection $ankets */
        $ankets = $operator->adminAncets()->with(['user', 'user.rating', 'user.rating.history'])->get();
        $anketChats = $this->chatRepository->index(['anket_ids' => $ankets->pluck('user_id'), 'per_page' => $request->get('per_page'), "search" => $request->get('search'), 'filter_type' => $request->get('filter_type')]);

//        $favoriteUsers = $this->favoriteRepository->getUserFavorite($operator);

        return $anketChats;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function storeChat(Request $request)
    {
        $anketId = $request->get('anket_id');
        $manId = $request->get('man_id');

        $anket = User::findOrFail($anketId);
        $man = User::findOrFail($manId);

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

        $operatorLimit = OperatorLimitController::getChatLimits($anket->id, $man->id);

        if (!$operatorLimit) {
            OperatorChatLimit::create([
                'man_id' => $manId,
                'girl_id' => $anket->id,
                'limits' => 0,
                'chat_id' => $chat->id
            ]);
        } else {
            $operatorLimit->chat_id = $chat->id;
            $operatorLimit->save();
        }

        return response()->json($chat);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Chat|JsonResponse $chat */
        $chat = $this->chatRepository->findForAdminAnket($user, $id, ['firstUser', 'secondUser']);

        if ($chat instanceof JsonResponse) {
            return $chat;
        }

        $chatMessages = $this->chatRepository->getChatMessages($chat);

        $chat->chat_messages = $chatMessages;
        $chat->chat_id = $chat->id;

        $chat->other_user->count_chats = $this->chatRepository->getUserChatsCount($chat->other_user);
        $chat->other_user->count_letters = $this->letterRepository->getUserLetterCount($chat->other_user);
        $chat->other_user->count_images = $this->imageRepository->getUserImagesCount($chat->other_user);

        $chat->self_user->count_images = $this->imageRepository->getUserImagesCount($chat->self_user);
        $chat->self_user->count_public = $this->imageRepository->getUserImagesCount($chat->self_user, 2);
        $chat->self_user->count_eighteen = $this->imageRepository->getUserImagesCount($chat->self_user, 4);
        $chat->self_user->count_videos = $this->videoRepository->getUserVideoCount($chat->self_user);

        return response()->json($chat);
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

        $chat = $this->chatRepository->findForAdminAnket($user, $id);

        if ($chat->deleted_by_first_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }

        if ($chat->deleted_by_second_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }

        $this->chatRepository->saveChatMessage($chat, $request->text);

//       TODO добавть
//        if (AceLog::where('chat_id', $chat->id)->exists()) {
//            if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
//                OperatorLimitController::removeLimits($chat->recepient_id, 1);
//            } else {
//                OperatorLimitController::removeLimits($chat->recepient_id, 2);
//            }
//        }

        return response()->json($this->chatRepository->getCurrentChatList($chat));
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function ignore($id): JsonResponse
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAdminAnket($user, $id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == $chat->user_id) {
            Chat::query()->where('id', $chat->id)->update(['is_ignored_by_first_user' => true]);
        } else {
            Chat::query()->where('id', $chat->id)->update(['is_ignored_by_second_user' => true]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function favorite($id)
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAdminAnket($user, $id);

        if (!$chat instanceof Chat) {
            return $chat;
        }

        if ($chat->first_user_id == $chat->user_id) {
            Chat::query()->where('id', $chat->id)->update(['is_anket_favorite_by_first_user' => !$chat->is_anket_favorite_by_first_user]);
        } else {
            Chat::query()->where('id', $chat->id)->update(['is_anket_favorite_by_second_user' => !$chat->is_anket_favorite_by_second_user]);
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
        $chat = $this->chatRepository->findForAdminAnket($user, $id);
        if (!$chat instanceof Chat) {
            return $chat;
        }

        return response()->json(
            ChatImageMessage::whereHas('chat_message', function ($query) use ($id) {
                $query->where('chat_id', $id);
            })->paginate(5)
        );
    }

    /**
     * @param $id
     * @param $message
     * @return JsonResponse
     */
    public function read($id, $message): JsonResponse
    {
        $user = Auth::user();
        $chat = $this->chatRepository->findForAdminAnket($user, $id);
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
        }

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

        $chat = $this->chatRepository->findForAdminAnket($user, $id);
        if ($chat->deleted_by_first_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }

        if ($chat->deleted_by_second_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }
        $sticker = $this->stickerRepository->find($sticker);

        $this->chatRepository->saveChatSticker($chat, $sticker);

//       TODO добавть
//        if (AceLog::where('chat_id', $chat->id)->exists()) {
//            if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
//                OperatorLimitController::removeLimits($chat->recepient_id, 1);
//            } else {
//                OperatorLimitController::removeLimits($chat->recepient_id, 2);
//            }
//        }

        return response()->json($this->chatRepository->getCurrentChatList($chat));
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

        $chat = $this->chatRepository->findForAdminAnket($user, $id);
        if ($chat->deleted_by_first_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }

        if ($chat->deleted_by_second_user) {
            return response()->json(['error' => 'User deleted the chat.'], 404);
        }
        $gift = $this->gifRepository->find($gift);

        $this->chatRepository->saveChatGift($chat, $gift);

//       TODO добавть
//        if (AceLog::where('chat_id', $chat->id)->exists()) {
//            if (ChatMessage::where('chat_id', $chat->id)->where('sender_user_id', $user->id)->count() <= 1) {
//                OperatorLimitController::removeLimits($chat->recepient_id, 1);
//            } else {
//                OperatorLimitController::removeLimits($chat->recepient_id, 2);
//            }
//        }

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
        $this->chatRepository->saveChatImage($chat, $request->get('image_url'), $request->get('thumbnail_url'), $request->get('category_id'));

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

        $chat = $this->chatRepository->findForAdminAnket($user, $id);

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

        return response()->json(['message' => 'success'], 200);
    }

}
