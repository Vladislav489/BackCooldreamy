<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Operator\OperatorStatisticTableResource;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatMessageLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLogic;
use App\Models\ChatMessage;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorChatLimit;
use App\Models\User;
use App\Models\User\UserRole;
use App\Repositories\Operator\OperatorRepository;
use App\Services\Statistic\StatisticService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperatorStatisticController extends Controller
{
    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var StatisticService */
    private StatisticService $statisticService;

    public function __construct(
        OperatorRepository $operatorRepository,
        StatisticService $statisticService
    )
    {
        $this->operatorRepository = $operatorRepository;
        $this->statisticService = $statisticService;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function me()
    {
        return Auth::user();
    }

    public function blockLimits(Request $request)
    {
        $manId = $request->man_id;
        if (!$manId) {
            return response()->json(['error' => 'man id is required'], 422);
        }

        $girlId = $request->girl_id;
        if (!$girlId) {
            return response()->json(['error' => 'girl id is required'], 422);
        }

        OperatorChatLimit::query()->where('girl_id', $girlId)->where('man_id', $manId)->update([
            'limits' => 0
        ]);

        return response()->json(['message' => 'success']);
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

}
/*
Illuminate\Database\QueryException: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'chat_messages.sender_user_id' in 'where clause' (Connection: mysql, SQL: select count(*) as aggregate from `model_has_roles` where model_has_roles.role_id in (2) AND chat_messages.sender_user_id <= 0 AND chat_messages.sender_user_id != null) in file C:\OSPanel\domains\site.com\vendor\laravel\framework\src\Illuminate\Database\Connection.php on line 795

#0 C:\OSPan
 */
