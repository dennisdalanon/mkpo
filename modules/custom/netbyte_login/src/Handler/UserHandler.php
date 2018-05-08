<?php


namespace Drupal\netbyte_login\Handler;
use \Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserHandler
{
    private $currentUser;
    private $status;
    public function __construct($currentUser)
    {
        $this->currentUser = $currentUser;
        $this->status['message'] = '';
        $this->status['code'] = 200;
        $this->status['data'] = array();
    }

    public function updateProfile($uid, $uuid, $data)
    {
        //TODO: may be we are going to add validation user id later,
        //      But at moment, just update user data via uuid and uid

        //\Drupal::logger('user')->notice('Session opened for %name.', array('%name' => $account->getUsername()));
        $user = User::load($uid);
        if ($user->uuid() == $uuid) {
            $this->updateUser($user, $data);
            \Drupal::logger('user')->notice('User profile been updated for %id.', array('%id' => $uid));
        } else {
            $this->status['message'] = 'Unauthorized access.';
            $this->status['code'] = 401;
            $ip = \Drupal::request()->getClientIp();
            \Drupal::logger('user')->critical('User profile %id Unauthorized access. bad %uuid, from %ip', array('%id' => $uid, '%uuid' => $uuid, '%ip' => $ip));
        }

        return new JsonResponse($this->status);
    }

    private function updateUser($user,$data)
    {
        /** @var \Drupal\user\Entity\User $user */
        if (!$this->validateEmail($data)) {
            $this->status['message'] = 'Invalid e-mail address.';
            $this->status['code'] = 400;

        } else {
            if (isset($data['mail'])) {

                $user->setEmail($data['mail']);
            }
            foreach ($data as $key => $value) {
                //custom fields?
                if( preg_match("%^field%ism", $key, $m) ) {
                    if (isset($user->$key)) {
                        $type = $user->getFieldDefinition($key)->getType();
                        if ($type == 'datetime') {
                            $value = $this->toUTC($value);

                        }
                        $user->$key = $value;
                    }
                }
            }

            $errors = $user->validate();
            if ($errors->count() == 0) {
                $user->save();
                $this->status['message'] = 'User Profile been updated.';
                $this->status['code'] = 200;
            } else {
                $this->status['message'] = 'User Profile updateing service.['.$errors->__toString().']' ;
                $this->status['code'] = 500;
            }
        }

        return new JsonResponse($this->status);
    }

    private function toUTC($date)
    {
        $given = new \DateTime($date . " 00:00:00");
        $given->setTimezone(new \DateTimeZone("UTC"));
        return $given->format("Y-m-d");
    }

    private function validateEmail($data)
    {
        $result = true;
        if (isset($data['email'])) {
            if( !\Drupal::service('email.validator')->isValid($data['mail'])){
                $result = false;
                \Drupal::logger('user')->notice('User profile Invalid e-mail address %email.', array('%id' => $data['mail']));
            }
        }
        return $result;
    }
}