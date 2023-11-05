<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptReportsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_reports')->delete();
        
        \DB::table('prompt_reports')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Ненастоящий профиль',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            1 => 
            array (
                'id' => 2,
                'text' => 'Грубость или оскорбление',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            2 => 
            array (
                'id' => 3,
                'text' => 'Мошенничество или реклама',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            3 => 
            array (
                'id' => 4,
                'text' => 'Поведение вне PrivetSecret',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            4 => 
            array (
                'id' => 5,
                'text' => 'Несовершеннолетний пользователь',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            5 => 
            array (
                'id' => 6,
                'text' => 'Кому-то грозит опасность',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
            6 => 
            array (
                'id' => 7,
                'text' => 'Этот пользователь мне не нравится',
                'gender' => '',
                'created_at' => '2023-03-28 14:58:20',
                'updated_at' => '2023-03-28 14:58:20',
            ),
        ));
        
        
    }
}