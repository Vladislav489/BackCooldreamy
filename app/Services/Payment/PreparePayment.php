<?php

namespace App\Services\Payment;

use App\Models\Subscription\SubscriptionList;
use App\Models\Subscriptions;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\User\Premuim;
use App\Models\UserPromotion;

trait PreparePayment
{
    public function prepare(Payment $payment, $log) {
        $user = $payment->user;
        if ($payment->list_type == CreditList::class) {

            $creditList = CreditList::find($payment->list_id);
            if (!$creditList) {
                $log->error('Not Found Credit List' . $creditList->id);
            }
            $user->addCreditsReal($creditList->credits);

            $log->info('User add credits: '. $creditList->credits);
        } else if ($payment->list_type == SubscriptionList::class) {
            $subscriptionList = SubscriptionList::find($payment->list_id);
            Subscriptions::addNewSubscriptions($user->id,$payment->list_id);
            if (!$subscriptionList) {
                $log->error('Not Found Subscription List' . $subscriptionList->id);
            }


            $log->info('User accept subscription: '. $subscriptionList->id);

        } else if ($payment->list_type == PremiumList::class) {
            $premiumList =  PremiumList::find($payment->list_id);
            if (!$premiumList) {
                $log->error('Not Found Premium List' . $premiumList->id);
            }
            Premuim::addNewPremuim($user->id,$payment->list_id);
            $log->info('User accept premium: '. $premiumList->id);
        } else if ($payment->list_type == UserPromotion::class) {
            $userPromotion = UserPromotion::find($payment->list_id);
            if (!$userPromotion) {
                $log->error('Not Found User Promotion ' . $userPromotion->id);
            }
            $user = $userPromotion->user;

            $promotion = $userPromotion->promotion;

            if (!$promotion) {
                $log->error('Not Found Promotion ' . $promotion->id);
            }

            $user->addCreditsReal( $promotion->credits);
            $userPromotion->status = 'success';
            $userPromotion->save();
            $log->info('Promotion: '. $promotion->id);
            $log->info('User add credits by promotion: '. $promotion->credits);
        }
    }
}
