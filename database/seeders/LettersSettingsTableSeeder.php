<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LettersSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('Letters_Settings')->delete();
        
        \DB::table('Letters_Settings')->insert(array (
            0 => 
            array (
                'ID' => 1,
                'ReadLetterPrice' => 2,
                'SendLetterPrice' => 0,
                'ViewPhotoPrice' => 2,
                'ViewVideoPrice' => 2,
                'AttachFilePrice' => 2,
            ),
        ));
        
        
    }
}