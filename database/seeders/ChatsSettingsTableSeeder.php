<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChatsSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Chats_Settings')->delete();
        
        \DB::table('Chats_Settings')->insert(array (
            0 => 
            array (
                'ID' => 1,
                'SendMessagePrice' => 2,
                'SendStickerPrice' => 2,
                'SendFilePrice' => 2,
            ),
        ));
        
        
    }
}