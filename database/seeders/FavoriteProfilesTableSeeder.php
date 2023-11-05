<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FavoriteProfilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('favorite_profiles')->delete();
        
        
        
    }
}