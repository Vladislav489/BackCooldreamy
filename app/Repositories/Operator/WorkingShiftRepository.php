<?php

namespace App\Repositories\Operator;

use App\Enum\Operator\WorkingShiftStatusEnum;
use App\Models\Operator;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class WorkingShiftRepository
{
    /**
     * @param User $operator
     * @param $id
     * @return WorkingShiftLog
     */





    public function findByOperator(User $operator, $id): WorkingShiftLog
    {
        return WorkingShiftLog::query()
            ->where('user_id', $operator->id)
            ->findOrFail($id);
    }

    /**
     * @param User $operator
     * @param array $requestData
     * @return WorkingShiftLog|null
     */



    public function getLastPauseStatus(User $operator){
        return WorkingShiftLog::query()
            ->where("user_id",$operator->id)
            ->where("status",'=',WorkingShiftStatusEnum::PAUSE)
            ->whereDate('created_at','=',date('Y-m-d',Carbon::now()->getTimestamp()))
            ->orderBy('created_at','desc')
            ->first();
    }
    public function getLastActiveStatus(User $operator){
        return WorkingShiftLog::query()
            ->where("user_id",$operator->id)
            ->where("status",'=',WorkingShiftStatusEnum::ACTIVE)
            ->whereDate('created_at','=',date('Y-m-d',Carbon::now()->getTimestamp()))
            ->orderBy('created_at','desc')
            ->first();
    }
    public function getLastCurrentStatus(User $operator){
        return WorkingShiftLog::query()
            ->where("user_id",$operator->id)
            ->where("status",'!=',WorkingShiftStatusEnum::INACTIVE)
            ->whereDate('created_at','=',date('Y-m-d',Carbon::now()->getTimestamp()))
            ->orderBy('created_at','desc')
            ->first();
    }


    public function searchByOperator(User $operator, $statusEnum ): ?WorkingShiftLog
    {
        return WorkingShiftLog::query()
        ->where("user_id",$operator->id)
        ->where('status', $statusEnum)
        ->whereDate('created_at','=',date('Y-m-d',Carbon::now()->getTimestamp()))
        ->latest()->first();
    }

    /**
     * @param User $operator
     * @return false|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function findLatestClosed(User $operator)
    {
        $latest = WorkingShiftLog::query()->where("user_id",$operator->id)->where('status', WorkingShiftStatusEnum::CLOSED)->latest()->first();
        if ($latest && $latest->date_to >= Carbon::now()->subMinutes(10)) {
            return $latest;
        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data = [])
    {
        return WorkingShiftLog::create($data);
    }

    /**
     * @param WorkingShiftLog $workingShiftLog
     * @param $data
     * @return WorkingShiftLog
     */
    public function update(WorkingShiftLog $workingShiftLog, $data): WorkingShiftLog
    {
        $workingShiftLog->fill($data);
        $workingShiftLog->save();
        return $workingShiftLog;
    }
}
