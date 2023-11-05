<?php

namespace App\Enum\Rating;

use App\Enum\AbstractEnum;

class RatingAssignmentEnum extends AbstractEnum
{
    // Открытие профиля
    const OPEN_PROFILE = 1;

    // Лайк
    const LIKE_GIRL = 2;

    // Подмигнул
    const WINKED = 3;

    // Написано сообщение с прем подпиской
    const PREMIUM_MESSAGE = 4;

    // Написано сообщение по кредитам
    const CREDIT_MESSAGE = 5;

    // Сообщение в чате
    const SEND_PHOTO_CHAT = 6;

    // Видео в чате
    const SEND_VIDEO_CHAT = 7;

    // Просмотр фотографии
    const LOOK_PHOTO_CHAT = 8;

    // Просмотр видео
    const LOOK_VIDEO_CHAT = 9;

    // Открытие письма
    const OPEN_LETTER = 10;

    // Просмотр фото в письме
    const LOOK_PHOTO_LETTER = 11;

    // Просмотр видео в письме
    const LOOK_VIDEO_LETTER = 12;

    // Покупка премиум статуса
    const BUY_PREMIUM = 13;

    /** @var array */
    public static array $ratings = [
        self::OPEN_PROFILE => 0.1,
        self::LIKE_GIRL => 0.025,
        self::WINKED => 1,
        self::PREMIUM_MESSAGE => 1,
        self::CREDIT_MESSAGE => 10,
        self::SEND_PHOTO_CHAT => 20,
        self::SEND_VIDEO_CHAT => 30,
        self::LOOK_PHOTO_CHAT => 3,
        self::LOOK_VIDEO_CHAT => 6,
        self::OPEN_LETTER => 20,
        self::LOOK_PHOTO_LETTER => 5,
        self::LOOK_VIDEO_LETTER => 30,
        self::BUY_PREMIUM => 50
    ];

    /** @var array|string[] */
    public static array $ratingTexts = [
        self::OPEN_PROFILE => "Open Girl Profile",
        self::LIKE_GIRL => "Like Girl",
        self::WINKED => "Winked",
        self::PREMIUM_MESSAGE => "Send Premium Message",
        self::CREDIT_MESSAGE => "Send Credit Message",
        self::SEND_PHOTO_CHAT => "Send Photo",
        self::SEND_VIDEO_CHAT => "Send Video",
        self::LOOK_PHOTO_CHAT => "Look Photo",
        self::LOOK_VIDEO_CHAT => "Look Video",
        self::OPEN_LETTER => "Open Letter",
        self::LOOK_PHOTO_LETTER => "Look Photo From Letter",
        self::LOOK_VIDEO_LETTER => "Look Video From Letter",
        self::BUY_PREMIUM => "Buy Premium Subscription"
    ];
}
