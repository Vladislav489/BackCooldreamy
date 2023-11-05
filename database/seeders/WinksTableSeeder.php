<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WinksTableSeeder extends Seeder {

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run() {


        \DB::table('winks')->delete();

        \DB::table('winks')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'from_user_id' => 301,
                    'to_user_id' => 205,
                    'created_at' => '2023-03-31 20:20:24',
                    'updated_at' => '2023-03-31 20:20:24',
                ),
            1 =>
                array(
                    'id' => 4,
                    'from_user_id' => 302,
                    'to_user_id' => 298,
                    'created_at' => '2023-03-31 17:31:13',
                    'updated_at' => '2023-03-31 17:31:13',
                ),
            2 =>
                array(
                    'id' => 14,
                    'from_user_id' => 302,
                    'to_user_id' => 250,
                    'created_at' => '2023-03-31 17:39:20',
                    'updated_at' => '2023-03-31 17:39:20',
                ),
        ));


    }
}
