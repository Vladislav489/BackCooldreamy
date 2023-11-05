<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptCareersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_careers')->delete();
        
        \DB::table('prompt_careers')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Работаю',
                'gender' => '',
                'created_at' => '2023-03-28 14:55:57',
                'updated_at' => '2023-03-28 14:55:57',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Учусь',
                'gender' => '',
                'created_at' => '2023-03-28 14:55:57',
                'updated_at' => '2023-03-28 14:55:57',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Безработный',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:55:57',
                'updated_at' => '2023-03-28 14:55:57',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Безработная',
                'gender' => 'Female',
                'created_at' => '2023-03-28 14:55:57',
                'updated_at' => '2023-03-28 14:55:57',
            ),
        ));
        
        
    }
}