<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserDataTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('UserData')->delete();
        
        
        
    }
}