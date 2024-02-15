<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\OperatorChatLimit;
use App\Models\OperatorChatLimitActions;
use App\Models\OperatorChatLimitAssigment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon;

class OperatorLimitController extends Controller
{
    const FirstMessage = 1;
    const MessageInChat = 2;
    const SendWink = 3;
    const SendLike = 4;
    const OpenProfile = 5;
    const SendGift = 6;
    const ReadAce = 7;
    const StickerChat = 8;
    const ImageChat = 9;

    const LimitMaximum = 10.0;


    public static function generateStartOperatorChatLimits(User $user)
    {
//        $startLimits = OperatorChatLimitAssigment::all();
//        foreach ($startLimits as $startLimit) {
//            $girls_count = rand($startLimit->anket_count_from, $startLimit->anket_count_to);
//            $girls = User::where('is_real', false)->where('profile_type_id', $startLimit->type_id)->inRandomOrder()->limit($girls_count)->get();
//            foreach ($girls as $girl) {
//                OperatorChatLimit::create([
//                    'man_id' => $user->id,
//                    'girl_id' => $girl->id,
//                    'limits' => rand($startLimit->limit_from, $startLimit->limit_to)
//                ]);
//            }
//        }
    }


    public static function getById($id)
    {
        return OperatorChatLimit::where('chat_id', $id)->first();
    }

    /**
     * @param $girlId
     * @param $manId
     * @param null $chatId
     * @return false
     */
    public static function spendLimitsByOperator($girlId, $manId, $chatId = null)
    {
        $limit = OperatorChatLimit::where('man_id','=', $manId)->where('girl_id','=', $girlId);

        if ($chatId) {
            $limit->where('chat_id','=', $chatId);
        }
        $limits = $limit->get();
        dd($limits);
        foreach ($limits as $limit) {
            if ($chatId) {
                $limit->chat_id = $chatId;
            }
            if ($limit && $limit->limits >= 1) {
                $limit->limits--;
                $limit->save();
                return $limit;
            }
        }

        return false;
    }

    /**
     * @param $girlId
     * @param $manId
     * @return mixed
     */
    public static function getChatLimits($girlId, $manId)
    {
        return OperatorChatLimit::where('man_id', $manId)->where('girl_id', $girlId)->first();
    }

    public static function spendLimits(User $user)
    {
        $limit = OperatorChatLimit::where('man_id', $user->id)->where('girl_id', Auth::user()->id)->first();
        if ($limit && $limit->limits >= 1) {
            $limit->limits--;
            $limit->save();
            return $limit;
        } else return false;
    }

    public static function addChatLimits($girl_id, $action_id, $chatId = null)
    {

     //   if(!is_null(Auth::user()->prompt_target_id) || !is_null(Auth::user()->prompt_finance_state_id) ||
     //       !is_null(Auth::user()-prompt_source_id) ||!is_null(Auth::user()->prompt_relationship_id) || !is_null(Auth::user()->prompt_career_id)) {
            $array = [
                'girl_id' => $girl_id,
                'man_id' => Auth::user()->id,
            ];

            if ($chatId) {
                $array['chat_id'] = $chatId;
            }

            $limit = OperatorChatLimit::where('girl_id', $girl_id)->where('man_id', Auth::user()->id)->first();
            if (!$limit) {
                $limit = OperatorChatLimit::create([
                    'girl_id' => $girl_id,
                    'man_id' => Auth::user()->id,
                    'chat_id' => $chatId
                ]);
            } else {
                if ($chatId) {
                    $limit->chat_id = $chatId;
                }
            }

            $value = OperatorChatLimitActions::where('id', $action_id)->first()->limits;
            $limit->limits = $limit->limits + $value;
            if ($limit->limits > self::LimitMaximum) {
                $limit->limits = self::LimitMaximum;
            }
            $limit->save();

            self::addOperatorCoolDown($girl_id);
            return $limit;
      //  }else{
       //     return  NULL;
      //  }
    }

    public static function addOperatorCoolDown($operator_id)
    {
        $user = User::find($operator_id);
        if ($user->online == false) {
            $user->cooldown = now()->addMinute(10);
            $user->save();
        }
    }
}
