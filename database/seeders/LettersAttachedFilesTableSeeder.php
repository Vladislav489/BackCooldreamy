<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LettersAttachedFilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Letters_AttachedFiles')->delete();
        
        
        
    }
}