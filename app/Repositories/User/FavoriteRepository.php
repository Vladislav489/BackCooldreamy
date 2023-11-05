<?php

namespace App\Repositories\User;

use App\Models\FavoriteProfile;
use App\Models\User;
use Illuminate\Support\Collection;

class FavoriteRepository
{
    /**
     * @param User $user
     * @return Collection
     */
    public function getUserFavorite(User $user): Collection
    {
        return FavoriteProfile::where('user_id', $user->id)
            ->where('disabled', false)
            ->pluck('favorite_user_id');
    }

    /**
     * @param $userId
     * @return Collection
     */
    public function getUserFavoriteById($userId): Collection
    {
        return FavoriteProfile::where('user_id', $userId)
            ->where('disabled', false)
            ->pluck('favorite_user_id');
    }
}
