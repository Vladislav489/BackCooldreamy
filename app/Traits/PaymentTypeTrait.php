<?php

namespace App\Traits;

use App\Enum\Payment\PaymentStatusEnum;
use App\Models\Promotion;
use App\Models\Subscription\SubscriptionList;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\UserPromotion;
use Illuminate\Support\Facades\Auth;

trait PaymentTypeTrait
{
    public function getProductModel(array $typeData)
    {
        $type = $typeData['list_type'];
        $paymentTypeId = $typeData['list_id'];
        $columnId = $typeData['column_id'];
        switch ($type){
            case 'credit':
                $model = CreditList::where($columnId, $paymentTypeId)->first();
                break;
            case 'subscription':
                $model = SubscriptionList::where($columnId, $paymentTypeId)->first();
                break;
            case 'premium':
                $model = PremiumList::where($columnId, $paymentTypeId)->first();
                break;
            case 'promotion':
                $model = Promotion::where($columnId, $paymentTypeId)->first();
                break;
            default:
                return 'type error';
                break;
        }
        return $model ?? 'no such model';
    }

    public function checkOneTime($model, $user)
    {
        if ($model->is_one_time) {
            if (Payment::query()->where('user_id', $user->id)->where('list_id', $model->id)
                ->where('status', '=', PaymentStatusEnum::SUCCESS)->exists()) {
                return true;
            }
        }
        if ($model instanceof Promotion) {
            if (UserPromotion::query()->where('promotion_id', $model->id)
                ->where('user_id', Auth::id())->where('status', '!=','new')->exists()) {
                return true;
            }
        }
        return false;
    }

    public function createPayment($model, $type, $user)
    {
        switch ($type){
            case 'credit':
            case 'subscription':
            case 'premium':
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'status' => PaymentStatusEnum::WAITING_PAYMENT,
                    'price' => $model->price,
                    'list_id' => $model->id,
                    'list_type' => get_class($model),
                ]);
                break;
            case 'promotion':
                $model = $this->promotionService->subscribe($user, $model, PaymentStatusEnum::WAITING_PAYMENT);
                break;
            default:
                abort(500);
                break;
        }
        return $payment;
    }
}
