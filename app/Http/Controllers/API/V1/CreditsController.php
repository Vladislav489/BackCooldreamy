<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Action\ActionEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\CreditLog;
use App\Models\Image;
use App\Models\Promotion;
use App\Models\UserPromotion;
use App\Repositories\Auth\CreditLogRepository;
use App\Services\Rating\RatingService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\ServicePrices;
use App\Models\Subscriptions;
use App\Models\OperatorChatLimit;
use App\Models\OperatorChatLimitActions;
use App\Models\CreditsRefillLog;

class CreditsController extends Controller
{
//    /** @var SubscriptionService */
//    private SubscriptionService $subscriptionService;
//
//    public function __construct(SubscriptionService $subscriptionService)
//    {
//        $this->subscriptionService = $subscriptionService;
//    }

    public function get_free_message(){
      $user = User::find(Auth::id());
      $prices = ServicePrices::find(1);
      return response(['count_free_message'=>ceil($prices->credits/$prices->price)]);
    }


    // получае счёт пользователя
    public function get_my_credits()
    {
        // получаем пользователя, а затем возвращаем баланс его счёта
        $user = Auth::user();
        $credit = User\CreditsReals::query()->where("user_id","=",$user->id)->get()->first();
        if(is_null($credit)){
            $credit = User\CreditsReals::create([
                'user_id' => $user->id,
                'credits' => 0
            ]);
        }

        return response($credit->credits);
    }

    public function check_payment_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required'
        ]);

        if (!$request->service_id) {
            return response()->json(['error' => "Не указан ID услуги!","acquiring" => 0], 500);
        } else {
            return $this->check_payment($request->service_id);
        }
    }

    // проверяем возможность оплаты
    public function check_payment($service_id,$action = "chat",$second_user_id = 0){
        //получаем сервис
        $result = false;
        $servicePrice = ServicePrices::where('id', $service_id)->get()->first();
        dump(['service_price' => $servicePrice->price]);
        if($servicePrice) {
            // получаем данные пользоваетля
            $user = Auth::user();
//            $subscriptionService = resolve(SubscriptionService::class);
//            $subscrip = $subscriptionService->getUserCurrentSubscription($user);
//            if ($subscrip) {
//                switch ($action){
//                    case ActionEnum::VIEWING_PHOTO_IN_CHAT:
//                        if($subscrip->count_watch_or_send_photos > 0) {
//                            Subscriptions::query()->where('id','=',$subscrip->id)
//                                ->update(['count_watch_or_send_photos'=> $subscrip->count_watch_or_send_photos-=1]);
//                            $result = true;
//                        }else{
//                            $result = false;
//                        }
//                    break;
//                    default:
//                        $result = true;
//                    break;
//                }
//
//                if ($result) {
//                    return $result;
//                } else {
//                    return response()->json(['error' => "Цена покупки превышает сумму на счету пользоваетля!", "acquiring" => 1], 500);
//                }
//            }

            //оплата только лоя мужчин
            dump('before paymentcheck');
            $result = $user->check_payment_man($servicePrice->price,$service_id,$action,$second_user_id);
            dump('after paymentcheck');
            // если не false знасит все прошло успешно
            if ($result) {
                return $result;
            } else {
                return response()->json(['error' => "Цена покупки превышает сумму на счету пользоваетля!", "acquiring" => 1], 500);
            }
        } else {
            return response()->json(['error' => "Услуги с ID " . $service_id . " не существует!", "acquiring" => 0], 500);
        }
    }

    // получение списка услуг и цены за них
    public function get_services_cost()
    {
        $ervicesCost = ServicePrices::where("disabled",0)->get();
        return response($ervicesCost);
    }

    // добавление элемента в список услуг с ценами
    public function put_service_cost()
    {
        $ervicesCost = ServicePrices::all();
        return response($ervicesCost);
    }

    // редактирование элемента в списке услуг с ценами
    public function set_service_cost()
    {
        $ervicesCost = ServicePrices::all();
        return response($ervicesCost);
    }

}
