<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/12/2016
 * Time: 10:59 AM
 */

namespace Drupal\netbyte_locator\Handler;
use Symfony\Component\HttpFoundation\JsonResponse;

class FilterHandler
{
    public function content($type)
    {
        $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'locator');
        $settings = $fields['field_type']->getSettings();

        if ( !$this->validateType($settings,$type)) {
            return new JsonResponse([], 400);
        }

        $data = \Drupal::service('netbyte_locator.locatortype')->allPositions($type);

        $temp = [];
        foreach ($data as $key => $item) {
            //var_dump($item->toArray()); die();
            $temp[$key] = array();
            $temp[$key]['lat'] =  $item->field_latitude_->value;
            $temp[$key]['body'] = $item->body->value;
            //field_city_town, field_number_unit, field_ongitude_
            //field_street, field_suburb, field_type
            $temp[$key]['city'] = $item->field_city_town->value;
            $temp[$key]['number'] = $item->field_number_unit->value;
            $temp[$key]['lng'] = $item->field_ongitude_->value;
            $temp[$key]['street'] = $item->field_street->value;
            $temp[$key]['suburb'] = $item->field_suburb->value;
            $temp[$key]['type'] = $item->field_type->value;
            $temp[$key]['country'] = 'new zealand';
            $temp[$key]['title'] = $item->title->value;
            $temp[$key]['phone'] = $item->field_phone->value;
        }


        return new JsonResponse($temp);
    }

    private function validateType($validSettings, $type)
    {
        $good = array_keys($validSettings['allowed_values']);
        $good[] = 'all';
        $b = true;

        if (!in_array($type, $good)) {
            $b = false;
        }
        return $b;
    }
}