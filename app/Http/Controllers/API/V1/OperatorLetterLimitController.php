<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Operator\OperatorLetterLimit;
use App\Models\Operator\OperatorLetterLimitAction;
use App\Models\OperatorChatLimit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OperatorLetterLimitController
{
    const OPEN = 1;

    const SEND_MESSAGE = 2;

    const LimitMaximum = 2;

    /**
     * @param $girlId
     * @param $manId
     * @return false
     */
    public static function spendLimitsByOperator($girlId, $manId, $letterId = null)
    {
        $limit = OperatorLetterLimit::where('man_id', $manId)->where('girl_id', $girlId);

        if ($letterId) {
            $limit->where('letter_id', $letterId);
        }

        $limit = $limit->first();

        if ($limit && $limit->limits >= 1) {
            $limit->limits--;
            $limit->save();
            return $limit;
        } else return false;
    }

    /**
     * @param $girlId
     * @param $manId
     * @return mixed
     */
    public static function getLetterLimits($girlId, $manId)
    {
        return OperatorLetterLimit::where('man_id', $manId)->where('girl_id', $girlId)->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getById($id)
    {
        return OperatorLetterLimit::where('letter_id', $id)->first();
    }

    /**
     * @param $girl_id
     * @param $action_id
     * @param null $letterId
     * @return mixed
     */
    public static function addLetterLimits($girl_id, $action_id, $letterId = null)
    {
        $data = [
            'girl_id' => $girl_id,
            'man_id' => Auth::user()->id,
        ];

        if ($girl_id) {
            $data['letter_id'] = $letterId;
        }

        $limit = OperatorLetterLimit::firstOrCreate($data);

        $value = OperatorLetterLimitAction::where('id', $action_id)->first();
        if (!$value) {
            $value = 1;
        } else {
            $value = $value->limits;
        }

        $limit->limits = $limit->limits + $value;
        if ($limit->limits > self::LimitMaximum) {
            $limit->limits = self::LimitMaximum;
        }
        $limit->save();
        self::addOperatorCoolDown($girl_id);
        return $limit;
    }

    /**
     * @param $operator_id
     */
    public static function addOperatorCoolDown($operator_id)
    {
        $user = User::find($operator_id);
        if ($user->online == false) {
            $user->cooldown = now()->addMinute(10);
            $user->save();
        }
    }
}
