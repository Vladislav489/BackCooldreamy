<?php

namespace App\Services\Subscription;

use App\Enum\Payment\PaymentStatusEnum;
use App\Enum\Subscription\SubscriptionStatusEnum;
use App\ModelAdmin\CoreEngine\LogicModels\Helper\HelperLogic;
use App\Models\Subscriptions;
use App\Models\User;
use App\Repositories\Auth\CreditLogRepository;
use App\Repositories\Subscription\SubscriptionListRepository;
use App\Repositories\Subscription\SubscriptionRepository;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    const NEW_CONTACT = 1;

    const PHOTO = 2;

    const VIDEO = 3;

    /** @var SubscriptionRepository */
    private SubscriptionRepository $subscriptionRepository;

    /** @var SubscriptionListRepository */
    private SubscriptionListRepository $subscriptionListRepository;

    /** @var CreditLogRepository */
    private CreditLogRepository $creditLogRepository;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        SubscriptionListRepository $subscriptionListRepository,
        CreditLogRepository $creditLogRepository,
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionListRepository = $subscriptionListRepository;
        $this->creditLogRepository = $creditLogRepository;
    }

    /**
     * Проверка оформления подписки
     * @param User $user
     * @return Subscriptions|null
     */
    public function getUserCurrentSubscription(User $user): ?Subscriptions {
        $timeNow = Carbon::now();
        $subscription = Subscriptions::where('user_id','=', $user->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->first();
        if(!is_null($subscription)) {
            $subscription->timeToEnd = HelperLogic::getDateDiffPeriod($subscription->period_end, date("Y-m-d H:i:s"));
        }
        return $subscription;
    }




    /**
     * TODO
     * @return false
     */
    public function checkSubscription()
    {
        return false;
    }

    /**
     * @param User $user
     * @param $subscriptionList
     * @param $status
     * @return Subscriptions
     */
    public function subscribe(User $user, $subscriptionList, $status): Subscriptions
    {

        if (!$user->check_payment($subscriptionList->price)) {
            abort(403);
        }
        return $this->subscriptionRepository->store($user, $subscriptionList->id, [
            'status' => $status,
            'period_start' => Carbon::now(),
            'period_end' => Carbon::now()->addMinutes($subscriptionList->duration),
            'one_time' => $subscriptionList->one_time,
            'count_letters' => $subscriptionList->count_letters,
            'count_watch_or_send_photos' => $subscriptionList->count_watch_or_send_photos,
            'count_watch_or_send_video' => $subscriptionList->count_watch_or_send_video
        ]);
    }

    public function spend(User $user, $type): bool
    {
        $subscription = $this->getUserCurrentSubscription($user);

        if (!$subscription) {
            return false;
        }

        if ($type == self::NEW_CONTACT) {
//            if ($subscription->)
        }


        return true;
    }
}
