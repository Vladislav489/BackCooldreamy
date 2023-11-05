<?php

namespace App\Repositories\Geo;

use App\Models\UserGeo;

class UserGeoRepository
{
    /**
     * @param array $data
     * @return UserGeo
     */
    public function store(array $data = []): UserGeo
    {
        return UserGeo::create(
            $data
        );
    }
}
