<?php

namespace App\Repositories\Auth;

use App\Enum\AbstractEnum;
use App\Enum\Auth\CreditLogTypeEnum;
use App\Models\Auth\CreditLog;
use App\Models\User;

class CreditLogRepository
{
    const SUBSCRIPTION = 10001;

    /**
     * @param User $user
     * @param $price
     * @param bool $isReal
     * @param null $otherUserId
     * @param string $actionType
     * @return CreditLog
     */
    public function logPayment(User $user, $price, $otherUserId = null, $actionType = 0): CreditLog
    {
        $isReal = $user->is_donate ? true : false;

        return CreditLog::create([
            'user_id' => $user->id,
            'credits' => $price,
            'real_credits' => $isReal,
            'credit_type' => CreditLogTypeEnum::OUTCOME,
            'other_user_id' => $otherUserId,
            'action_type' => $actionType
        ]);
    }

    /**
     * @param User $user
     * @param $price
     * @return CreditLog
     */
    public function logIncome(User $user, $price): CreditLog
    {
        return CreditLog::create([
            'user_id' => $user->id,
            'credits' => $price,
            'real_credits' => true,
            'credit_type' => CreditLogTypeEnum::INCOME
        ]);
    }
}
