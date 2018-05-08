<?php

namespace Drupal\netbyte_locator\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\netbyte_locator\Services\LocatorService;
/**

 * Provides a block for location map

 *

 * @Block(

 * id = "netbyte_locator_block",

 * admin_label = @Translation("Location map"),

 * )

 */
class LocatorBlock extends BlockBase
{
    /**

     * {@inheritdoc}

     */

    public function build() {

        // External Uri.

        /*$url = Url::fromUri('http://www.commercialprogression.com');

        $external_link = \Drupal::l(t('Drupal website design'), $url);
        */
        // Render array that returns link and text for block content.
        /*$service = new LocatorService();
        $allowedvalues = $service->all();
        */
        $allowedvalues = \Drupal::service("netbyte_locator.locatortype")->all();
        return array(

            'locator_map' => array(

                '#theme' => 'content',
                '#locator_types' => $allowedvalues,
                '#attached' => array(
                    'library' =>  array(
                        'netbyte_locator/google_map_api',
                        'netbyte_locator/google_makercluster_min',
                        'netbyte_locator/netbyte_locator_js',
                        'netbyte_locator/netbyte_locator_css',
                    ),
                ),

            ),

        );

    }
}