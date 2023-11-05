<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Operator\WorkingShiftStatusEnum;
use App\Http\Controllers\Controller;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorForfeitsLogic;
use App\Models\User;
use App\Repositories\Operator\WorkingShiftRepository;
use App\Services\Operator\WorkingShiftService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function Symfony\Component\Translation\t;

class WorkingShiftController extends Controller
{
    /** @var WorkingShiftService */
    private WorkingShiftService $workingShiftService;

    public function __construct(WorkingShiftService $workingShiftService)
    {
        $this->workingShiftService = $workingShiftService;
    }

   /* public function find(): JsonResponse
    {
        $user = Auth::user();

        $workingShiftLog = $this->workingShiftService->getCurrent($user);
        if ($workingShiftLog) {
            $workingShiftLog->setRelation('price', $this->workingShiftService->getPrice($workingShiftLog));
        }
        return response()->json(['data' => $workingShiftLog]);
    }*/
    public function start(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = ($request->get('operator_id'))?User::find($request->get('operator_id')):Auth::user();
        if($this->workingShiftService->checkUserIntersection($user)){
            $workingShiftLog = $this->workingShiftService->start($user);
            return response()->json(['data' => $workingShiftLog]);
        } else {
            return response()->json(['message' => "another user is using one of your accounts"]);
        }
    }
    public function stop(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = ($request->get('operator_id') && Auth::user()->getRoleNames()->toArray()[0] == 'admin' )?User::find($request->get('operator_id')):Auth::user();
        $workingShiftLog = $this->workingShiftService->stop($user);

        return response()->json(['data' => $workingShiftLog]);
    }
    public function inactive(Request $request){
        $user = ($request->get('operator_id') && Auth::user()->getRoleNames()->toArray()[0] == 'admin' )?User::find($request->get('operator_id')):Auth::user();
        $workingShiftLog = $this->workingShiftService->inactive($user);
        try {
            OperatorForfeitsLogic::add($request->get('operator_id'), $request->get('message_id'), $request->get('chat_id'));
        }catch (\Throwable $e){

        }
        return response()->json(['data' => $workingShiftLog]);
    }
    public function inactiveDelete(Request $request){
        $user = ($request->get('operator_id') && Auth::user()->getRoleNames()->toArray()[0] == 'admin' )?User::find($request->get('operator_id')):Auth::user();
        $workingShiftLog = $this->workingShiftService->inactiveDelete($user);

        return response()->json(['data' => $workingShiftLog]);
    }
    public function pausedStart(Request $request){
        $user = ($request->get('operator_id') && Auth::user()->getRoleNames()->toArray()[0] == 'admin'  )?User::find($request->get('operator_id')):Auth::user();
        $workingShiftLog = $this->workingShiftService->paused($user);
        return response()->json(['data' => $workingShiftLog]);
    }
    public function pausedStop(Request $request){
        $user = ($request->get('operator_id') && Auth::user()->getRoleNames()->toArray()[0] == 'admin' )?User::find($request->get('operator_id')):Auth::user();
        $workingShiftLog = $this->workingShiftService->paused_stop($user);
        return response()->json(['data' => $workingShiftLog]);
    }
    public function workTimeLog(Request $request){
        $validator = Validator::make($request->all(), [
            'work_time' => [
                'required'
            ],
            'user_id' => [
                'required'
            ],
            'log_action' => [
                'required',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $workingShiftLogTimeFromFont = $this->workingShiftService->saveLogWorkTimeFromFont($request);
        return  response()->json(['data' =>$workingShiftLogTimeFromFont]);
    }

    public function getCurrentStatus(){
        $user = Auth::user();
        $workingShiftLog = $this->workingShiftService->getLastCurrentStatus($user);
        if(is_null($user))
            return response()->json(['data' => null]);

        return response()->json(['data' => $workingShiftLog]);
    }
    public  function getStatusList(){
        return response()->json(['data' =>  WorkingShiftStatusEnum::getList()]);
    }


}
