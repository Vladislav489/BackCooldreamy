<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Operator\OperatorFineRepository;
use App\Repositories\Operator\OperatorLogRepository;
use App\Repositories\Operator\OperatorRepository;
use App\Services\Statistic\StatisticService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OperatorFineController extends Controller
{
    /** @var OperatorFineRepository  */
    private OperatorFineRepository $operatorFineRepository;

    public function __construct(
        OperatorFineRepository $operatorFineRepository,
    )
    {
        $this->operatorFineRepository = $operatorFineRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fines(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json(
            $this->operatorFineRepository->index($user, $request->all())
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'man_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
            'anket_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
            'reason' => 'required|string|max:255',
            'limit' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();

        if (User::findOrFail($request->man_id)->is_real == false) {
            return response()->json(['error' => 'Man should be real'], 500);
        }

        if (User::findOrFail($request->anket_id)->is_real == true) {
            return response()->json(['error' => 'Anket shouldnt be real'], 500);
        }

        $this->operatorFineRepository->store($user, $request->all());

        return response()->json(['message' => 'success']);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $report = $this->operatorFineRepository->show($user, $id);

        $this->operatorFineRepository->delete($report);

        return response()->json(['message' => 'success']);
    }
}
