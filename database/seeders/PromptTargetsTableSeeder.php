<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptTargetsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_targets')->delete();
        
        \DB::table('prompt_targets')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Общение',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:59',
                'updated_at' => '2023-03-28 14:58:59',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Серьезные отношения',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:59',
                'updated_at' => '2023-03-28 14:58:59',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Флирт',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:59',
                'updated_at' => '2023-03-28 14:58:59',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Совместное путешествие',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:59',
                'updated_at' => '2023-03-28 14:58:59',
            ),
        ));
        
        
    }
}