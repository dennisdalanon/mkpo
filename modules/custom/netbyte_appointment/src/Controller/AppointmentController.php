<?php
namespace Drupal\netbyte_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\netbyte_appointment\Handler\Appointment;
use \Drupal\user\Entity\User;
class AppointmentController extends ControllerBase
{
    public function month($year,$month, $uid)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        return $infor = $handler->month($year,$month, $uid);
    }

    public function year($year, $uid)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        $infor = $handler->year($year,$uid);
    }

    public function createAppointment($uid, $uuid)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        $data = \Drupal::request()->request->all();
        return $handler->createAppointment($data, $uid, $uuid);
    }

    public function updateAppointment($uid, $uuid)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        $data = \Drupal::request()->request->all();
        //return $handler->updateAppointment($data, $uid, $uuid);
    }

    public function deleteAppointment($uid, $uuid)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        $data = \Drupal::request()->request->all();
        return $handler->deleteAppointment($data, $uid, $uuid);
    }

    public function fetchMonth($year, $month)
    {
        $handler = new Appointment(User::load(\Drupal::currentUser()->id()));
        return $handler->fetchMonth($year, $month);
    }
}