<?php
/**
 * @file
 * Contains \Drupal\netbyte_locator\Form\LocatorSettingForm
 */
namespace Drupal\netbyte_locator\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LocatorSettingForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'netbyte_locator_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return array(
            'netbyte_locator.settings',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('netbyte_locator.settings');

        $form['netbyte_locator_settings_google_api_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Google API Key'),
            '#default_value' => $config->get('google_api_key'),
        );

        $form['netbyte_locator_settings_google_geo_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Google Geo API url'),
            '#default_value' => $config->get('geo_url'),
        );

        $form['netbyte_locator_settings_google_map_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Google Geo API url'),
            '#default_value' => $config->get('map_url'),
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('netbyte_locator.settings')
            ->set('google_api_key', $form_state->getValue('netbyte_locator_settings_google_api_key'))
            ->set('geo_url', $form_state->getValue('netbyte_locator_settings_google_geo_url'))
            ->set('map_url', $form_state->getValue('netbyte_locator_settings_google_map_url'))
            ->save();

        parent::submitForm($form, $form_state);
    }
}