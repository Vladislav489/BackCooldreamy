<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\OperatorStatisticTableResource;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLogic;
use App\Models\User;
use App\Repositories\Operator\OperatorRepository;
use App\Services\Operator\WorkingShiftService;
use App\Services\Statistic\StatisticService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OperatorStatisticController extends Controller
{
    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var StatisticService */
    private StatisticService $statisticService;

    private WorkingShiftService $workingShiftService;
    public function __construct(
        OperatorRepository $operatorRepository,
        StatisticService $statisticService,
        WorkingShiftService $workingShiftService
    )
    {
        $this->workingShiftService = $workingShiftService;
        $this->operatorRepository = $operatorRepository;
        $this->statisticService = $statisticService;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function me()
    {
        return Auth::id();
    }



    /**
     * @return AnonymousResourceCollection
     */
    public function table(): AnonymousResourceCollection
    {
        $users = $this->operatorRepository->index();

        return OperatorStatisticTableResource::collection($users);
    }

    /**
     * @return JsonResponse
     */
    public function statistic(): JsonResponse
    {
        return response()->json($this->statisticService->getStatistic());
    }

    public function inactiveOperator(){
        return  response()->json($this->workingShiftService->getInactiveOperator());
    }

    public function getAverageMessageFirstTime(Request  $request){
        return response()->json($this->statisticService->getAverageMessageFirstTime($request));
    }

    public function getSaleBalance(Request  $request){
        return response()->json($this->statisticService->getCreditsBalance($request));
    }


    public function getCountMessage(Request  $request){
        return response()->json($this->statisticService->getMessageCount($request));
    }


    public function getCountMessageListAncet(Request  $request){
        return response()->json($this->statisticService->getMessageCountByAncet($request));
    }

    public function getCountMessageListOperator(Request  $request){
        return response()->json($this->statisticService->getMessageCountByOperator($request));
    }
    public function getMessageFirstTimeByChat(Request  $request){
        return response()->json($this->statisticService->getMessageFirstTime($request));
    }

    public function getOperatorList(Request  $request){
        return response()->json($this->statisticService->getOperator($request));
    }

    public function getOperatorListStatistic(Request  $request){
        return response()->json($this->statisticService->getOperatorListStatisticAdmin($request));
    }

    public function getCountAncet(Request  $request){
        return response()->json($this->statisticService->getCountAncet($request));
    }
    public function getCountAncetWork(Request  $request){
        return response()->json($this->statisticService->getCountAncetWork($request));
    }
    public function getAverageMessageFirstTimeOperator(Request  $request){
        return response()->json($this->statisticService->getAverageMessageFirstTimeOperator( $request));
    }

}
