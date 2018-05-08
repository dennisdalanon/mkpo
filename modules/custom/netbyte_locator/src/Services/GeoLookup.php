<?php

namespace Drupal\netbyte_locator\Services;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory;
class GeoLookup
{
    protected $config;
    protected $http_client;
    public function __construct(ConfigFactory $config, ClientFactory $httpFactory)
    {
        $this->config = $config;
        $this->httpClient = $httpFactory->fromOptions();
    }

    /**
     * @return Array an array consists latitude, longitude
     * ***/
    public function lookup($address)
    {
        $url = $this->getGoogleUrl($address);
        $json = file_get_contents($url);
        $lat = '';
        $lng = '';
        if ($json) {
            $data = json_decode($json);
            if (isset($data->results[0])) {
                $geo = $data->results[0];
                //var_dump($geo->geometry);
                $lat = $geo->geometry->location->lat;
                $lng = $geo->geometry->location->lng;
            }

        }
        return array('lat' => $lat, 'lng' => $lng);
    }

    private function getGoogleUrl($address)
    {
        $setting = $this->config->get('netbyte_locator.settings');
        $key = $setting->get('google_api_key');
        $url = $setting->get('geo_url');
        $url = $url . "json?sensor=false&key=".$key."&address=".urlencode($address);
        return $url;
    }
}