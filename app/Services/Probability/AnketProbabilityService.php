<?php

namespace App\Services\Probability;

use App\Events\SympathyEvent;
use App\Http\Controllers\AceController;
use App\Http\Controllers\API\V1\ChatController;
use App\Jobs\AnketFavorite;
use App\Jobs\AnketLike;
use App\Jobs\AnketWatch;
use App\Jobs\SendAce;
use App\ModelAdmin\CoreEngine\LogicModels\Ace\AceLogic;
use App\Models\AceSendingProbability;
use App\Models\Chat;
use App\Models\FavoriteProfileProbability;
use App\Models\LikeProfileProbability;
use App\Models\Setting;
use App\Models\User;
use App\Models\WatchProfileProbability;
use Illuminate\Support\Facades\Log;

class AnketProbabilityService
{
    // Меня лайкают
    const LIKE = 'LIKE';

    // Я лайкаю
    const MY_LIKE = 'MY_LIKE';

    // Общие
    const MUTUAL = 'MUTUAL';

    const FAVORITE = 'FAVORITE';

    // Просмотры
    const WATCH = 'WATCH';

    // Отправка айса
    const ACE = 'ACE';

    private $log;

    public function __construct()
    {
        $this->log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('/logs/probability/probability.log')
        ]);
    }

    /**
     * @param User $user
     * @param User $favoriteUser
     */
    public function addToFavorite(User $user, User $favoriteUser)
    {
       if (!$this->getUserProbabilityByType($user, self::FAVORITE)) {
           $this->log->info("[AnketProbabilityService::addToFavorite] Drooped Favorite Probability for user: {$user->id} to {$favoriteUser->id}");
           return false;
       }

       $time = $this->getProbabilityTimes(self::FAVORITE);
       $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::addToFavorite] Send User Favorite Event from user: {$user->id} to {$favoriteUser->id} by {$rand}");
        AnketFavorite::dispatch($user, $favoriteUser)->onQueue('default')->delay($rand);
        return true;
    }

    /**
     * @param User $user
     * @param User $likeUser
     */
    public function like(User $user, User $likeUser)
    {
        if (!$this->getUserProbabilityByType($user, self::LIKE)) {
            $this->log->info("[AnketProbabilityService::like] Drooped Like Probability for user: {$user->id} to {$likeUser->id}");
            return false;
        }

        $time = $this->getProbabilityTimes(self::LIKE);
        $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::like] Send User Like Event from user: {$user->id} to {$likeUser->id} by {$rand}");
        AnketLike::dispatch($user, $likeUser)->onQueue('default')->delay(120);
        return true;
    }

    /**
     * @param User $user
     * @param User $watchUser
     */
    public function watch(User $user, User $watchUser)
    {
        if (!$this->getUserProbabilityByType($user, self::WATCH)) {
            $this->log->info("[AnketProbabilityService::watch] Drooped Watch Probability for user: {$user->id} to {$watchUser->id}");
            return false;
        }

        $time = $this->getProbabilityTimes(self::WATCH);
        $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::watch] Send User Watch Event from user: {$user->id} to {$watchUser->id} by {$rand}");
        AnketWatch::dispatch($user, $watchUser)->onQueue('default')->delay($rand);
        return true;
    }

    public function sendAce(User $user, User $girl)
    {
        if (!$this->getUserProbabilityByType($user, self::ACE)) {
            $this->log->info("[AnketProbabilityService::watch] Drooped Watch Probability for user: {$girl->id} to {$user->id}");
            return false;
        }

        $ace = AceController::getAce($user, $girl);
        $time = $this->getProbabilityTimes(self::ACE);
        $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::sendAce] Send User Ace Event from user: {girl->id} to {$user->id} by {$rand}");
        SendAce::dispatch($user, $girl, $ace)->onQueue('default')->delay($rand);

        return true;
    }

    /**
     * @param $user
     * @param $type
     * @return bool
     */
    private function getUserProbabilityByType($user, $type): bool
    {
        $userGroup = $this->getUserGroup($user);
        $probability = $this->getProbabilities($user->profile_type_id, $type, $userGroup);

        if (!$probability) {
            return false;
        }

        return $this->checkProbability($probability);
    }

    /**
     * @param $profileType
     * @param $probabilityType
     * @return float
     */
    private function getProbabilities($profileType, $probabilityType, $userGroup): float
    {
        if ($probabilityType == self::LIKE) {
            $probability = LikeProfileProbability::query()->where([['profile_type_id', $profileType], ['user_group', $userGroup]])->first();
        } else if ($probabilityType == self::FAVORITE) {
            $probability = FavoriteProfileProbability::where([['profile_type_id', $profileType], ['user_group', $userGroup]])->first();
        } else if ($probabilityType == self::WATCH) {
            $probability = WatchProfileProbability::query()->where([['profile_type_id', $profileType], ['user_group', $userGroup]])->first();
        } else if ($probabilityType == self::ACE) {
            $probability = AceSendingProbability::query()->where([['profile_type_id', $profileType], ['user_group', $userGroup]])->first();
        } else {
            return 0;
        }

        if (!$probability) {
            return 0;
        }
        return $probability->probability;
    }

    /**
     * @param $probability
     * @return bool
     */
    private function checkProbability($probability): bool
    {
        $randomNumber = mt_rand(1, 100);

        $probabilityKey = $probability * 100;

        if ($probabilityKey >= $randomNumber) {
            return true;
        }
        $this->log->info("[AnketProbabilityService::watch] Random number: {$randomNumber} to {$probabilityKey}");

        return false;
    }

    private function getProbabilityTimes($type)
    {
        if ($type == self::FAVORITE) {
            $settingFrom = Setting::query()->where('name', 'favorite_from_time')->first();
            $settingTo = Setting::query()->where('name', 'favorite_to_time')->first();
            return ['from' => $settingFrom->value ?? 10, 'to' => $settingTo->value ?? 120];
        } else if ($type == self::LIKE) {
            $settingFrom = Setting::query()->where('name', 'like_from_time')->first();
            $settingTo = Setting::query()->where('name', 'likes_to_time')->first();
            return ['from' => $settingFrom->value ?? 10, 'to' => $settingTo->value ?? 120];
        } else if ($type == self::WATCH) {
            $settingFrom = Setting::query()->where('name', 'watch_from_time')->first();
            $settingTo = Setting::query()->where('name', 'watch_to_time')->first();
            return ['from' => $settingFrom->value ?? 10, 'to' => $settingTo->value ?? 120];
        } else if ($type == self::ACE) {
            $settingFrom = Setting::query()->where('name', 'ace_from_time')->first();
            $settingTo = Setting::query()->where('name', 'ace_to_time')->first();
            return ['from' => $settingFrom->value ?? 10, 'to' => $settingTo->value ?? 120];
        } else {
            return ['from' => 0, 'to' => 0];
        }
    }

    private function getUserGroup(User $user)
    {
        /** 1 группа - 0-100 лайков, 2 группа - 101-150 лайков, 3 группа 151-200 лайков, 4 группа - 201-1000000
         */
        $count_likes = $user->feeds_users->count();
        if ($count_likes <= 100) {
            return 1;
        } elseif ($count_likes <= 150) {
            return 2;
        } elseif (($count_likes <= 200)) {
            return 3;
        } else return 4;
    }


}
