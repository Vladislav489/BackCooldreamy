<?php


namespace App\Services\FireBase;


use App\Models\Auth\UsersTokenFireBase;
use App\Models\User;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class FireBaseService {

    static public function sendPushFireBase($user_to,$title,$message,$image = null,$priority = null){
        $fireBase = new FirebaseMessage();
        if(!($user_to instanceof  User)) {
            $user_to = User::find($user_to);
        }
        if(!is_null($user_to)) {
            $token_fire_base = UsersTokenFireBase::query()->where("user_id", '=', $user_to->id)->first();
            if (!is_null($token_fire_base)) {
                $fireBase->withTitle($title)->withBody($message);
                if (!is_null($image))
                    $fireBase->withImage($image)->withIcon("https://cooldreamy.com/_next/static/media/logo-big.e8c435d9.svg");
                if (is_null($priority))
                    $fireBase->withPriority('high');
                else
                    $fireBase->withPriority($priority);
                logger( "Firebase".$user_to->id."=".$token_fire_base->token_fire_base);
                $result = $fireBase->asNotification($token_fire_base->token_fire_base);
            }
        }
    }
}
