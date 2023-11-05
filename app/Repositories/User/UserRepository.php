<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository
{
    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data = []): User
    {
        $user->fill($data);
        $user->save();

        return $user;
    }
}
