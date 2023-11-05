<?php

namespace App\Repositories\Operator;

use App\Models\Operator\OperatorFine;
use App\Models\Operator\OperatorReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OperatorFineRepository
{
    /**
     * @param User $operator
     * @param array $requestData
     * @return LengthAwarePaginator
     */
    public function index(User $operator, array $requestData = []): LengthAwarePaginator
    {
        $query = OperatorFine::query()->with(['man', 'anket'])->where('operator_id', $operator->id);

        if ($dateTime = Arr::get($requestData, 'date')) {
            $query->whereDate('created_at', Carbon::parse($dateTime)->format('Y-m-d'));
        }

        return $query->paginate();
    }

    /**
     * @param User $operator
     * @param $id
     * @return OperatorFine
     */
    public function show(User $operator, $id): OperatorFine
    {
        return OperatorFine::where('operator_id', $operator->id)->findOrFail($id);
    }

    /**
     * @param User $operator
     * @param array $data
     * @return OperatorFine
     */
    public function store(User $operator, array $data = []): OperatorFine
    {
        return OperatorFine::create(array_merge($data, [
            'operator_id' => $operator->id
        ]));
    }

    /**
     * @param OperatorFine $operatorFine
     * @return bool|null
     */
    public function delete(OperatorFine $operatorFine): bool|null
    {
        return $operatorFine->delete();
    }
}
