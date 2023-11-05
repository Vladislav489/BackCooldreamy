<?php

namespace App\Repositories\Operator;

use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AnketRepository
{
    /**
     * @param User $user
     * @return Collection
     */
    public function index(User $user): Collection
    {
        return $user->ancets()->get();
    }

    /**
     * @param array $requestData
     * @return int
     */
    public function getCount(array $requestData = []): int
    {
        $query = User::where('is_real', false);

        if ($lastMonth = Arr::get($requestData, 'last_month')) {
            $query->where('created_at', '<=', Carbon::now()->subMonth($lastMonth));
        }

        return $query->count();
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return User::where('is_real', false)->whereHas('linkedOperator')->count();
    }

    public function setAnketsStatusWork($operator_id){
        return OperatorLinkUsers::query()->where("operator_id",'=',$operator_id)->update(['operator_work' => 1]);
    }

    public function setAnketsStatusCloseWork($operator_id){
        return OperatorLinkUsers::query()->where("operator_id",'=',$operator_id)->update(['operator_work' => 0]);
    }


}
