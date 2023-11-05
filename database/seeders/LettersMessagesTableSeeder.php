<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LettersMessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Letters_Messages')->delete();
        
        
        
    }
}