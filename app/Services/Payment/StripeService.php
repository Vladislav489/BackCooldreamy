<?php

namespace App\Services\Payment;

use App\Enum\Payment\PaymentStatusEnum;
use App\ModelAdmin\CoreEngine\LogicModels\User\UserCooperationCronLogic;
use App\Models\Subscription\SubscriptionList;
use App\Models\Subscriptions;
use App\Models\User;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\User\Premuim;
use App\Models\UserPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService
{
    use PreparePayment;

    /**
     * @param Payment $payment
     * @return string
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function payCheckout(Payment $payment)
    {
        $success_url = url('/api/payment');
        $cancel_url = 'https://cooldreamy.com/profile';
        Stripe::setApiKey(config('stripe.sk'));

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => $success_url . '?session_id={CHECKOUT_SESSION_ID}&user_id=' . $payment->user_id . '&app_key=' . env('APP_KEY'),
            'cancel_url' => $cancel_url,
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Payment'
                        ],
                        'unit_amount' => $payment->price * 100 // because stripe
                    ],
                    'quantity' => 1
                ]
            ],
        ]);

        return $session;
    }

    public function checkoutSuccess($request)
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/payments/stripe/stripe.log')
        ]);
        $appKey = $request->get('app_key');
        $userId = $request->get('user_id');
        $sessionId = $request->get('session_id');

        if ($appKey != env('APP_KEY')) {
            return 'error';
        }

        try {
            User::findOrFail($userId);
        } catch (\Throwable $e) {
            return 'no such user';
        }

        $payment = Payment::where('payment_id', $sessionId)->where('user_id', $userId)->first();
        if (is_null($payment)) {
            $log->error('Payment not found: ' . $payment->id);
            return 'no payments found';
        } else {
            $payment->status = PaymentStatusEnum::SUCCESS;
            $payment->save();
            $this->prepare($payment, $log);
            return 'success';
        }
    }

    public function pay(Payment $payment)
    {

        Stripe::setApiKey(config('stripe.sk'));
        $session = PaymentIntent::create([
            "currency" => "USD",
            "automatic_payment_methods" => ["enabled" => true],
            'amount' => $payment->price * 100 // because stripe
        ]);

        return $session;
    }

    public function parseWebhook($requestData)
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/payments/stripe/stripe.log')
        ]);

        $log->info(json_encode($requestData));

        $data = Arr::get($requestData, 'data');

        if ($data && count($data)) {
            $object = Arr::get($data, 'object');
            if ($object && count($object)) {
                $id = Arr::get($object, 'id');
                $status = Arr::get($object, 'status');
                $payment = Payment::query()->where('payment_id', $id)->first();
                if (!$payment) {
                    $log->error('Payment not found:' . $id);
                    return ;
                }
               /* if ($payment->status == PaymentStatusEnum::CANCEL || $payment->status == PaymentStatusEnum::SUCCESS) {
                    $log->info('Payment is already canceled or success');
                    return;
                }*/

                if ($status == 'canceled') {
                    $payment->status = PaymentStatusEnum::CANCEL;
                    $payment->save();
                    $log->alert('CANCEL changed payment status: ' . $payment->id);
                    return true;
                } else if ($status == 'succeeded') {
                    $payment->status = PaymentStatusEnum::SUCCESS;
                    $this->prepare($payment, $log);
                    $payment->save();
                    try {
                        (new  UserCooperationCronLogic())->addTaskSale($payment);
                    }catch (\Throwable $e){
                        logger("UserCooperationCronLogic ". $e->getMessage());
                    }
                    $log->alert('SuccessFully changed payment status: ' . $payment->id);
                    return true;
                }
                return true;
                $log->alert('SuccessFully not changed payment status: ' . $payment->id);
            }
        }
        return false;
    }


    /**
     * @param Payment $payment
     * @return \Stripe\Checkout\Session
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPayment(Payment $payment)
    {
        Stripe::setApiKey(config('stripe.sk'));

        return Session::retrieve($payment->payment_id);
    }

}
