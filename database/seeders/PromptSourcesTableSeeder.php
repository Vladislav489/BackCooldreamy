<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptSourcesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_sources')->delete();
        
        \DB::table('prompt_sources')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'TikTok',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'YouTube',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Google',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Рекомендации друзей и знакомых',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
            4 => 
            array (
                'id' => 5,
                'text' => 'Реклама на других сайтах',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
            5 => 
            array (
                'id' => 6,
                'text' => 'Другое',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:40',
                'updated_at' => '2023-03-28 14:58:40',
            ),
        ));
        
        
    }
}