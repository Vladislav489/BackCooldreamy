<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Operator\OperatorFineRepository;
use App\Repositories\Operator\OperatorLogRepository;
use App\Repositories\Operator\OperatorReportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OperatorReportController extends Controller
{
    /** @var OperatorReportRepository  */
    private OperatorReportRepository $operatorReportRepository;

    public function __construct(
        OperatorReportRepository $operatorReportRepository,
    )
    {
        $this->operatorReportRepository = $operatorReportRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function reports(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json(
            $this->operatorReportRepository->index($user, $request->all())
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
            'text' => 'required|string|max:255',
            'date_time' => 'required|date_format:Y-m-d H:i',
            'is_important' => 'required|boolean'
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

        $this->operatorReportRepository->store($user, $request->all());

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

        $report = $this->operatorReportRepository->show($user, $id);

        $this->operatorReportRepository->delete($report);

        return response()->json(['message' => 'success']);
    }
}
