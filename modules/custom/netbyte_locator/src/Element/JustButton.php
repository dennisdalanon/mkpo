<?php


namespace Drupal\netbyte_locator\Element;

use Drupal\Core\Render\Element\Button;
use Drupal\Core\Render\Element;

/**
 * Provides an example element.
 *
 * @FormElement("just_button")
 */

class JustButton extends Button
{
    public static function preRenderButton($element) {

        $element['#attributes']['type'] = 'button';
        Element::setAttributes($element, array('id', 'name', 'value'));

        $element['#attributes']['class'][] = 'button';
        if (!empty($element['#button_type'])) {
            $element['#attributes']['class'][] = 'button--' . $element['#button_type'];
        }
        $element['#attributes']['class'][] = 'js-form-submit';
        $element['#attributes']['class'][] = 'form-submit';

        if (!empty($element['#attributes']['disabled'])) {
            $element['#attributes']['class'][] = 'is-disabled';
        }

        return $element;
    }
}