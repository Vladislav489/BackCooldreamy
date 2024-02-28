<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorCreditsLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorForfeitsLogic;
use App\Models\Operator\OperatorForfeit;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use App\Repositories\Operator\ChatRepository;
use App\Repositories\Operator\OperatorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OperatorMessageController extends Controller
{
    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    public function __construct(OperatorRepository $operatorRepository)
    {
        $this->operatorRepository = $operatorRepository;
    }

    public function statistics()
    {    $ankets = null;

        $operator = Auth::user();
        if ($operator->getRoleNames()->toArray()[0] == 'admin') {
            $ankets = OperatorLinkUsers::all()->pluck('user_id');
        }else{
            $ankets = $operator->ancets()->with([])->pluck('user_id');
        }

        $chats = OperatorChatLimit::query()->whereIn('girl_id', $ankets)
            ->where(function ($query) {
                $query->where('chat_id', null);
            })->where('limits', ">=", 1)
            ->selectRaw("*, 'chat' as type_of_model");

//        $letters = OperatorLetterLimit::query()->whereIn('girl_id', $ankets)
//            ->where(function ($query) {
//                $query->where('letter_id', null);
//            })->where('limits', ">", 0)
//            ->selectRaw("*, 'letter' as type_of_model");
//
//        if ($filterSearch = Arr::get($requestData, 'search')) {
//
//            $letters->whereHas('man', function ($query) use ($filterSearch) {
//                $query->where('name', 'like', "%$filterSearch%")->orWhere('email', 'like', "%$filterSearch%");
//            });
//        }

//        $combinedBuilder = $chats->union($letters)->orderBy('updated_at', 'desc');

        $countLimits = $chats->orderByDesc('updated_at')->count();

        $chats = resolve(ChatRepository::class)
            ->index(['anket_ids' => $ankets, 'is_query' => true])
            ->with([])
            ->where('deleted_by_first_user', false)
            ->where('deleted_by_second_user', false)
            ->where('is_answered_by_operator', false)
            ->selectRaw("*, 'chat' as type_of_model");

//        $letters = resolve(LetterRepository::class)
//            ->index(['anket_ids' => $ankets, 'search_message' => Arr::get($requestData, 'search'), 'is_query' => true])
//            ->with([])
//            ->where('is_answered_by_operator', false)
//            ->selectRaw("*, 'letter' as type_of_model");

        $combinedBuilder = $chats->where('is_answered_by_operator', false)->orderBy('updated_at', 'desc');

        $countMessages = $combinedBuilder->count();

        return response()->json([
            'count_messages' => $countMessages,
            'count_limits' => $countLimits
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $request->get('page');
        $per_page = $request->get('per_page');
        $messages = $this->operatorRepository->getOperatorLastMessages($user, $page,$per_page, $request->all());
        return response()->json($messages);
    }

    public function latterlimits(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $request->get('page');
        $per_page = $request->get('per_page');
        $messages = $this->operatorRepository->getOperatorLetterLimits()($user, $page,$per_page, $request->all());
        return response()->json($messages);
    }



    public function forfeitsMessage(Request $request){
        $validator = Validator::make($request->all(), [
            'operator_id' => ['required'],
            'message_id' => ['required'],
            'chat_id' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        try {
            OperatorForfeitsLogic::add($request->operator_id, $request->message_id, $request->chat_id);
        }catch (\Throwable $e){
            return response()->json(['error' => "server error"], 500);
        }
        return response()->json(['success' => 'success'], 200);
    }

    public function limits(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $request->get('page');
        $per_page = $request->get('per_page');
        $messages = $this->operatorRepository->getOperatorLimits($user, $page,$per_page, $request->all());
        return response()->json($messages);
    }

    public function getOperatorPayment(Request $request)
    {
        $params = $request->all();
        $params['operator'] = (string)Auth::id();
        $operatorPayments = new OperatorCreditsLogic($params, [DB::raw('SUM(credits) as credits, message_type')]);
        $operatorPayments->offPagination()->getFullQuery()->groupBy('message_type');
        return $operatorPayments->getList();
    }
}
