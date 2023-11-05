<?php

namespace App\Repositories\Auth;

use App\Models\Auth\AuthLog;

class AuthLogRepository
{
    /**
     * @param $user
     * @param $type
     * @return AuthLog
     */
    public function logAuth($user, $type): AuthLog
    {
        return AuthLog::create([
            'user_id' => $user->id,
            'log_type' => $type
        ]);
    }
}
