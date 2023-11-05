<?php

namespace App\Services\Premium;

use App\Enum\Payment\PaymentStatusEnum;
use App\Enum\Premium\PremiumStatusEnum;
use App\ModelAdmin\CoreEngine\LogicModels\Helper\HelperLogic;
use App\Models\Subscriptions;
use App\Models\User;
use App\Models\User\Premuim;
use App\Repositories\Auth\CreditLogRepository;
use Carbon\Carbon;

class PremuimService
{

    /**
     * Проверка оформления подписки
     * @param User $user
     * @return Premuim|null
     */
    public static function getUserCurrentSubscription(User $user): ?Premuim
    {
        $timeNow = Carbon::now();
        $premuim =  Premuim::where('user_id', $user->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->first();
        if(!is_null($premuim)) {
            $premuim->timeToEnd = HelperLogic::getDateDiffPeriod($premuim->period_end, date("Y-m-d H:i:s"));
        }
        return $premuim;
    }

    /**
     * @param User $user
     * @param $subscriptionList
     * @param $status
     * @return Premuim
     */
    public function subscribe(User $user, $subscriptionList, $status): Premuim
    {
        $price = $subscriptionList->price;
        $result = $user->check_payment($price);
        if (!$result) {
            abort(403);
        }
        //$user->credits = $user->credits - $price;
        //$user->save();
        return Premuim::create([
            'user_id' => $user->id,
            'service_id' => $subscriptionList->id,
            'period_start' => \Illuminate\Support\Carbon::now(),
            'period_end' => Carbon::now()->addWeeks($subscriptionList->duration),
            'status' => $status
        ]);
    }
}
