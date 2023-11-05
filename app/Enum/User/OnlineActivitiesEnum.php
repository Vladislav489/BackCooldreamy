<?php

namespace App\Enum\User;

use App\Enum\AbstractEnum;

class OnlineActivitiesEnum extends AbstractEnum
{
    /**
     * Мужчина первый написал
     */
    const MAN_SEND_MESSAGE_FIRST = 1;

    /**
     * Мужчина написал сообщение в сущ чат
     */
    const MAN_SEND_MESSAGE_TO_EXISTS_CHAT = 2;
}
