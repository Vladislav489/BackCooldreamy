<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PromptChatMessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('prompt_chat_messages')->delete();
        
        \DB::table('prompt_chat_messages')->insert(array (
            0 => 
            array (
                'id' => 1,
                'text' => 'Привет. Как насчет знакомства?',
                'gender' => '',
                'created_at' => '2023-03-28 14:56:21',
                'updated_at' => '2023-03-28 14:56:21',
            ),
            1 => 
            array (
                'id' => 2,
            'text' => 'Привет. Наткнулся на тебя случайно и не мог упустить шанс познакомиться :)',
            'gender' => 'Male',
            'created_at' => '2023-03-28 14:56:21',
            'updated_at' => '2023-03-28 14:56:21',
        ),
        2 => 
        array (
            'id' => 3,
        'text' => 'Привет. Наткнулась на тебя случайно и не могла упустить шанс познакомиться :)',
        'gender' => 'Female',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
    3 => 
    array (
        'id' => 4,
        'text' => 'У тебя очень интересный профиль... Можешь рассказать о себе побольше?',
        'gender' => '',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
    4 => 
    array (
        'id' => 5,
        'text' => 'Здравствуйте. Вы очень красивая. Пообщаемся?',
        'gender' => 'Male',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
    5 => 
    array (
        'id' => 6,
        'text' => 'Здравствуйте. Вы очень красивый. Пообщаемся?',
        'gender' => 'Female',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
    6 => 
    array (
        'id' => 7,
        'text' => 'Привет. Хотел бы познакомиться с такой классной девушкой как ты!',
        'gender' => 'Male',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
    7 => 
    array (
        'id' => 8,
        'text' => 'Привет. Хотела бы познакомиться с таким классным парнем как ты!',
        'gender' => 'Female',
        'created_at' => '2023-03-28 14:56:21',
        'updated_at' => '2023-03-28 14:56:21',
    ),
));
        
        
    }
}