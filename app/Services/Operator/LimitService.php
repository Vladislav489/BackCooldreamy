<?php

namespace App\Services\Operator;

use App\Models\Chat;
use App\Models\User;

class LimitService
{
    /**
     * @param Chat $chat
     * @param User $operator
     * @return bool
     */
    public function getIsNewChat(Chat $chat, User $operator): bool
    {
        return true;
    }
}
