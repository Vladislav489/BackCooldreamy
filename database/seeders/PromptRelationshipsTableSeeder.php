<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptRelationshipsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_relationships')->delete();
        
        \DB::table('prompt_relationships')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Одинок',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Одинока',
                'gender' => 'Female',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'В поиске',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Вдова',
                'gender' => 'Female',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            4 => 
            array (
                'id' => 5,
                'text' => 'Вдовец',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            5 => 
            array (
                'id' => 6,
                'text' => 'Есть парень',
                'gender' => 'Female',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
            6 => 
            array (
                'id' => 7,
                'text' => 'Есть девушка',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:57:50',
                'updated_at' => '2023-03-28 14:57:50',
            ),
        ));
        
        
    }
}