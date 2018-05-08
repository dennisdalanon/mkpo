<?php

namespace Drupal\netbyte_locator\Handler;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
class GeoipHandler
{
    public function getGeoInformation($data)
    {
        $address = $this->getAddress($data);
        $data = \Drupal::service('netbyte_locator.geoip')->lookup($address);
        $ajax_response = new AjaxResponse();

        $lat = new InvokeCommand('#edit-field-latitude-0-value',
          'val' , array($data['lat']));

        $lng = new InvokeCommand('#edit-field-ongitude-0-value',
          'val' , array($data['lng']));

        $ajax_response->addCommand($lat);
        return $ajax_response->addCommand($lng);
    }

    private function getAddress($data)
    {
        $number = $this->getFieldvalue('field_number_unit',$data);
        $street = $this->getFieldvalue('field_street',$data);
        $suburb = $this->getFieldvalue('field_suburb',$data);
        $city = $this->getFieldvalue('field_city_town',$data);

        return $number . " " . $street . ", " . $suburb . ", ".$city . ", New Zealand";
    }

    private function getFieldvalue($fieldName,$data)
    {
        if (isset($data[$fieldName][0]['value'])) {
            return trim( $data[$fieldName][0]['value']);
        }

        return '';
    }
}