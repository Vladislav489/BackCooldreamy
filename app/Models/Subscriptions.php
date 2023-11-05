<?php

namespace App\Models;

use App\Enum\Action\ActionEnum;
use App\Enum\Payment\PaymentStatusEnum;
use App\Models\Subscription\SubscriptionList;

use App\Models\User\Premuim;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Subscriptions extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'service_id',
        'period_start',
        'period_end',
        'one_time',
        'count_letters',
        'count_watch_or_send_photos',
        'count_watch_or_send_video'
    ];

    public static function getValidPeriodAndLimit($user_id,$action){
        $obj = new self();
        $subInfo = $obj->newQuery()
            ->where('user_id','=',$user_id)
            ->where('status','=',1)->orderBy("created_at",'desc')
            ->get()->first();
        if(is_null($subInfo))
            return false;


        if(strtotime($subInfo->period_start) >= time() && strtotime($subInfo->period_start) <= time()){
            switch ($action){
                case ActionEnum::SEND_PHOTO_IN_CHAT:
                case ActionEnum::VIEWING_PHOTO_IN_CHAT:
                    if($subInfo->count_watch_or_send_photos == 0)
                        return false;
                    $subInfo->count_watch_or_send_photos-=1;
                break;
                case ActionEnum::SEND_VIDEO_IN_CHAT:
                case ActionEnum::VIEWING_VIDEO_IN_CHAT:
                    if($subInfo->count_watch_or_send_video == 0)
                        return false;
                    $subInfo->count_watch_or_send_video-=1;
                    break;
                case ActionEnum::SEND_LETTER:
                    if($subInfo->count_letters == 0)
                        return false;
                    $subInfo->count_letters -= 1;
                break;

                case ActionEnum::SEND_MESSAGE:
                    return  true;
                    break;

            }
            $subInfo->save();
            return  true;
        } else {
            $subInfo->newQuery()->where('user_id','=',$user_id)->update(['status'=>2]);
            return false;
        }
    }

   public static function addNewSubscriptions($user_id,$list_id){
       $typeId = $list_id;
       $modelType = SubscriptionList::findOrFail($typeId);
       if(is_null($modelType)) {
           return ['error' => 'type error'];
       }

       if ($modelType->one_time) {
          if (Subscriptions::where('user_id', $user_id)
                   ->where('service_id', $modelType->id)
                   ->exists()) {
               return ['error' => 'exist'];
          }
       }
       $timeNow = Carbon::now();

       if (Subscriptions::where('user_id', $user_id)
           ->where('service_id', $modelType->id)
           ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
           ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
           ->exists()) {
           return ['error' => 'already exists this day'];
       }


       self::insert([
            'status' => 1,
            'user_id' => $user_id,
            'service_id' => $modelType->id,
            'period_start' => Carbon::now(),
            'period_end' => Carbon::now()->addMinutes( $modelType->duration),
            'one_time' =>  $modelType->one_time,
            'count_letters' =>  $modelType->count_letters,
            'count_watch_or_send_photos' =>  $modelType->count_watch_or_send_photos,
            'count_watch_or_send_video' =>  $modelType->count_watch_or_send_video
        ]);
       return response()->json(['message' => 'success']);

   }

}
