<?php
namespace Drupal\netbyte_notification\Controller;

use \Drupal\user\Entity\User;
use Drupal\netbyte_notification\Handler;

class NotificationController
{
    public function notification($id, $uuid)
    {
        $handler = new Handler\NotificationHandler(User::load($id));
        return $handler->fetch($uuid);
    }

    public function target($targetId, $notificationId)
    {
        $handler = new Handler\NotificationTargetHandler();
        return $handler->target($targetId, $notificationId);
    }
}