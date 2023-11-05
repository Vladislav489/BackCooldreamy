<?php

namespace App\Enum\User;

use App\Enum\AbstractEnum;

class RoleEnum extends AbstractEnum
{
    /** @var int */
    const ADMIN = 1;

    /** @var int */
    const OPERATOR = 2;

    /**
     * @var array|int[]
     */
    public static array $adminRoles = [
        self::ADMIN,
        self::OPERATOR
    ];
}
