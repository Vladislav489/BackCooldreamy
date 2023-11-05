<?php

namespace App\Repositories\Operator;

use App\Models\Operator\OperatorDelay;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class DelayRepository
{
    /**
     * @param array $requestData
     * @return float
     */
    public function getDelay(array $requestData = []): float
    {
        $operators = OperatorDelay::query();

        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            $operators->where('created_at', '<=', Carbon::now()->subMonths($lastMonth));
        }

        return $operators->avg('delay') ?? 0;
    }

    /**
     * @param array $requestData
     * @return float
     */
    public function getAvgResponse(array $requestData = []): float
    {
        // TODO уточнить
        $operators = OperatorDelay::query();

        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            $operators->where('created_at', '<=', Carbon::now()->subMonths($lastMonth));
        }

        $delay = $operators->avg('delay') ?? 0;

        $time = $operators->avg('time') ?? 0;

        return $time - $delay;
    }
}
