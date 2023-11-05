<?php

namespace Database\Seeders\Factory;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableFactorySeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->delete();
        User::factory()->count(10)->create();
    }
}
