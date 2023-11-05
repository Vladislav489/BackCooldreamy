<?php

namespace App\Enum\Auth;

use App\Enum\AbstractEnum;

class AuthLogTypeEnum extends AbstractEnum
{
    /** @var string */
    const AUTH = 'authorization';

    /** @var string */
    const REG = 'register';

    /** @var string */
    const LOGOUT = 'logout';
}
