<?php

namespace App\Repositories\Operator;

use App\Models\Gift;

class GifRepository
{
    public function find($id)
    {
        return Gift::findOrFail($id);
    }
}
