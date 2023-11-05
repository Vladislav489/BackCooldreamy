<?php

namespace App\Services\Operator;

use App\Enum\Operator\WorkingShiftStatusEnum;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLogic;
use App\Models\Operator\WorkingShiftAnserOperators;
use App\Models\Operator\WorkingShiftCron;
use App\Models\Operator\WorkingShiftLog;
use App\Models\Operator\WorkingShiftWorkTimeLogs;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use App\Repositories\Operator\AnketRepository;
use App\Repositories\Operator\WorkingShiftRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkingShiftService
{
    /** @var WorkingShiftRepository */
    private WorkingShiftRepository $workingShiftRepository;
    public function __construct(WorkingShiftRepository $workingShiftRepository)
    {
        $this->workingShiftRepository = $workingShiftRepository;
    }

    public function getInactiveOperator()
    {     $user_ids = WorkingShiftCron::query()->groupBy("user_id")->get('user_id')->pluck('user_id');
          return User::query()->whereIn('id',$user_ids)->get();
    }
    public function getPrice($workingShift = null)
    {
        if ($workingShift) {
            // todo logic
            return 0;
        }
        return 0;
    }


    public function operatorSendAnsver($operator_id,$ancet_id,$man_id,$chat_id,$message_id,$status_message = null){
      return  WorkingShiftAnserOperators::create([
            'operator_id' => $operator_id ,
            'ancet_id' => $ancet_id,
            'man_id' => $man_id,
            'chat_id' => $chat_id,
            'ansver_new_message' => $status_message,
            'message_id' => $message_id,
        ]);
    }
    //возвращает последний статус на текуший день
    public function getLastCurrentStatus(User $operator){
        $workingShift = $this->workingShiftRepository->getLastCurrentStatus($operator);
        if(!is_null($workingShift)){
            return $workingShift;
        }else{
            return null;
        }
    }
    /**
     * @param User $operator
     * @return WorkingShiftLog
     */
    //Проверяет нет ли пересечение между фейк анкетами для операторов
    public function checkUserIntersection(User $operator){

      //  var_dump((new OperatorLogic(['operator' => (string)$operator->id, 'is_work_operator' => '2']))->offPagination()->setJoin(['Ancet'])->getSqlToStr());

  //      var_dump((new OperatorLogic(['operator' => (string)$operator->id, 'is_work_operator' => '2']))->offPagination()->setJoin(['Ancet'])->Exist());
        return(!(new OperatorLogic(['operator' => (string)$operator->id, 'is_work_operator' => '2']))->offPagination()->setJoin(['Ancet'])->Exist());
    }

    //Запуск рабочего времени
    public function start(User $operator): WorkingShiftLog
    {
        /*$workingShift = $this->workingShiftRepository->searchByOperator($operator,WorkingShiftStatusEnum::ACTIVE);
        if (!$workingShift) {*/
            (new AnketRepository())->setAnketsStatusWork($operator->id);
            return $this->workingShiftRepository->store([
                'user_id' => $operator->id,
                'date_from' => Carbon::now(),
                'status' => WorkingShiftStatusEnum::ACTIVE,
            ]);
        /*} else {
            return $workingShift;
        }*/
    }
    /**
     * @param User $operator
     * @return WorkingShiftLog|null
     */
    public function stop(User $operator)
    {
       /* $workingShift = $this->workingShiftRepository->searchByOperator($operator,WorkingShiftStatusEnum::CLOSED);

        if (!$workingShift) {*/
            (new AnketRepository())->setAnketsStatusCloseWork($operator->id);
            $active = $this->workingShiftRepository->getLastActiveStatus($operator);
            if(!is_null($active)) {
                WorkingShiftLog::query()->where('id', '=', $active->id)->update(['date_to' => Carbon::now()]);
            }
            return $this->workingShiftRepository->store([
                'user_id' => $operator->id,
                'date_from' => Carbon::now(),
                'status' => WorkingShiftStatusEnum::CLOSED,
            ]);
        /*}else{
            return $workingShift;
        }*/
    }
    //Добавляем метку простоя в лог и в урон
    public function inactive(User $operator){
        $this->workingShiftRepository->store([
            'user_id' => $operator->id,
            'date_from' => Carbon::now(),
            'status' => WorkingShiftStatusEnum::INACTIVE,
        ]);
        return WorkingShiftCron::create([
            'user_id' => $operator->id,
        ]);
    }
    //удаляем метку простоя из крона
    public function inactiveDelete(User $operator){
       return WorkingShiftCron::query()->where('user_id','=',$operator->id)->delete();
    }

    public function paused(User $operator){
        $workingShift = $this->workingShiftRepository->searchByOperator($operator,WorkingShiftStatusEnum::PAUSE);
        if (!$workingShift) {
            return $this->workingShiftRepository->store([
                'user_id' => $operator->id,
                'date_from' => Carbon::now(),
                'status' => WorkingShiftStatusEnum::PAUSE,
            ]);
        } else {
            return $workingShift;
        }
    }

    public function paused_stop(User $operator){
        //$workingShift = $this->workingShiftRepository->searchByOperator($operator,WorkingShiftStatusEnum::PAUSE_BACK);
        //if (!$workingShift) {
        $pause = $this->workingShiftRepository->getLastPauseStatus($operator);
        $pause->update(['date_to'=>Carbon::now()]);
            return $this->workingShiftRepository->store([
                'user_id' => $operator->id,
                'date_from' => Carbon::now(),
                'status' => WorkingShiftStatusEnum::PAUSE_BACK,
            ]);
        //} else {
        //    return $workingShift;
       // }
    }
    //лог и сторкджа браузера  все действия оператора  с кгопками действиям
    public function saveLogWorkTimeFromFont(Request $request){
           return WorkingShiftWorkTimeLogs::create(
                [
                    'user_id' => $request->user_id,
                    'work_time' => $request->work_time,
                    'log_action'=>json_encode($request->log_action),
                ]
            );
    }
    //Крон который закрывает рабочую сессию если большое время простоя
    public static function closeWorkIfInactiveMoreLimit(){
       $operators = WorkingShiftCron::query()
            ->select('user_id')
            ->where(DB::raw('created_at <  NOW() - INTERVAL 240 MINUTE'))->get()->pluck('user_id');
       if(count($operators)) {
           $closeWork = [];
           foreach ($operators as $id) {
               $closeWork[] = [
                   'user_id' => $id,
                   'date_from' => Carbon::now(),
                   'status' => WorkingShiftStatusEnum::INACTIVE_CLOSED,
               ];
               (new AnketRepository())->setAnketsStatusCloseWork($id);
           }
           WorkingShiftLog::query()->insert($closeWork);
           WorkingShiftCron::query()->whereIn('user_id', $operators)->delete();
       }
    }
}
