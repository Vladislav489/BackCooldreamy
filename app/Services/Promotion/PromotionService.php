<?php

namespace App\Services\Promotion;

use App\Models\Promotion;
use App\Models\User;
use App\Models\User\Premuim;
use App\Models\UserPromotion;
use Carbon\Carbon;

class PromotionService
{
    /**
     * @param User $user
     * @param Promotion $promotion
     * @param $status
     * @return mixed
     */
    public function subscribe(User $user, Promotion $promotion, $status)
    {
        $promotion = UserPromotion::where('user_id', $user->id)->where('promotion_id', $promotion->id)->firstOrFail();
//        $promotion->status = $status;
//        $promotion->save();

        return $promotion;
    }
}
