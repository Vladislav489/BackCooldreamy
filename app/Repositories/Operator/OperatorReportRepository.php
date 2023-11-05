<?php

namespace App\Repositories\Operator;

use App\Models\Operator\OperatorReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OperatorReportRepository
{
    /**
     * @param User $operator
     * @param array $requestData
     * @return LengthAwarePaginator
     */
    public function index(User $operator, array $requestData = []): LengthAwarePaginator
    {
        $query = OperatorReport::query()->with(['man', 'anket']);

        //todo можно добавить только для оператора
        if ($dateTime = Arr::get($requestData, 'date')) {
            $query->whereDate('date_time', Carbon::parse($dateTime)->format('Y-m-d'));
        }

        return $query->paginate();
    }

    /**
     * @param User $operator
     * @param $id
     * @return OperatorReport
     */
    public function show(User $operator, $id): OperatorReport
    {
        return OperatorReport::where('operator_id', $operator->id)->findOrFail($id);
    }

    /**
     * @param User $operator
     * @param array $data
     * @return mixed
     */
    public function store(User $operator, array $data = []): OperatorReport
    {
        return OperatorReport::create(array_merge($data, [
            'operator_id' => $operator->id,
        ]));
    }

    /**
     * @param OperatorReport $operatorReport
     * @return bool|null
     */
    public function delete(OperatorReport $operatorReport): bool|null
    {
        return $operatorReport->delete();
    }
}
