<?php
namespace Drupal\netbyte_locator\Services;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\Query\QueryFactory;
use \Drupal\node\Entity\Node;

class LocatorService
{
    private $fieldManager;
    private $config;
    private $entityQuery;
    public function __construct(EntityFieldManager $fieldManager,
                               ConfigFactory $config,
                                QueryFactory $entityQuery
                                )
    {
        $this->fieldManager = $fieldManager;
        $this->config = $config;
        $this->entityQuery = $entityQuery;
    }

    public function all()
    {
        $fields = $this->fieldManager->getFieldDefinitions('node', 'locator');
        //var_dump($fields); die();
        //$fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'locator');
        $settings = $fields['field_type']->getSettings();

        $settings['positions'] = $this->allPositions();
        $settings['url'] = $this->getGooleUrl();
        return $settings;
    }

    public function allPositions($type = 'all')
    {
        //$query = \Drupal::entityQuery('node');
        $query = $this->entityQuery->get('node')->condition('status', 1)->condition('type','locator');
        if ($type != 'all') {
            $query->condition('field_type', $type);
        }

        return Node::loadMultiple($query->execute());
    }

    public function getGooleUrl()
    {
        $setting = $this->config->get('netbyte_locator.settings');
        //$config = \Drupal::service('config.factory');
        //$setting = $config->get('netbyte_locator.settings');
        $key = $setting->get('google_api_key');
        $url = $setting->get('map_url');
        $url = $url . "?sensor=false&key=".$key;
        return $url;
    }
}