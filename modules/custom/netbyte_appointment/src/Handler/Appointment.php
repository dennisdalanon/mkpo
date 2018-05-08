<?php
namespace Drupal\netbyte_appointment\Handler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Entity;
class Appointment
{
    private $user;
    private $status;
    public function __construct($currentUser)
    {
        $this->user = $currentUser;
        $this->status = array('code'=>[],'message'=>[],'data'=>[]);
    }

    public function month($year,$month, $uid)
    {
        if ($this->user->id() != $uid) {
            $this->status['code'] = 403;
            $this->status['message'] = "Unauthorized access.";
        } else {

            $this->status['data'] = $this->loadMonth($year, $month, $uid);
            $this->status['code'] = 200;
            $this->status['message'] = "";

        }


        return new JsonResponse($this->status);
    }

    public function fetchMonth($year, $month)
    {
        //$data = $this->getCalendar();
        $user = \Drupal::currentUser();
        $allowedvalues = \Drupal::service("netbyte_appointment.appointment")->month($user->id(),$year, $month );
        $data = [];
        foreach ($allowedvalues as $item) {
            $temp = [];
            $temp['title'] = $item->title->value;
            $temp['body'] = $item->body->value;

            $date = new \DateTime($item->field_appointment_date_time->value);
            $temp['start'] = $date->format("m/d/Y h:i A"). " UTC";
            $temp['url'] = $item->toUrl()->toString();
            $data[] = $temp;
        }

        $this->status['data'] = $data;
        $this->status['code'] = 200;
        $this->status['message'] = '';
        return new JsonResponse($this->status);

    }

    public function year($year, $uid)
    {
        if ($this->user->id() != $uid) {
            $this->status['code'] = 401;
            $this->status['message'] = "Unauthorized access.";
        } else {

            $this->status['data'] = $this->loadYear($year, $uid);
            $this->status['code'] = 200;
            $this->status['message'] = "";
        }

        return new JsonResponse($this->status);
    }

    public function deleteAppointment($data, $uid, $uuid)
    {
        $user = \Drupal\user\Entity\User::load($uid);
        if ($user->uuid() != $uuid || !isset($data['appointment_id'])) {
            $this->status['code'] = 401;
            $this->status['message'] = "Unauthorized access.";
        } else {

            try {
                /** @var \Drupal\node\Entity\Node $node */
                $node = Node::load($data['appointment_id']);
                if ($node) {
                    $node->delete();
                    $this->status['message'] = 'An appointment has been deleted.';
                    $this->status['code'] = 200;
                } else {
                    $this->status['code'] = 400;
                    $this->status['message'] = "Resource (appointment id) is not exists.";
                }
            } catch (\Exception $e) {
                $this->status['code'] = 500;
                $this->status['message'] = "Appointment deleting process has an error";
                return new JsonResponse($this->status, $this->status['code']);
            }
        }

        return new JsonResponse($this->status, $this->status['code']);
    }

    public function updateAppointment($data, $uid, $uuid)
    {
        $user = \Drupal\user\Entity\User::load($uid);
        if ($user->uuid() != $uuid || !isset($data['appointment_id'])) {
            $this->status['code'] = 401;
            $this->status['message'] = "Unauthorized access.";
        } else {
            $query = \Drupal::entityQuery('node');
            $query->condition('status', 1)
                ->condition('type','appointment')
                ->condition('field_client.entity.uid', $uid)
                ->condition('uid', $data['appointment_id'])
                ->sort('field_appointment_date_time');

            $results = Node::loadMultiple($query->execute());

            if (count($results) > 0) {
                /** @var \Drupal\node\Entity\Node $appointment */
                $datetime = $this->toUTC($data['datetime']);
                $appointment = reset($results);
                $appointment->title = $data['title'];
                $appointment->body = $data['body'];
                $appointment->field_client = $uid;
                $appointment->field_appointment_date_time = $datetime;
                $errors = $appointment->validate();

                if ($errors->count() == 0) {
                    $appointment->save();
                    $this->status['message'] = 'An appointment has been updated.';
                    $this->status['code'] = 200;
                    $this->status['data'] = ['appointment' =>
                        ['id' => $appointment->id(),
                            'uuid' => $appointment->uuid()]];
                } else {
                    $this->status['message'] = 'Appointment updating process has an error.['.$errors->__toString().']' ;
                    $this->status['code'] = 500;
                }

            }
        }

        return new JsonResponse($this->status);
    }


    public function createAppointment($data, $uid, $uuid)
    {

        /** @var \Drupal\user\Entity\User $user **/
        $user = \Drupal\user\Entity\User::load($uid);

        if ($user->uuid() != $uuid) {
            $this->status['code'] = 401;
            $this->status['message'] = "Unauthorized access.";
        } else {
            $datetime = $this->toUTC($data['field_appointment_date_time']);

            $values = array(
                'title' => $data['title'],
                'body' => $data['body'],
                'field_client' => $uid,
                'field_appointment_date_time' => $datetime,
                'type' => 'appointment'
            );

            $appointment = Node::create($values);

            /** @var \Drupal\node\Entity\Node $appointment */
            $errors = $appointment->validate();
            if ($errors->count() == 0) {
                $appointment->save();
                $this->status['message'] = 'An appointment has been created.';
                $this->status['code'] = 201;
                $this->status['data'] = ['appointment' =>
                    ['id' => $appointment->id(),
                    'uuid' => $appointment->uuid()]];


            } else {
                $this->status['message'] = 'Appointment creation has an error.['.$errors->__toString().']' ;
                $this->status['code'] = 500;
            }

        }

        return new JsonResponse($this->status, $this->status['code']);
    }

    private function toUTC($datetime)
    {
        $given = new \DateTime($datetime);
        $given->setTimezone(new \DateTimeZone("UTC"));

        return $given->format("Y-m-d\TH:i:s");
    }

    private function toLocal($utc)
    {
        $timezone = drupal_get_user_timezone();
        $given = new \DateTime($utc, new \DateTimeZone("UTC"));
        $given->setTimezone($timezone);
        return $given->format("Y-m-d H:i:s");
    }

    private function loadYear($year, $uid)
    {
        $number = $this->days_in_month(12, $year);
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','appointment')
            ->condition('field_appointment_date_time', $year."-01-01 00:00:00", ">=")
            ->condition('field_appointment_date_time', $year."-12-".$number." 00:00:00", "<=")
            ->condition('field_client.entity.uid', $uid)
            ->sort('field_appointment_date_time');
        return Node::loadMultiple($query->execute());
    }

    private function loadMonth($year, $month,$uid)
    {
        $number = $this->days_in_month($month, $year);
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','appointment')
            ->condition('field_appointment_date_time', $year."-".$month."-01 00:00:00", ">=")
            ->condition('field_appointment_date_time', $year."-".$month."-".$number." 00:00:00", "<=")
            ->condition('field_client.entity.uid', $uid)
            ->sort('field_appointment_date_time');
        return Node::loadMultiple($query->execute());
    }

    private function getCalendar()
    {
        $now = new \DateTime('now');
        $month = $now->format('m');
        $year = $now->format('Y');
        return ['year' => $year, 'month' => $month];
    }

    /*
    * days_in_month($month, $year)
    * Returns the number of days in a given month and year, taking into account leap years.
    *
    * $month: numeric month (integers 1-12)
    * $year: numeric year (any integer)
    *
    * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer.
    * Post: none
    */
    // corrected by ben at sparkyb dot net
    //http://php.net/manual/en/function.cal-days-in-month.php
    function days_in_month($month, $year)
    {
        // calculate number of days in a month
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }
}