<?php

namespace App\Services\Geo;

class GeoRequest
{
    public string $userIp = '';
    public string $city = 'unknown';
    public string $state = 'unknown';
    public string $country = 'unknown';
    public string $countryCode = 'unknown';
    public string $continent = 'unknown';
    public string $continentCode = 'unknown';

    /**
     * Получение инфы по ip
     *
     * @return $this
     */
    public function infoByIp(): self
    {
        if (filter_var($this->userIp, FILTER_VALIDATE_IP) === false) {
            $this->userIp = $_SERVER["REMOTE_ADDR"];
        }

        if ($this->userIp == '127.0.0.1') {
            $this->city = $this->state = $this->country = $this->countryCode = $this->continent = $this->countryCode = 'local machine';
        }

        if (filter_var($this->userIp, FILTER_VALIDATE_IP)) {
            $ipData = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $this->userIp));

            if (strlen(trim($ipData->geoplugin_countryCode)) == 2) {
                $this->city = $ipData->geoplugin_city;
                $this->state = $ipData->geoplugin_regionName;
                $this->country = $ipData->geoplugin_countryName;
                $this->countryCode = $ipData->geoplugin_countryCode;
                $this->continent = $ipData->geoplugin_continentName;
                $this->continentCode = $ipData->geoplugin_continentCode;
            }

        }

        return $this;
    }

    /**
     * @return $this
     */
    public function getIp(): self
    {

        if (getenv('HTTP_CLIENT_IP')) {
            $this->userIp = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $this->userIp = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $this->userIp = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $this->userIp = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $this->userIp = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $this->userIp = getenv('REMOTE_ADDR');
        } else {
            $this->userIp = 'UNKNOWN';
        }

        return $this;
    }
}
