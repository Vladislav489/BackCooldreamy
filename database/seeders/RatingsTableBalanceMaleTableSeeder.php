<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RatingsTableBalanceMaleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Ratings_TableBalanceMale')->delete();
        
        \DB::table('Ratings_TableBalanceMale')->insert(array (
            0 => 
            array (
                'ID' => 1,
                'Text' => 'Нулевая',
                'WeightFrom' => 0.0,
            ),
            1 => 
            array (
                'ID' => 2,
                'Text' => 'Низкая',
                'WeightFrom' => 40.0,
            ),
            2 => 
            array (
                'ID' => 3,
                'Text' => 'Обычная',
                'WeightFrom' => 60.0,
            ),
            3 => 
            array (
                'ID' => 4,
                'Text' => 'Средняя',
                'WeightFrom' => 80.0,
            ),
            4 => 
            array (
                'ID' => 5,
                'Text' => 'Высокая',
                'WeightFrom' => 100.0,
            ),
        ));
        
        
    }
}