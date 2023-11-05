<?php

namespace App\Repositories\Operator;

use App\Models\User\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PaymentRepository
{
    /**
     * @param array $requestData
     * @return float
     */
    public function getBalance(array $requestData = []): float
    {
        $payments = Payment::query();

        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            if ($toMonth = Arr::get($requestData, 'to_month')) {
                $payments->where('created_at', '>=', Carbon::now()->subMonths($lastMonth))
                ->where('created_at', "<=", Carbon::now()->subMonth($toMonth));
            } else {
                $payments->where('created_at', '>=', Carbon::now()->subMonths($lastMonth));
            }
        }

        if ($lastDay = Arr::get($requestData, 'last_day')) {
            if ($lastDay == 0) {
                $payments->where('created_at', '>=', Carbon::now()->startOfDay());
            } else {
                $payments->where('created_at', '>=', Carbon::now()->subDays($lastDay)->startOfDay())
                    ->where('created_at', '<=', Carbon::now()->subDays($lastDay)->endOfDay());
            }
        }

        return $payments->sum('price');
    }
}
