<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Events\LetterEvent;
use App\Events\AbstractLetterMessageReadEvent;
use App\Http\Controllers\API\V1\OperatorLetterLimitController;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Letter;
use App\Models\Operator\OperatorLetterLimit;
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

        $ankets = $user->adminAncets()->with(['user', 'user.rating', 'user.rating.history'])->get();

        /** @var LengthAwarePaginator $letterList */
        $letterList = $this->letterRepository->index(['anket_ids' => $ankets->pluck('user_id'), 'per_page' => $request->get('per_page'), "search" => $request->get('search'), 'letter_limit' => 1]);

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

        return response()->json($letter);
    }

    public function storeLetter(Request $request)
    {
        $anketId = $request->get('anket_id');
        $manId = $request->get('man_id');

        $anket = User::findOrFail($anketId);
        $man = User::findOrFail($manId);

        $chat = Letter::create([
            'first_user_id' => $anket->id,
            'second_user_id' => $man->id,
            'is_ignored_by_first_user' => false,
            'is_ignored_by_second_user' => false,
            'disabled' => false,
            'deleted_by_first_user' => false,
            'deleted_by_second_user' => false,
            'uuid' => Str::uuid()
        ]);

        $operatorLimit = OperatorLetterLimitController::getLetterLimits($anket->id, $man->id);

        if (!$operatorLimit) {
            OperatorLetterLimit::create([
                'man_id' => $manId,
                'girl_id' => $anket->id,
                'limits' => 0,
                'letter_id' => $chat->id
            ]);
        } else {
            $operatorLimit->chat_id = $chat->id;
            $operatorLimit->save();
        }

        return response()->json($chat);
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
        $letter = $this->letterRepository->findForAdminAnket($user, $letterId);

        $lastLetter = $this->letterRepository->getLettersForAnket($letter);
        $letter->setRelation('letter_messages', $lastLetter);

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
        $letter = $this->letterRepository->findForAdminAnket($user, $id);

        $message = $this->letterRepository->createTextMessage($letter, [
            'images' => json_decode($request->input('images')),
            'text' => $request->get('text')
        ]);

        // TODO
        $this->sendEvent($letter, $message, $user, $id);
        $this->updateLetter($letter);

        // Возвращаем последнее сообщение TODO переделать
        return response()->json($this->getLetter($user, $id));
    }

    /**
     * @param Chat $chat
     */
    private function updateLetter(Letter $letter)
    {
        Letter::where('id', $letter->id)->update(['updated_at' => now()]);
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
        $letterListItem = $this->getLetter($user, $id);
        LetterEvent::dispatch($letter->recepient_id, $message, $letterListItem);
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

        $letter = $this->letterRepository->findForAdminAnket($user, $id);

        $sticker = $this->stickerRepository->find($sticker);

        $message = $this->letterRepository->createStickerMessage($letter, $sticker);

        $this->sendEvent($letter, $message, $user, $id);

        return response()->json($this->getLetter($user, $id));
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

        $letter = $this->letterRepository->findForAdminAnket($user, $id);

        $gift = $this->gifRepository->find($gift);

        $message = $this->letterRepository->createGiftMessage($letter, $gift);

        $this->sendEvent($letter, $message, $user, $id);

        return response()->json($this->getLetter($user, $id));
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

        $letter = $this->letterRepository->findForAdminAnket($user, $id);
        $this->letterRepository->saveLetterImage($letter, $request->get('image_url'), $request->get('thumbnail_url'));

        return response()->json($this->getLetter($user, $id));
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

        $letter = $this->letterRepository->findForAdminAnket($user, $id);

        $letterMessage = $this->letterRepository->findMessage($letter, $letterMessageId);

        $this->letterRepository->readMessage($letterMessage);

        AbstractLetterMessageReadEvent::dispatch($letterMessage->sender_user_id, $letterMessage->letter_id, $letterMessage->id);

        return response()->json(['message' => 'success'], 200);
    }
}
