<?php

namespace App\Enum\User;

use App\Enum\AbstractEnum;

class ProfileTypeEnum extends AbstractEnum
{
    /**
     * Топ
     */
    const TOP = 1;

    /**
     * Восемнадцать+
     */
    const EIGHTEEN = 2;

    /**
     * Премиум
     */
    const PREM = 3;

    /**
     * Стандартный
     */
    const STANDARD = 4;
}
