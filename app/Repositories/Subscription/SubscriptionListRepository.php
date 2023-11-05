<?php

namespace App\Repositories\Subscription;

use App\Models\Subscription\SubscriptionList;

class SubscriptionListRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return SubscriptionList::findOrFail($id);
    }
}
