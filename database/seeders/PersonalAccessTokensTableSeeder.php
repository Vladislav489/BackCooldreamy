<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PersonalAccessTokensTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('personal_access_tokens')->delete();

        \DB::table('personal_access_tokens')->insert(array (
            0 =>
            array (
                'id' => 1,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 10,
                'name' => 'auth_token',
                'token' => '1ca0fb9d0ec8a71a22028a6e506e9fa2303223164e7f43ad8492341a9fa1a894',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:30:07',
                'updated_at' => '2023-03-27 14:30:07',
            ),
            1 =>
            array (
                'id' => 2,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 11,
                'name' => 'auth_token',
                'token' => '5e579559b66ba1a3f2ca8077aa57c3e040e048ce3143c71c43c2b0c1fc206c6e',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:30:17',
                'updated_at' => '2023-03-27 14:30:17',
            ),
            2 =>
            array (
                'id' => 3,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 13,
                'name' => 'auth_token',
                'token' => '5be65a34d79c65de7eabb28186a0001a7bbd8d8cd29c51b6497694512943ffa5',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:32:19',
                'updated_at' => '2023-03-27 14:32:19',
            ),
            3 =>
            array (
                'id' => 4,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 14,
                'name' => 'auth_token',
                'token' => 'd08e533b4689fe40af876d22b28907f428bcc3b50ad7bca413ee783034737b75',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:32:30',
                'updated_at' => '2023-03-27 14:32:30',
            ),
            4 =>
            array (
                'id' => 5,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 15,
                'name' => 'auth_token',
                'token' => 'a025b78527496a0e026db63bdba07ac5a256cd1e8a2dcb96eea02f8e4ed10c95',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:32:47',
                'updated_at' => '2023-03-27 14:32:47',
            ),
            5 =>
            array (
                'id' => 6,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 16,
                'name' => 'auth_token',
                'token' => '2f9e79b2d465d63b646436a39e36ef56e1526aecf99c9436c60320b33437079b',
                'abilities' => '["subscriber"]',
                'last_used_at' => '2023-03-27 14:58:56',
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:55:01',
                'updated_at' => '2023-03-27 14:58:56',
            ),
            6 =>
            array (
                'id' => 7,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 17,
                'name' => 'auth_token',
                'token' => 'da72edb35587a5004c4fa889bf05e22f8da39aa799314723152b6c2e38931148',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:56:02',
                'updated_at' => '2023-03-27 14:56:02',
            ),
            7 =>
            array (
                'id' => 8,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 18,
                'name' => 'auth_token',
                'token' => '5866f01f6410298b7d313bb5fe4c4bbbf667059bae3f3c8c1d6af241ef3a5ec8',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-27 14:56:09',
                'updated_at' => '2023-03-27 14:56:09',
            ),
            8 =>
            array (
                'id' => 9,
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => 1,
                'name' => 'auth_token',
                'token' => '6094e8e950c1b70d19981c3ccf67f020bdab1d9f3ec0c2e30c73534e5699c156',
                'abilities' => '["subscriber"]',
                'last_used_at' => NULL,
                'expires_at' => NULL,
                'created_at' => '2023-03-28 14:32:37',
                'updated_at' => '2023-03-28 14:32:37',
            ),
            9 =>
                array(
                    'id' => 10,
                    'tokenable_type' => 'App\\Models\\User',
                    'tokenable_id' => 301,
                    'name' => 'auth_token',
                    'token' => 'a183022fc3ac327c1945002f8a87347559d15a6a501acec7f5bd75cc0e79ca5d',
                    'abilities' => '["subscriber"]',
                    'last_used_at' => '2023-03-31 13:04:59',
                    'expires_at' => NULL,
                    'created_at' => '2023-03-31 11:37:29',
                    'updated_at' => '2023-03-31 13:04:59',
                ),
            10 =>
                array(
                    'id' => 11,
                    'tokenable_type' => 'App\\Models\\User',
                    'tokenable_id' => 302,
                    'name' => 'auth_token',
                    'token' => '20908b5da2f1385189a93e2c3eb35d9baf06588e90f11b3da426ef72c9634895',
                    'abilities' => '["subscriber"]',
                    'last_used_at' => '2023-03-31 17:39:22',
                    'expires_at' => NULL,
                    'created_at' => '2023-03-31 13:31:41',
                    'updated_at' => '2023-03-31 17:39:22',
                ),
        ));


    }
}
