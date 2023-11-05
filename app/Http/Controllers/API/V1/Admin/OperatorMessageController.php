<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use App\Repositories\Operator\OperatorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperatorMessageController extends Controller
{
    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    public function __construct(OperatorRepository $operatorRepository)
    {
        $this->operatorRepository = $operatorRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse{
        /** @var User $user */
        $user = Auth::user();
        $page = $request->get('page');
        $messages = $this->operatorRepository->getAdminOperatorLastMessages($user, $page, $request->all());
        return response()->json($messages);
    }

    public function getAllMessageLast8Hour(Request $request){
        $user = Auth::user();
        $page = $request->get('page');
        $per_page = $request->get('per_page');
        $messages = $this->operatorRepository->getOperatorLastMessages8($user, $page,$per_page, $request->all());
        return response()->json($messages);
    }



}
