<?php

namespace App\Http\Controllers;

use App\Enum\Payment\PaymentStatusEnum;
use App\Models\Pay\PayGoogle;
use App\Models\Promotion;
use App\Models\Subscription\SubscriptionList;
use App\Models\Subscriptions;
use App\Models\User\CreditList;
use App\Models\User\Payment;
use App\Models\User\PremiumList;
use App\Models\User\Premuim;
use App\Models\UserPromotion;
use App\Services\Payment\GooglePayService;
use App\Services\Payment\StripeService;
use App\Services\Premium\PremuimService;
use App\Services\Promotion\PromotionService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use function Symfony\Component\Translation\t;
use Illuminate\Http\Request;

class PaymentController
{
    private StripeService $stripeService;

    private SubscriptionService $subscriptionService;

    private PremuimService $premuimService;

    private PromotionService $promotionService;

    private GooglePayService $googlePayService;

    public function __construct(
        StripeService $stripeService,
        SubscriptionService $subscriptionService,
        PremuimService $premuimService,
        PromotionService $promotionService,
        GooglePayService $googlePayService,
    )
    {
        $this->stripeService = $stripeService;
        $this->subscriptionService = $subscriptionService;
        $this->premuimService = $premuimService;
        $this->promotionService = $promotionService;
        $this->googlePayService = $googlePayService;
    }

    public function creditList(){
        $user =  Auth::user();
        $list = CreditList::query()->selectRaw("*")
            ->whereRaw("credit_lists.id  NOT IN ((SELECT list_id  FROM payments  WHERE user_id = {$user->id}
            AND list_id = credit_lists.id  AND  credit_lists.is_one_time = 1  AND status = 'success'  AND list_type  LIKE '%CreditList%' GROUP BY list_id ))")
            ->get();
        return response()->json($list);
    }

    public function subscriptionList(){
        $user =  Auth::user();
        $list = SubscriptionList::query()->selectRaw("*")
            ->whereRaw("subscriptions_list.id  NOT IN ((SELECT list_id  FROM payments  WHERE user_id = {$user->id}
            AND list_id = subscriptions_list.id  AND  subscriptions_list.one_time = 1 AND status = 'success'   AND list_type  LIKE '%SubscriptionList%' GROUP BY list_id ))")
            ->get();
        return response()->json($list);
    }

    public function premiumList(){
        $user =  Auth::user();
        $list = PremiumList::query()->selectRaw("*")
            ->whereRaw("premium_lists.id  NOT IN ((SELECT list_id  FROM payments  WHERE user_id = {$user->id}
            AND list_id = premium_lists.id  AND  premium_lists.one_time = 1   AND list_type  AND status = 'success' LIKE '%PremiumList%' GROUP BY list_id ))")
            ->get();
        return response()->json($list);
    }

    public function promotion()
    {
        $userPromotion = UserPromotion::query()->with('promotion')->where('user_id', Auth::id())->where('status', 'new')->orderBy('created_at', 'desc')->get();
//        $userPromotion = UserPromotion::query()->with('promotion')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        foreach ($userPromotion as $item) {
            $hours = $item->promotion->hours;
            if (Carbon::now()->lt(Carbon::parse($item->created_at)->addHours($hours))) {
                return response()->json(['data' => $userPromotion]);
            }
        }

        return response()->json(['data' => []]);
    }

    public function promotions()
    {
        $userPromotions = UserPromotion::query()->with('promotion')->where('user_id', Auth::id())->where('status', 'new')->get();

        $promotions = new Collection();
        foreach ($userPromotions as $userPromotion) {
            $hours = $userPromotion->promotion->hours;
            if (Carbon::now()->lt(Carbon::parse($userPromotion->created_at)->addHours($hours))) {
                $promotions = $promotions->merge([$userPromotion]);
            }
        }

        return response()->json($promotions);
    }

    public function watch()
    {
        $user = Auth::user();
        $user->count_shop_watches = $user->count_shop_watches + 1;
        $user->save();

        return response()->json(['message' => 'success']);
    }

    public function activatePromotion(Request $request)
    {
        $userPromotion = UserPromotion::findOrFail($request->user_promotion_id);
        $userPromotion->status = 'waiting_confirmation';
        $userPromotion->save();

        return response()->json(['message' => 'success']);
    }

    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'list_id' => [
                'required', 'integer',
            ],
            'list_type' => ['required', 'string', 'in:credit,subscription,premium,promotion'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $user = Auth::user();

        $type = $request->list_type;
        $typeId = $request->list_id;

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

        switch ($type){
            case 'credit':
            case 'subscription':
            case 'premium':
                $model = $modelType;
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'status' => PaymentStatusEnum::WAITING_PAYMENT,
                    'price' => $modelType->price,
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


        $link = $this->stripeService->pay($payment);
        $payment->payment_url = $link->client_secret;
        $payment->payment_id = $link->id;
        $payment->save();

        return response()->json(['clientSecret' => $link->client_secret, 'link' => $link, 'model' => $model]);
    }

    public function subscribe(Request $request){
        $validator = Validator::make($request->all(), [
            'list_id' => ['required', 'integer'],
            'list_type' => ['required', 'string', 'in:subscription,premium'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $user = Auth::user();
        $type = $request->list_type;
        $typeId = $request->list_id;

        if ($type == 'subscription') {
            $modelType = SubscriptionList::findOrFail($typeId);
        } else if ($type == 'premium') {
            $modelType = PremiumList::findOrFail($typeId);
        } else {
            return response()->json(['error' => 'type error'], 500);
        }

        if ($modelType->one_time) {
            if ($type == 'subscription') {
                if (Subscriptions::where('user_id', $user->id)
                    ->where('service_id', $modelType->id)
                    ->exists()) {
                    return response()->json(['error' => 'type error'], 500);
                }
            } else if ($type == 'premium') {
                if (Premuim::where('user_id', $user->id)
                    ->where('service_id', $modelType->id)
                    ->exists()) {
                    return response()->json(['error' => 'type error'], 500);
                }
            }
        }
        $timeNow = Carbon::now();

        if (Subscriptions::where('user_id', $user->id)
            ->where('service_id', $modelType->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->exists()) {
            return response()->json(['error' => 'already exists'], 500);
        }

        if (Premuim::where('user_id', $user->id)
            ->where('service_id', $modelType->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->exists()) {
            return response()->json(['error' => 'already exists'], 500);
        }

        if ($type == 'subscription')
            $this->subscriptionService->subscribe($user, $modelType, PaymentStatusEnum::SUCCESS);
        else
            $this->premuimService->subscribe($user, $modelType, PaymentStatusEnum::SUCCESS);

        return response()->json(['message' => 'success']);
    }

    public function stripeWebhook(Request $request){
        $this->stripeService->parseWebhook($request->all());
        return response()->json(['message' => 'success']);
    }

    public function saveGooglePay(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'numeric'],
            'data_pay' =>['required']
        ]);
        $data = $validator->valid();
        if(!$data) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $userId = $request->get('user_id');
       $result = PayGoogle::create([
                'user_id' => $data['user_id'],
                'data_pay' => $data['data_pay']
       ]);
       if(!is_null($result)) {
//           $response = $this->googlePayService->pay($userId);
           return response()->json(['message' => $response ?? 0]);
       } else
       return response()->json(['message' => "error"]);
    }

    public function googlePay()
    {
        $this->googlePayService->pay();
    }

    public function testWebHook(){
       $data  =  json_decode('{"id":"evt_3NzR3rFjkPZRdnX10mKp9CYu","object":"event","api_version":"2022-11-15","created":1696887303,"data":{"object":{"id":"pi_3NzR3rFjkPZRdnX10aZgcIto","object":"payment_intent","amount":5000,"amount_capturable":0,"amount_details":{"tip":[]},"amount_received":5000,"application":null,"application_fee_amount":null,"automatic_payment_methods":{"allow_redirects":"always","enabled":true},"canceled_at":null,"cancellation_reason":null,"capture_method":"automatic","client_secret":"pi_3NzR3rFjkPZRdnX10aZgcIto_secret_RNwRs3i7kf9xPdTMBAzNcV0TQ","confirmation_method":"automatic","created":1696887143,"currency":"usd","customer":null,"description":null,"invoice":null,"last_payment_error":null,"latest_charge":"ch_3NzR3rFjkPZRdnX10GRlg3Fs","livemode":true,"metadata":[],"next_action":null,"on_behalf_of":null,"payment_method":"pm_1NzR6OFjkPZRdnX13BMKXqcR","payment_method_configuration_details":{"id":"pmc_1MzoFwFjkPZRdnX1o4y1HWQX","parent":null},"payment_method_options":{"card":{"installments":null,"mandate_options":null,"network":null,"request_three_d_secure":"automatic"},"link":{"persistent_token":null}},"payment_method_types":["card","link"],"processing":null,"receipt_email":null,"review":null,"setup_future_usage":null,"shipping":null,"source":null,"statement_descriptor":null,"statement_descriptor_suffix":null,"status":"succeeded","transfer_data":null,"transfer_group":null}},"livemode":true,"pending_webhooks":1,"request":{"id":"req_V3xPcCNojn2gto","idempotency_key":"ec6b3069-0232-49ff-bcb8-9c7319e30e8f"},"type":"payment_intent.succeeded"}',true);
       try {
           $this->stripeService->parseWebhook($data);
       }catch (\Throwable $e){
           var_dump($e->getMessage(),$e->getFile(),$e->getLine());
       }
    }

    public function premium(){
        $user = Auth::user();
        return response()->json($this->premuimService->getUserCurrentSubscription($user));
    }

    public function subscription(){
        $user = Auth::user();
        return response()->json($this->subscriptionService->getUserCurrentSubscription($user));
    }
}
