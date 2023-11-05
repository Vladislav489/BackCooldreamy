<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptInterestsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_interests')->delete();
        
        \DB::table('prompt_interests')->insert(array (
            0 => 
            array (
                'id' => 1,
                'Icon_url' => '',
                'text' => 'Спорт',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            1 => 
            array (
                'id' => 2,
                'Icon_url' => '',
                'text' => 'Искусство',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            2 => 
            array (
                'id' => 3,
                'Icon_url' => '',
                'text' => 'IT',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            3 => 
            array (
                'id' => 4,
                'Icon_url' => '',
                'text' => 'Финансы и инвестиции',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            4 => 
            array (
                'id' => 5,
                'Icon_url' => '',
                'text' => 'Наука',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            5 => 
            array (
                'id' => 6,
                'Icon_url' => '',
                'text' => 'Путешествия',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            6 => 
            array (
                'id' => 7,
                'Icon_url' => '',
                'text' => 'Бары и рестораны',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            7 => 
            array (
                'id' => 8,
                'Icon_url' => '',
                'text' => 'Экстрим',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            8 => 
            array (
                'id' => 9,
                'Icon_url' => '',
                'text' => 'Природа',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            9 => 
            array (
                'id' => 10,
                'Icon_url' => '',
                'text' => 'Кино',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            10 => 
            array (
                'id' => 11,
                'Icon_url' => '',
                'text' => 'Музыка',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            11 => 
            array (
                'id' => 12,
                'Icon_url' => '',
                'text' => 'Литература',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            12 => 
            array (
                'id' => 13,
                'Icon_url' => '',
                'text' => 'Шоппинг',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            13 => 
            array (
                'id' => 14,
                'Icon_url' => '',
                'text' => 'Танцы',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            14 => 
            array (
                'id' => 15,
                'Icon_url' => '',
                'text' => 'Машины',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
            15 => 
            array (
                'id' => 16,
                'Icon_url' => '',
                'text' => 'Кулинария',
                'gender' => '',
                'created_at' => '2023-03-28 14:57:12',
                'updated_at' => '2023-03-28 14:57:12',
            ),
        ));
        
        
    }
}