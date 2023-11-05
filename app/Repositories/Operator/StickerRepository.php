<?php

namespace App\Repositories\Operator;

use App\Models\Sticker;

class StickerRepository
{
    /**
     * @param $id
     * @return Sticker
     */
    public function find($id): Sticker
    {
        return Sticker::findOrFail($id);
    }
}
