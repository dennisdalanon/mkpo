<?php
namespace Drupal\netbyte_locator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

use Drupal\netbyte_locator\Handler\GeoipHandler;
use Drupal\netbyte_locator\Handler\FilterHandler;

class LocatorController extends ControllerBase
{
    public function content($type)
    {
        $handler = new FilterHandler();
        return $handler->content($type);
    }

    public function geoip()
    {
        $data = \Drupal::request()->request->all();
        $handler = new GeoipHandler();
        return $handler->getGeoInformation($data);
    }
}