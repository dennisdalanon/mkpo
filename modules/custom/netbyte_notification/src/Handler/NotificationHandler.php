<?php

namespace Drupal\netbyte_notification\Handler;

use \Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationHandler
{
    private $status;
    private $account;

    public function __construct($user)
    {
        $this->status = array();
        $this->account = $user;
    }

    public function fetch($uuid)
    {
        if ($this->isValidUser( $uuid)) {
            $this->calculateFireDate();
            return new JsonResponse($this->status['data'],$this->status['code']);
        }
    }

    private function isValidUser($uuid)
    {
        $flag = true;

        if ( $this->account ) {
            if ($this->account->uuid() != $uuid) {
                $this->status['data'] = "Unauthorized access.";
                $this->status['code'] = 401;
                $flag = false;
            } else {
                $this->status['data'] = "allowed.";
                $this->status['code'] = 200;
            }
        } else {
            $this->status['data'] = "Unauthorized access.";
            $this->status['code'] = 401;
            $flag = false;
        }

        return $flag;
    }

    private function loadingDueDate()
    {
        return $this->account->field_due_date->value;
    }


    private function loadingSignupDate()
    {
        $d = date('Y-m-d', $this->account->created->value);
        return $d;
    }

    protected function howManyDayFromSignupDayToDueDay()
    {
        $due = $this->loadingDueDate();
        $signup = $this->loadingSignupDate();
        $date1 = new DateTime($due);
        $date2 = new DateTime($signup);

        return $date1->diff($date2)->format("%a");
    }

    protected function calculateFireDate()
    {
        $notifications = $this->loadNotifications();
        $this->status['data'] = array();
        if($notifications) {
            foreach($notifications as $notification) {
                $date = $this->fireupDate($notification);
                $temp = $notification->toArray();
                $temp['fire'] = $date;
                $this->status['data'][] = $temp;
            }
        }
    }


    private function fireupDate($notification)
    {
        $dueDate = $this->loadingDueDate();
        $countDown = $notification->field_count_down_days->value;

        $date1 = new \DateTime($dueDate);
        $interval = new \DateInterval("P".$countDown."D");
        $countDownDate = $date1->sub($interval);

        $date = $countDownDate->format('Y-m-d');
        return $date;
    }

    private function loadNotifications()
    {
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','notification')
            ->sort('field_count_down_days', 'DES');
        return Node::loadMultiple($query->execute());
    }
}