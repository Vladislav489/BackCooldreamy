<?php


namespace App\Enum\Action;


class ActionEnum
{
 const 	SEND_MESSAGE = 1;
 const 	SEND_LETTER = 2;
 const 	SEND_PHOTO_IN_CHAT = 3;
 const 	SEND_VIDEO_IN_CHAT = 4;
 const 	VIEWING_PHOTO_IN_CHAT = 5;
 const 	VIEWING_VIDEO_IN_CHAT = 6;
 const 	SEND_STICKER_IN_CHAT = 7;
 const  OPEN_LETTER = 8;
 const 	VIEW_PHOTO_IN_LETTER = 9;
 const 	VIEW_VIDEO_IN_LETTER = 10;
 const 	INCOGNITO_MODE_FOTRVER =11;
 const 	ACTIVITY_IN_PHOTO_FEED = 12;
 const	SEND_GIFT_IN_CHAT = 13;
 const	SEND_STICKER_IN_LETTER  = 15;
 const	PAY_IMAGE_18 = 16;
 const	PAY_IMAGE_TOP = 17;

    public function getActions()
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getConstants();
 }


}
