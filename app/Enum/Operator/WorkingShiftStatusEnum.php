<?php

namespace App\Enum\Operator;

use App\Enum\AbstractEnum;

class WorkingShiftStatusEnum extends AbstractEnum
{
    /** Активно сейчас */
    const ACTIVE = 'ACTIVE';

    /** Закрыто */
    const CLOSED = 'CLOSED';
    /** Обед */
    const PAUSE = 'PAUSE';
    /** Обед  закрыто*/
    const PAUSE_BACK = 'PAUSE_BACK';
    /**Простой*/
    const INACTIVE = "INACTIVE";
}
