<?php

namespace App\Http\Controllers\API\V1\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LibraryController extends Controller
{
    public function countries()
    {
        $baseUrl = "https://restcountries.com/v3.1/all";
        $data = file_get_contents($baseUrl);
        $countries = json_decode($data, true);

        foreach ($countries as $country) {
            $countryName = Arr::get(Arr::get($country, 'name'), 'common');
            $countryRegions = Arr::get($country, 'subregion');


            print_r($country);

                echo "Страна: {$countryName}, Регион: {$countryRegions}";
                echo "\n";
        }
    }
}
