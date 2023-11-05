<?php

namespace App\Repositories\Operator;

use App\Http\Controllers\API\V1\OperatorLetterController;
use App\Models\Chat;
use App\Models\ChatTextMessage;
use App\Models\Gift;
use App\Models\Image;
use App\Models\Letter;
use App\Models\LetterGiftMessage;
use App\Models\LetterImageMessage;
use App\Models\LetterMessage;
use App\Models\LetterStickerMessage;
use App\Models\LetterTextMessage;
use App\Models\Sticker;
use App\Models\User;
use App\Repositories\File\ImageRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @see OperatorLetterController
 * Сущность письма такая же как и чат
 */
class LetterRepository
{
    const PER_PAGE = 15;

    const MESSAGE_PER_PAGE = 10;

    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var ImageRepository */
    private ImageRepository $imageRepository;

    public function __construct(
        OperatorRepository $operatorRepository,
        ImageRepository $imageRepository
    ) {
        $this->operatorRepository = $operatorRepository;
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param array $requestData
     * @return LengthAwarePaginator|Builder
     */
    public function index(array $requestData = []): LengthAwarePaginator|Builder
    {
        $query = Letter::query()->with(['lastMessage', 'firstUser', 'secondUser']);

        if ($anketIds = Arr::get($requestData,'anket_ids')) {
            $query->where(function ($query) use ($anketIds) {
                $query->where(function (Builder $builder) use ($anketIds) {
                    $builder->whereIn('first_user_id', $anketIds)
                        ->where('deleted_by_first_user', false);
                })->orWhere(function (Builder $builder) use ($anketIds) {
                    $builder->whereIn('second_user_id', $anketIds)
                        ->where('deleted_by_second_user', false);
                });
            });
        }

        if ($search = Arr::get($requestData, 'search')) {
            $query->where(function (Builder $query) use ($search) {
                $query->whereHas('firstUser', function (Builder $builder) use ($search) {
                    $builder->where('is_real', true);
                    $builder->where('id', 'like', "%$search%");
                });
                $query->orWhereHas('secondUser', function (Builder $builder) use ($search) {
                    $builder->where('is_real', true);
                    $builder->where('id', 'like', "%$search%");
                });
            });
        }

        if (Arr::get($requestData, 'letter_limit')) {
            $query->whereHas('limit', function ($query) {
                $query->where('limits', '>=', 1);
            });
        }

        if ($searchMessage = Arr::get($requestData, 'search_message')) {
            $query->whereHas('letter_messages', function ($builder) use ($searchMessage) {
                $builder->whereHasMorph('letter_messageable', [LetterTextMessage::class], function ($q) use ($searchMessage) {
                    $q->where('text', 'LIKE', '%' . $searchMessage . '%');
                });
            });
        }

        $query = $query->orderByDesc('updated_at');

        if (Arr::get($requestData, 'is_query')) {
            return $query;
        }

        return $query->paginate(Arr::get($requestData, 'per_page'));
    }

    /**
     * Для статистики высчитываем количество писем
     *
     * @param array $requestData
     * @return int
     */
    public function getCountMessage(array $requestData = []): int
    {
        $query = LetterMessage::query()->where(function($query) {
            $query->whereHas('sender_user', function ($query) {
                $query->where('is_real', false);
            })->orWhereHas('recepient_user', function ($query) {
                $query->where('is_real', false);
            });
        });


        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            $query->where('created_at', '<=', Carbon::now()->subMonths($lastMonth));
        }

        return $query->count();
    }

    /**
     * Выводим письмо по анкетам пользователя
     *
     * @param User $user
     * @param string $id
     * @return Letter
     */
    public function findForAnket(User $user, string $id): Letter
    {
        // Берем анкеты пользователей
        $userIds = $user->ancets()->pluck('user_id');

        $letter = Letter::query()->where(function (Builder $builder) use ($userIds) {
            $builder->whereIn('first_user_id', $userIds)->orWhereIn('second_user_id', $userIds);
        })->where('id', $id)->firstOrFail();

        // Указываем пользователя анкеты и пользователя, получателя сообщения(для писем), чтобы не запутаться
        // сама анкета
        $letter->user_id = $this->operatorRepository->getOperatorByAncets($letter, $userIds->toArray());
        // получатель, т.е. другой пользователь
        $letter->recepient_id = $this->operatorRepository->getRecepientUserByAncets($letter, $userIds->toArray());

        return $letter;
    }

    /**
     * @param User $user
     * @param string $id
     * @return Letter
     */
    public function findForAdminAnket(User $user, string $id): Letter
    {
        // Берем анкеты пользователей
        $userIds = $user->adminAncets()->pluck('user_id');

        $letter = Letter::query()->where(function (Builder $builder) use ($userIds) {
            $builder->whereIn('first_user_id', $userIds)->orWhereIn('second_user_id', $userIds);
        })->where('id', $id)->firstOrFail();

        // Указываем пользователя анкеты и пользователя, получателя сообщения(для писем), чтобы не запутаться
        // сама анкета
        $letter->user_id = $this->operatorRepository->getOperatorByAncets($letter, $userIds->toArray());
        // получатель, т.е. другой пользователь
        $letter->recepient_id = $this->operatorRepository->getRecepientUserByAncets($letter, $userIds->toArray());

        return $letter;
    }

    public function saveLetterImage(Letter $letter, $imageUrl, $thumbnailUrl): LetterMessage
    {
        // TODO
        $LetterImageMessage = LetterImageMessage::create([
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
        ]);

        return $this->saveMessage($letter, $LetterImageMessage);
    }

    /**
     * Последнее сообщение письма
     *
     * @param $letterId
     * @return LetterMessage|null
     */
    public function getLastLetterForAnket($letterId): ?LetterMessage
    {
        return LetterMessage::with('letter_messageable.gifts', 'letter_messageable.sticker')
            ->where('letter_id', $letterId)
            ->latest()
            ->first();
    }

    /**
     * @param Letter $letter
     * @return LengthAwarePaginator
     */
    public function getLettersForAnket(Letter $letter): LengthAwarePaginator
    {
        return $letter->letter_messages()
            ->with(['sender_user' => function ($query) {
                $query->select('id', 'name', 'avatar_url_thumbnail', 'birthday', 'avatar_url');
            }, 'letter_messageable.sticker', 'letter_messageable.gifts', 'letter_messageable.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(self::MESSAGE_PER_PAGE);
    }

    /**
     * @param Letter $letter
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getLettersLastForAnket(Letter $letter)
    {
        return $letter->letter_messages()->with(['sender_user' => function ($query) {
            $query->select('id', 'name', 'avatar_url_thumbnail', 'birthday');
        }, 'letter_messageable.sticker', 'letter_messageable.gifts', 'letter_messageable.images'])
            ->latest()->first();
    }

    /**
     * Создание сообщения для письма
     *
     * @param Letter $letter
     * @param array $requestData
     */
    public function createTextMessage(Letter $letter, array $requestData = [])
    {
        $letterTextMessage = $this->storeTextMessage(Arr::get($requestData, 'text'));

        $this->setLetterTextImages($letterTextMessage, Arr::get($requestData, 'images', []) ?? []);

        $letterMessage = $this->storeMessage($letter);

        $this->syncLetterMessage($letterTextMessage, $letterMessage);

        return $letterMessage;
    }

    /**
     * Создание стикера для письма
     *
     * @param Letter $letter
     * @param Sticker $sticker
     */
    public function createStickerMessage(Letter $letter, Sticker $sticker)
    {
        $letterStickerMessage = $this->storeStickerMessage($sticker);

        $letterMessage = $this->storeMessage($letter);

        $this->syncLetterMessage($letterStickerMessage, $letterMessage);

        return $letterMessage;
    }

    /**
     * @param Letter $letter
     * @param Gift $gift
     */
    public function createGiftMessage(Letter $letter, Gift $gift)
    {
        $letterGiftMessage = $this->storeGiftMessage($gift);

        $letterMessage = $this->storeMessage($letter);

        $this->syncLetterMessage($letterGiftMessage, $letterMessage);

        return $letterMessage;
    }

    /**
     * Создаем сообщение текстовое
     *
     * @param $text
     * @return LetterTextMessage
     */
    public function storeTextMessage($text): LetterTextMessage
    {
        return LetterTextMessage::create(['text' => $text]);
    }

    /**
     * @param Sticker $sticker
     * @return LetterStickerMessage
     */
    public function storeStickerMessage(Sticker $sticker): LetterStickerMessage
    {
        return LetterStickerMessage::create([
            'sticker_id' => $sticker->id,
        ]);
    }

    /**
     * @param Gift $gift
     * @return LetterGiftMessage
     */
    public function storeGiftMessage(Gift $gift): LetterGiftMessage
    {
        $letterGiftMessage = LetterGiftMessage::create();

        $letterGiftMessage->gift()->attach($gift->id);

        return $letterGiftMessage;
    }

    /**
     * Указываем картинки для тестового сообщения письма
     *
     * @param LetterTextMessage $letterTextMessage
     * @param array $images
     */
    public function setLetterTextImages(LetterTextMessage $letterTextMessage, array $images = [])
    {
        foreach ($images as $image) {
            $image = $this->imageRepository->find($image);

            $letterTextMessage->images()->attach($image);
        }
    }

    /**
     * Создаем само сообщение
     *
     * @param Letter $letter
     * @return LetterMessage
     */
    public function storeMessage(Letter $letter): LetterMessage
    {
        return new LetterMessage([
            'letter_id' => $letter->id,
            /** user_id, recepient - @see LetterRepository::findForAnket() */
            'sender_user_id' => $letter->user_id,
            'recepient_user_id' => $letter->recepient_id,
        ]);
    }

    /**
     * @param $entity
     * @param LetterMessage $letterMessage
     * @return mixed
     */
    public function syncLetterMessage($entity, LetterMessage $letterMessage): mixed
    {
        $entity->letter_message()->save($letterMessage);

        $letterMessage->letter_messageable = $letterMessage->letter_messageable;

        return $letterMessage;
    }

    /**
     * @param Letter $letter
     * @param $id
     * @return LetterMessage
     */
    public function findMessage(Letter $letter, $id): LetterMessage
    {
        return LetterMessage::query()
            ->where('letter_id', $letter->id)
            ->where('id', $id)
            ->where('is_read_by_recepient', false)
            ->firstorfail();
    }

    /**
     * @param LetterMessage $letterMessage
     * @return LetterMessage
     */
    public function readMessage(LetterMessage $letterMessage): LetterMessage
    {
        $letterMessage->is_read_by_recepient = true;
        $letterMessage->save();

        return $letterMessage;
    }

    public function getUserLetterCount(User $user)
    {
        return Letter::where(function ($builder) use ($user) {
            $builder->where('first_user_id', $user->id)
                ->where('deleted_by_first_user', false);
        })->orWhere(function ($builder) use ($user) {
            $builder->where('second_user_id', $user->id)
                ->where('deleted_by_second_user', false);
        })->count();
    }
}
