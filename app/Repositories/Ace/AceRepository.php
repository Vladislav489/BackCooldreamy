<?php

namespace App\Repositories\Ace;

use App\Models\Ace\AceLimitAssignment;
use App\Models\AceLimit;
use App\Models\User;

class AceRepository
{
    /**
     * @param int $lowestLimit
     * @return AceLimitAssignment|null
     */
    public function getLowestAssignment(int $lowestLimit = 1): ?AceLimitAssignment
    {
        return AceLimitAssignment::query()->where('limit', '<=', $lowestLimit)->latest()->first();
    }

    /**
     * @param User $user
     * @return AceLimit
     */
    public function createAceLimits(User $user): AceLimit
    {
        return AceLimit::create([
            'user_id' => $user->id,
            'current_random_second' => 30,
            'ace_limit' => 0
        ]);
    }
}
