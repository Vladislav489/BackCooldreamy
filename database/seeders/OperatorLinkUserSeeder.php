<?php

namespace Database\Seeders;

use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorLinkUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'cool_date_admin@gmail.com')->first();
        if (!$admin) {
            $admin = User::factory()->create([
                'email' => 'cool_date_admin@gmail.com',
                'password' => Hash::make('1Asd2s_asd#'),
                'name' => 'cool_date_admin'
            ]);
        }

        $admin->syncRoles(1);
//
        $operator = User::query()->where('email', 'cool_date_operator2@gmail.com')->first();
        if (!$operator) {
            $operator = User::factory()->create([
                'email' => 'cool_date_operator2@gmail.com',
                'password' => Hash::make('2d5swdfgkptrdee#'),
                'name' => 'cool_date_operator'
            ]);
        }
        $operator->syncRoles(2);

        $operator = User::query()->where('email', 'cool_date_operator1@gmail.com')->first();
        if (!$operator) {
            $operator = User::factory()->create([
                'email' => 'cool_date_operator1@gmail.com',
                'password' => Hash::make('2d5swdfgkptrdee#'),
                'name' => 'cool_date_operator'
            ]);
        }
        $operator->syncRoles(2);

//        foreach (User::query()->where('is_real', false)->where('gender', 'female')->whereDoesntHave('operator')->get() as $user) {
//            OperatorLinkUsers::query()->create([
//                'operator_work' => 0,
//                'admin_work' => 0,
//                'description' => 'test',
//                'disabled' => false,
//                'user_id' => $user->id,
//                'operator_id' => $operator->id,
//                'admin_id' => $admin->id
//            ]);
//        }
    }
}
