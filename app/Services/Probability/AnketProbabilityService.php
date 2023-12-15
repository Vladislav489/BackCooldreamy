<?php

namespace App\Services\Probability;

use App\Events\SympathyEvent;
use App\Jobs\AnketFavorite;
use App\Jobs\AnketLike;
use App\Jobs\AnketWatch;
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
           return;
       }

       $time = $this->getProbabilityTimes(self::FAVORITE);
       $rand = rand($time['from'], $time['to']);
       AnketFavorite::dispatch($user, $favoriteUser)->onQueue('default')->delay($rand);
       $this->log->info("[AnketProbabilityService::addToFavorite] Send User Favorite Event from user: {$user->id} to {$favoriteUser->id} by {$rand}");
    }

    /**
     * @param User $user
     * @param User $likeUser
     */
    public function like(User $user, User $likeUser)
    {
        if (!$this->getUserProbabilityByType($user, self::LIKE)) {
            $this->log->info("[AnketProbabilityService::like] Drooped Like Probability for user: {$user->id} to {$likeUser->id}");
            return;
        }

        $time = $this->getProbabilityTimes(self::LIKE);
        $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::like] Send User Like Event from user: {$user->id} to {$likeUser->id} by {$rand}");
        AnketLike::dispatch($user, $likeUser)->onQueue('default')->delay($rand);
    }

    /**
     * @param User $user
     * @param User $watchUser
     */
    public function watch(User $user, User $watchUser)
    {
        if (!$this->getUserProbabilityByType($user, self::WATCH)) {
            $this->log->info("[AnketProbabilityService::watch] Drooped Watch Probability for user: {$user->id} to {$watchUser->id}");
            return;
        }

        $time = $this->getProbabilityTimes(self::WATCH);
        $rand = rand($time['from'], $time['to']);
        $this->log->info("[AnketProbabilityService::watch] Send User Watch Event from user: {$user->id} to {$watchUser->id} by {$rand}");
        AnketWatch::dispatch($user, $watchUser)->onQueue('default')->delay($rand);
    }

    /**
     * @param $user
     * @param $type
     * @return bool
     */
    private function getUserProbabilityByType($user, $type): bool
    {
        $probability = $this->getProbabilities($user->profile_type_id, $type);

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
    private function getProbabilities($profileType, $probabilityType): float
    {
        if ($probabilityType == self::LIKE) {
            $probability = LikeProfileProbability::query()->where('profile_type_id', $profileType)->first();
        } else if ($probabilityType == self::FAVORITE) {
            $probability = FavoriteProfileProbability::query()->where('profile_type_id', $profileType)->first();
        } else if ($probabilityType == self::WATCH) {
            $probability = WatchProfileProbability::query()->where('profile_type_id', $profileType)->first();
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

        if ($probabilityKey <= $randomNumber) {
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
        } else {
            return ['from' => 0, 'to' => 0];
        }
    }
}
