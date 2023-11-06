<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Auth\CreditLogTypeEnum;
use App\Events\OperatorsEventMessage;
use App\Events\LetterEvent;
use App\Events\AbstractLetterMessageReadEvent;
use App\Http\Controllers\Controller;
use App\Models\Auth\CreditLog;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Letter;
use App\Models\LetterMessage;
use App\Models\Operator\OperatorLetterLimit;
use App\Models\OperatorChatLimit;
use App\Models\User;
use App\Repositories\Operator\GifRepository;
use App\Repositories\Operator\LetterRepository;
use App\Repositories\Operator\OperatorRepository;
use App\Repositories\Operator\StickerRepository;
use App\Repositories\User\FavoriteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OperatorLetterController extends Controller
{
    /** @var LetterRepository */
    private LetterRepository $letterRepository;

    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var FavoriteRepository */
    private FavoriteRepository $favoriteRepository;

    /** @var StickerRepository */
    private StickerRepository $stickerRepository;

    /**
     * @var GifRepository
     */
    private GifRepository $gifRepository;

    public function __construct(
        LetterRepository $letterRepository,
        OperatorRepository $operatorRepository,
        FavoriteRepository $favoriteRepository,
        StickerRepository $stickerRepository,
        GifRepository $gifRepository,
    )
    {
        $this->letterRepository = $letterRepository;
        $this->operatorRepository = $operatorRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->stickerRepository = $stickerRepository;
        $this->gifRepository = $gifRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user Сам оператор */
        $user = Auth::user();

        $ankets = $user->ancets()->with(['user', 'user.rating', 'user.rating.history'])->get();

        /** @var LengthAwarePaginator $letterList */
        $letterList = $this->letterRepository->index(['anket_ids' => $ankets->pluck('user_id'), 'per_page' => $request->get('per_page'), "search" => $request->get('search'), 'letter_limit' => 1]);

        $letterList->getCollection()->each(function ($item) {
            $limit =  OperatorLetterLimitController::getLetterLimits($item->selfUser->id, $item->otherUser->id);
            $item->available_limit = $limit ? $limit->limits : 0;
            $item->max_limit = CreditLog::query()->where('credit_type', CreditLogTypeEnum::OUTCOME)->where('user_id', $item->otherUser->id)->where('other_user_id', $item->selfUser->id)->sum('credits');;
            return $item;
        });

        return response()->json($letterList);
    }

    /**
     * Тут получаем по id запись с последним сообщением
     * @param $letter
     * @return JsonResponse
     */
    public function show($letter): JsonResponse
    {
        /** @var User $user Сам оператор */
        $user = Auth::user();

        $letter = $this->getLetter($user, $letter);

        $limit =  OperatorLetterLimitController::getLetterLimits($letter->user_id, $letter->recepient_id);
        $letter->available_limit = $limit ? $limit->limits : 0;
        $letter->other_user->count_chats = ChatMessage::query()->where('sender_user_id', $letter->other_user->id)->where('recepient_user_id', $letter->self_user->id)->count();
        $letter->other_user->count_letters = LetterMessage::query()->where('letter_id', $letter->id)->where('sender_user_id', $letter->other_user->id)->count();
//        $letter->other_user->load('userGeo');
        //        $letter->other_user->count_images = $this->imageRepository->getUserImagesCount($letter->other_user);
        $credit = CreditLog::query()->where('user_id', $letter->other_user->id)->where('other_user_id', $letter->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
        $letter->max_limit = $credit;
        $letter->other_user->credits = $credit;
        return response()->json($letter);
    }


    public function storeLetter(Request $request)
    {
        $anketId = $request->get('anket_id');
        $manId = $request->get('man_id');
        $operatorChatLimit = $request->get('operator_chat_limit_id');

        $anket = User::findOrFail($anketId);
        $man = User::findOrFail($manId);

        $operatorLetterLimit = OperatorLetterLimit::query()->where('man_id', $manId)->where('girl_id', $anket->id)->findOrFail($operatorChatLimit);

        if ($letter = Letter::query()->where([
            'first_user_id' => $anket->id,
            'second_user_id' => $man->id,
        ])->orWhere([
            'first_user_id' => $anket->id,
            'second_user_id' => $man->id,
        ])->first()) {
            return $letter;
        }

        $letter = Letter::create([
            'first_user_id' => $anket->id,
            'second_user_id' => $man->id,
            'is_ignored_by_first_user' => false,
            'is_ignored_by_second_user' => false,
            'disabled' => false,
            'deleted_by_first_user' => false,
            'deleted_by_second_user' => false,
            'uuid' => Str::uuid()
        ]);

        $operatorLetterLimit->letter_id = $letter->id;
        $operatorLetterLimit->save();

        return response()->json($letter);
    }

    /**
     * Получение письма
     *
     * @param $user
     * @param $letterId
     * @return Letter
     */
    private function getLetter($user, $letterId): Letter
    {
        $letter = $this->letterRepository->findForAnket($user, $letterId);
        $letter->load(['firstUser', 'secondUser']);
        $lastLetter = $this->letterRepository->getLettersForAnket($letter);
        $letter->setRelation('letter_messages', $lastLetter);

//        $letter = $this->operatorRepository->getAnotherUser($letter, $user);

        return $letter;
    }

    /**
     * @param $user
     * @param $letterId
     * @return Letter
     */
    public function getLetterWithLastMessage($user, $letterId): Letter
    {
        $letter = $this->letterRepository->findForAnket($user, $letterId);

        $lastLetter = $this->letterRepository->getLettersLastForAnket($letter);
        $letter->setRelation('last_message', $lastLetter);

//        $letter = $this->operatorRepository->getAnotherUser($letter, $user);

        return $letter;
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function sendMessage(Request $request, $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Правила валидации
        $validator = Validator::make($request->all(), [
            'text' => 'max:1800',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        /** @var Letter $letter */
        $letter = $this->letterRepository->findForAnket($user, $id);

        if (!$limit = OperatorLetterLimitController::spendLimitsByOperator($letter->user_id, $letter->recepient_id, $letter->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }

        $message = $this->letterRepository->createTextMessage($letter, [
            'images' => $request->input('images'),
            'text' => $request->get('text')
        ]);

        // TODO
        $this->sendEvent($letter, $message, $user, $id);
        $this->updateLetter($letter);
        $letter->load(['firstUser', 'secondUser']);
        $letter->setRelation('last_message', $message);
        $letter->available_limit = $limit ? $limit->limits : 0;
        $credit = CreditLog::query()->where('user_id', $letter->other_user->id)->where('other_user_id', $letter->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
        $letter->max_limit = $credit;
        return response()->json($letter);
    }

    /**
     * @param Chat $letter
     */
    private function updateLetter(Letter $letter)
    {
        $letter->load('firstUser', 'secondUser');
        if ($letter->selfUser && $letter->selfUser->operator) {
            $operator = $letter->selfUser->operator;
            if (!$letter->is_answered_by_operator) {
                OperatorsEventMessage::dispatch($operator->operator_id, $letter->id, 'letter');
            }
        }
        Letter::where('id', $letter->id)->update(['updated_at' => now(), 'is_answered_by_operator' => true]);
    }

    /**
     * Именно тут мы отправляем сообщение по вебсокету другому пользователю чтобы у него обновился чат!
     *
     * @param $letter
     * @param $message
     * @param $user
     * @param $id
     */
    private function sendEvent($letter, $message, $user, $id)
    {
        //LetterEvent::dispatch($letter->recepient_id, $message, $letter);
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

        $letter = $this->letterRepository->findForAnket($user, $id);
        if (!$limit = OperatorLetterLimitController::spendLimitsByOperator($letter->user_id, $letter->recepient_id, $letter->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $sticker = $this->stickerRepository->find($sticker);

        $message = $this->letterRepository->createStickerMessage($letter, $sticker);

        $this->sendEvent($letter, $message, $user, $id);
        $letter->load(['firstUser', 'secondUser']);

        $letter->setRelation('last_message', $message);
        $letter->available_limit = $limit ? $limit->limits : 0;
        $credit = CreditLog::query()->where('user_id', $letter->other_user->id)->where('other_user_id', $letter->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
        $letter->max_limit = $credit;
        return response()->json($letter);
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

        $letter = $this->letterRepository->findForAnket($user, $id);
        if (!$limit = OperatorLetterLimitController::spendLimitsByOperator($letter->user_id, $letter->recepient_id, $letter->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $gift = $this->gifRepository->find($gift);

        $message = $this->letterRepository->createGiftMessage($letter, $gift);

        $this->sendEvent($letter, $message, $user, $id);
        $letter->load(['firstUser', 'secondUser']);

        $letter->setRelation('last_message', $message);
        $letter->available_limit = $limit ? $limit->limits : 0;
        $credit = CreditLog::query()->where('user_id', $letter->other_user->id)->where('other_user_id', $letter->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
        $letter->max_limit = $credit;
        return response()->json($letter);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function sendImage(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'thumbnail_url' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $letter = $this->letterRepository->findForAnket($user, $id);
        if (!$limit = OperatorLetterLimitController::spendLimitsByOperator($letter->user_id, $letter->recepient_id, $letter->id)) {
            return response()->json(['error' => 'NO_LIMIT'], 500);
        }
        $message = $this->letterRepository->saveLetterImage($letter, $request->get('image_url'), $request->get('thumbnail_url'));
        $letter->load(['firstUser', 'secondUser']);

        $letter->setRelation('last_message', $message);
        $letter->available_limit = $limit ? $limit->limits : 0;
        $credit = CreditLog::query()->where('user_id', $letter->other_user->id)->where('other_user_id', $letter->self_user->id)->where('credit_type', CreditLogTypeEnum::OUTCOME)->sum('credits');
        $letter->max_limit = $credit;
        return response()->json($letter);
    }

    /**
     * @param $id
     * @param $letterMessageId
     * @return JsonResponse
     */
    public function read($id, $letterMessageId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $letter = $this->letterRepository->findForAnket($user, $id);

        $letterMessage = $this->letterRepository->findMessage($letter, $letterMessageId);

        $this->letterRepository->readMessage($letterMessage);

        //AbstractLetterMessageReadEvent::dispatch($letterMessage->sender_user_id, $letterMessage->letter_id, $letterMessage->id);

        return response()->json(['message' => 'success'], 200);
    }
}

