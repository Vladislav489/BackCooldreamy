<?php

namespace App\Enum\Auth;

use App\Enum\AbstractEnum;

class CreditLogTypeEnum extends AbstractEnum
{
    /** Пополнение пользователя */
    const INCOME = 'income';

    /** Пользователь потерял деньги */
    const OUTCOME = 'outcome';
}
