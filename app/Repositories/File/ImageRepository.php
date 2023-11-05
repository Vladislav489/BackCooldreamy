<?php

namespace App\Repositories\File;

use App\Models\Image;
use App\Models\User;

class ImageRepository
{
    /**
     * @param $id
     * @return Image
     */
    public function find($id): Image
    {
        return Image::findorfail($id);
    }

    /**
     * @param User $user
     * @param null $category
     * @return int
     */
    public function getUserImagesCount(User $user, $category = null): int
    {
        $query = Image::where('user_id', $user->id);

        if ($category) {
            $query->where('category_id', $category);
        }

        return $query->count();
    }
}
