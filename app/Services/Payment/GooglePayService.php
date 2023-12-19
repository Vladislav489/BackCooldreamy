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
use App\Traits\PaymentTypeTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GooglePayService
{
    use PreparePayment, PaymentTypeTrait;

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
        $orderId = $payloadJson['orderId'];

        $typeArr = explode('_', $product);
        $typeData['list_type'] = $typeArr[0];
        $typeData['list_id'] = $product;
        $typeData['column_id'] = 'googlepay_id';

        $model = $this->getProductModel($typeData);
        $isOneTime = $this->checkOneTime($model, $user);

        $payment = null;

        if (!$isOneTime) {
            $payment = $this->createPayment($model, $typeData['list_type'], $user);
            $payment->status = PaymentStatusEnum::SUCCESS;
            $payment->payment_id = $orderId;
            $payment->save();
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
