<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FeedsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('feeds')->delete();

        \DB::table('feeds')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'from_user_id' => 301,
                    'to_user_id' => 300,
                    'is_skipped' => 0,
                    'is_liked' => 1,
                    'created_at' => '2023-03-31 14:47:46',
                    'updated_at' => '2023-03-31 14:47:46',
                ),
            1 =>
                array(
                    'id' => 2,
                    'from_user_id' => 301,
                    'to_user_id' => 299,
                    'is_skipped' => 1,
                    'is_liked' => 0,
                    'created_at' => '2023-03-31 14:47:46',
                    'updated_at' => '2023-03-31 14:47:46',
                ),
            2 =>
                array(
                    'id' => 3,
                    'from_user_id' => 302,
                    'to_user_id' => 299,
                    'is_skipped' => 0,
                    'is_liked' => 1,
                    'created_at' => '2023-03-31 13:32:03',
                    'updated_at' => '2023-03-31 13:32:03',
                ),
            3 =>
                array(
                    'id' => 4,
                    'from_user_id' => 302,
                    'to_user_id' => 298,
                    'is_skipped' => 0,
                    'is_liked' => 1,
                    'created_at' => '2023-03-31 13:33:19',
                    'updated_at' => '2023-03-31 13:33:19',
                ),
        ));


    }
}
