<?php

namespace App\Services\Payment;

use App\Enum\Payment\PaymentStatusEnum;
use App\Models\Pay\PayGoogle;
use App\Models\Promotion;
use App\Models\Subscription\SubscriptionList;
use App\Models\User;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\UserPromotion;
use App\Services\Promotion\PromotionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GooglePayService
{
    use PreparePayment;

    public function __construct(private PromotionService $promotionService)
    {
    }

    public function pay($userId)
    {
        $user = User::findOrFail($userId);
        $dataPay = json_decode(PayGoogle::where('user_id', $userId)->latest()->first()->data_pay, true);
        $payload = json_decode($dataPay['Payload'], true);
        $payloadJson = json_decode($payload['json'], true);
        $product = $payloadJson['productId'];

        $typeArr = explode('_', $product);
        $type = $typeArr[0];
        $typeId = $typeArr[1];


        switch ($type){
            case 'credit':
                $modelType = CreditList::findOrFail($typeId);
                break;
            case 'subscription':
                $modelType = SubscriptionList::findOrFail($typeId);
                break;
            case 'premium':
                $modelType = PremiumList::findOrFail($typeId);
                break;
            case 'promotion':
                $modelType = Promotion::findOrFail($typeId);
                break;
            default:
                return response()->json(['error' => 'type error'], 500);
                break;
        }
        // Проверка на one_time
        if ($modelType->is_one_time) {
            if (Payment::query()->where('user_id', $user->id)->where('list_id', $modelType->id)->where('status', '=', PaymentStatusEnum::SUCCESS)->exists()) {
                return response()->json(['error' => 'This is one time service!'], 500);
            }
        }
        if ($modelType instanceof Promotion) {
            if (UserPromotion::query()->where('promotion_id', $modelType->id)->where('user_id', Auth::id())->where('status', '!=','new')->exists()) {
                return response()->json(['error' => 'This is one time service!'], 500);
            }
        }

//        dd($price = $dataPay['Payload']['skuDetails'][0]['price_amount_micros'] * 0.000001);

        switch ($type){
            case 'credit':
            case 'subscription':
            case 'premium':
                $model = $modelType;
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'status' => PaymentStatusEnum::SUCCESS,
                    'price' => $model->price,
                    'list_id' => $model->id,
                    'list_type' => get_class($model),
                ]);
                break;
            case 'promotion':
                $model = $this->promotionService->subscribe($user, $modelType, PaymentStatusEnum::WAITING_PAYMENT);
                break;
            default:
                abort(500);
                break;
        }

        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/payments/google/gpay.log')
        ]);
        if (!is_null($payment)) {
            $this->prepare($payment, $log);
            return 'success';
        } else return 'error';
    }
}
