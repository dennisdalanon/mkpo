<?php

namespace Drupal\netbyte_notification\Handler;
use \Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
class NotificationTargetHandler
{
    private $status;

    public function __construct()
    {
        $this->status = array();
        $this->status['message'] = array();
        $this->status['data'] = array();
        $this->status['code'] = 200;
    }

    public function target($targetId, $notificationId)
    {
        $notification = $this->loadNotification($notificationId);

        if (count($notification) == 1) {
            $node = reset($notification);
            $targetIds = $node->field_destination->getValue();
            if ($targetIds) {
                foreach ($targetIds as $tid) {
                    $id = $tid['target_id'];
                    if ($id == $targetId) {
                        $this->status['data'][] = Node::load($id)->toArray();
                        $this->status['code'] = 200;
                        break;
                    }
                }
            }

        } else {
            $this->status['message'] = 'Object not found.';
            $this->status['code'] = 400;
        }

        return new JsonResponse($this->status, $this->status['code']);
        //var_dump($this->status['data']); die();

    }

    private function loadNotification($notificationId)
    {

        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','notification')
            ->condition('nid', $notificationId);
        //var_dump(Node::load($notificationId)); die();
        return Node::loadMultiple($query->execute());
    }
}