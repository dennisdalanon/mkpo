<?php

namespace Drupal\netbyte_appointment\Services;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\Query\QueryFactory;
use \Drupal\user\Entity\User;
use \Drupal\node\Entity\Node;

class Appointment
{

    private $query;
    public function __construct(QueryFactory $entityQuery)
    {
        $this->query = $entityQuery;
    }

    public function all()
    {

    }

    public function month($userId, $year, $month)
    {
        $month = str_pad($month, 2, "0",STR_PAD_LEFT);
        $number = $this->days_in_month($month, $year);
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','appointment')
            ->condition('field_appointment_date_time', $year."-".$month."-01 00:00:00", ">=")
            ->condition('field_appointment_date_time', $year."-".$month."-".$number." 00:00:00", "<=")
            ->condition('field_client.entity.uid', $userId)
            ->sort('field_appointment_date_time');
        return Node::loadMultiple($query->execute());
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