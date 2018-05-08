<?php
namespace Drupal\netbyte_appointment\Plugin\Block;
use Drupal\Core\Block\BlockBase;
/**

 * Provides a block for appointment

 *

 * @Block(

 * id = "netbyte_appointment_block",

 * admin_label = @Translation("Appointment Calendar"),

 * )

 */
class AppointmentBlock extends BlockBase
{
    /**

     * {@inheritdoc}

     */

    public function build() {

        $data = array('name' => 'anru', 'hello' => 'world', 'd8' => 'dev');
        $user = \Drupal::currentUser();
        $data = $this->getCalendar();
        $allowedvalues = \Drupal::service("netbyte_appointment.appointment")->month($user->id(),$data['year'], $data['month'] );

        return array(

            'appointment' => array(
                //'#theme' => 'netbyte_appointment',
                '#theme' => 'calendar',
                '#events' => $allowedvalues,
                '#attached' => array(
                    'library' =>  array(
                        'netbyte_appointment/fullcalendar',
                        'netbyte_appointment/netbyte_appointment_js'
                    ),
                ),

                '#cache' => array(
                    'max-age' => 0,
                ),

            ),

        );

    }

    private function getCalendar()
    {
        $now = new \DateTime('now');
        $month = $now->format('m');
        $year = $now->format('Y');
        return ['year' => $year, 'month' => $month];
    }
}