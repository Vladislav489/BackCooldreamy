<?php

namespace App\Repositories\Subscription;

use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Support\Carbon;

class SubscriptionRepository
{
    /**
     * @param User $user
     * @param $service
     * @param array $data
     * @return Subscriptions
     */
    public function store(User $user, $service, array $data = []): Subscriptions
    {
        return Subscriptions::create(array_merge([
            'user_id' => $user->id,
            'service_id' => $service,
        ], $data));
    }
}
