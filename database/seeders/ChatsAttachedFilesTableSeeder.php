<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChatsAttachedFilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Chats_AttachedFiles')->delete();
        
        
        
    }
}