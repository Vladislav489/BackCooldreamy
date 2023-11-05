<?php

namespace App\Enum\Ace;

use App\Enum\AbstractEnum;

class AceLimitAssignmentEnum extends AbstractEnum
{
    /** Начальный лимит */
    const INITIAL_LIMIT = 1;

    /** Лимит после 5 айсов */
    const INTERN_LIMIT = 5;

    /** Лимит после 8 айсов */
    const JUNIOR_LIMIT = 8;

    /** Лимит после 10 айсов */
    const MIDDLE_LIMIT = 10;

    /** Лимит после 15 айсов */
    const SENIOR_LIMIT = 15;

    /** Лимит после 20 айсов */
    const GURU_LIMIT = 20;

    /** @var array|int[] */
    private static array $defaultToSeconds = [
        self::INITIAL_LIMIT => 120,
        self::INTERN_LIMIT => 720,
        self::JUNIOR_LIMIT => 3600,
        self::MIDDLE_LIMIT => 7200,
        self::SENIOR_LIMIT => 12000,
        self::GURU_LIMIT => 36000,
    ];

    /** @var array|int[] */
    private static array $defaultFromSeconds = [
        self::INITIAL_LIMIT => 60,
        self::INTERN_LIMIT => 120,
        self::JUNIOR_LIMIT => 720,
        self::MIDDLE_LIMIT => 3600,
        self::SENIOR_LIMIT => 7200,
        self::GURU_LIMIT => 12000,
    ];

    /**
     * @param $item
     * @return int
     */
    public static function getFrom($item): int
    {
        return self::$defaultFromSeconds[$item];
    }

    /**
     * @param $item
     * @return int
     */
    public static function getTo($item): int
    {
        return self::$defaultToSeconds[$item];
    }
}
