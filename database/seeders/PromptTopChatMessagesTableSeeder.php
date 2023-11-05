<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptTopChatMessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_top_chat_messages')->delete();
        
        \DB::table('prompt_top_chat_messages')->insert(array (
            0 => 
            array (
                'Id' => 1,
                'text' => 'Привет, познакомимся?',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:59:23',
                'updated_at' => '2023-03-28 14:59:23',
            ),
            1 => 
            array (
                'Id' => 2,
                'text' => 'Каких отношений ты ищешь на этом сайте?',
                'gender' => 'Male',
                'created_at' => '2023-03-28 14:59:23',
                'updated_at' => '2023-03-28 14:59:23',
            ),
            2 => 
            array (
                'Id' => 3,
            'text' => 'Привет, тебе уже говорили, что ты сногшибательна?)',
            'gender' => 'Male',
            'created_at' => '2023-03-28 14:59:23',
            'updated_at' => '2023-03-28 14:59:23',
        ),
        3 => 
        array (
            'Id' => 4,
            'text' => 'Здравствуй! Отличные фотографии!',
            'gender' => 'Male',
            'created_at' => '2023-03-28 14:59:23',
            'updated_at' => '2023-03-28 14:59:23',
        ),
        4 => 
        array (
            'Id' => 5,
            'text' => 'У тебя великолепная внешность! Я поражен',
            'gender' => 'Male',
            'created_at' => '2023-03-28 14:59:23',
            'updated_at' => '2023-03-28 14:59:23',
        ),
    ));
        
        
    }
}