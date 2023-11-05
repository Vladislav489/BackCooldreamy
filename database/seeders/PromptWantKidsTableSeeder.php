<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptWantKidsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_want_kids')->delete();
        
        \DB::table('prompt_want_kids')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Нет детей',
                'gender' => '',
                'created_at' => '2023-03-28 14:59:45',
                'updated_at' => '2023-03-28 14:59:45',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Не хочу детей',
                'gender' => '',
                'created_at' => '2023-03-28 14:59:45',
                'updated_at' => '2023-03-28 14:59:45',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Хочу ребенка',
                'gender' => '',
                'created_at' => '2023-03-28 14:59:45',
                'updated_at' => '2023-03-28 14:59:45',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Есть дети',
                'gender' => '',
                'created_at' => '2023-03-28 14:59:45',
                'updated_at' => '2023-03-28 14:59:45',
            ),
        ));
        
        
    }
}