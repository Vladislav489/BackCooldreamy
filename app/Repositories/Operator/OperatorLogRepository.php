<?php

namespace App\Repositories\Operator;

use App\Models\Operator\OperatorLog;
use App\Models\Operator\OperatorReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OperatorLogRepository
{
    /**
     * @param User $operator
     * @param array $requestData
     * @return LengthAwarePaginator
     */
    public function index(User $operator, array $requestData = []): LengthAwarePaginator
    {
        $query = OperatorLog::query()->with(['man', 'anket'])->where('operator_id', $operator->id);

        if ($dateTime = Arr::get($requestData, 'date')) {
            $query->whereDate('created_at', Carbon::parse($dateTime)->format('Y-m-d'));
        }

        return $query->paginate();
    }

    /**
     * @param User $operator
     * @param $id
     * @return OperatorLog
     */
    public function show(User $operator, $id): OperatorLog
    {
        return OperatorLog::where('operator_id', $operator->id)->findOrFail($id);
    }

    /**
     * @param User $operator
     * @param array $data
     * @return OperatorLog
     */
    public function store(User $operator, array $data = []): OperatorLog
    {
        return OperatorLog::create(array_merge($data, [
            'operator_id' => $operator->id
        ]));
    }

    /**
     * @param OperatorLog $operatorLog
     * @return bool|null
     */
    public function delete(OperatorLog $operatorLog): bool|null
    {
        return $operatorLog->delete();
    }
}
