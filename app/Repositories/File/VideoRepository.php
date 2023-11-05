<?php

namespace App\Repositories\File;

use App\Models\User;
use App\Models\Video;

class VideoRepository
{
    /**
     * @param User $user
     * @param null $category
     * @return int
     */
    public function getUserVideoCount(User $user, $category = null): int
    {
        $query = Video::where('user_id', $user->id);

        if ($category) {
            $query->where('category_id', $category);
        }

        return $query->count();
    }
}
