<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptFinanceStatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_finance_states')->delete();
        
        \DB::table('prompt_finance_states')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Ни в чем не нуждаюсь',
                'gender' => '',
                'created_at' => '2023-03-28 14:56:45',
                'updated_at' => '2023-03-28 14:56:45',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Ищу спонсора',
                'gender' => '',
                'created_at' => '2023-03-28 14:56:45',
                'updated_at' => '2023-03-28 14:56:45',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Могу быть спонсором',
                'gender' => '',
                'created_at' => '2023-03-28 14:56:45',
                'updated_at' => '2023-03-28 14:56:45',
            ),
        ));
        
        
    }
}