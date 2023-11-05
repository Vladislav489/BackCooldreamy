<?php

namespace App\Traits;

use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserSubscriptionTrait
{
    /**
     * Проверка оформления подписки
     * @param User $user
     * @return bool
     */
    public function checkUserExistsSubscription(User $user): bool
    {
        $timeNow = Carbon::now();

        return Subscriptions::where('user_id', $user->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->exists();
    }
}
