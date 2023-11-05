<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChatsMessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Chats_Messages')->delete();
        
        
        
    }
}