<?php

namespace Drupal\netbyte_login\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
class LoginHandler
{
    private $flood;
    private $em;
    private $userAuth;
    private $userStorage;
    private $tran;
    private $loggerFactory;
    public function __construct()
    {
        $this->flood = \Drupal::getContainer()->get('flood');
        $this->em = \Drupal::getContainer()->get('entity.manager');
        $this->userAuth = \Drupal::getContainer()->get('user.auth');
        $this->userStorage = $this->em->getStorage('user');
        $this->tran =  \Drupal::service('string_translation');
        $this->loggerFactory = \Drupal::getContainer()->get('logger.factory');
    }

    public function login()
    {
        $all = \Drupal::request()->request->all();
        if ($this->validateName($all)) {
            $pass = $all['pass'];

            if (trim($pass) != '') {
                $result = $this->validateAuthentication($all);
                if ($result['code'] != 200) {
                     return new JsonResponse(array('message' => $result['message'], 'code' => $result['code']),  $result['code']);
                } else {
                    $error = $this->validateFinal($result,$all);
                    if (empty( $error) ) {
                        $data['uid'] = $result['state']['uid'];
                        $data['uuid'] = $result['state']['uuid'];

                        $account = $this->userStorage->load($result['state']['uid']);
                        user_login_finalize($account);
                        $sid = \Drupal::service('session')->getId();
                        $data['sid'] = $sid;
                        $data['data'] = $this->loadUserData($account);

                        return new JsonResponse(array('user' => $data));
                    } else {
                        user_logout();
                        return new JsonResponse(array('message' => $error['message']), 401);
                    }
                }
            }
        }
        return new JsonResponse(array('message' => $this->tran->translate('Unrecognized username or password')), 401);
    }

    private function loadUserData($account)
    {
        $data = $account->toArray();
        $temp = array();
        foreach ($data as $key => $value) {
            if (preg_match("%^field%ism", $key, $m)) {
                $temp[$key] = $value;
            }
        }
        $temp['username'] = $account->getUsername();
        return $temp;
    }

    private function validateFinal($result,$all) {
        $flood_config = \Drupal::getContainer()->get('config.factory')->get('user.flood');
        $error = array();
        $flood_control_user_identifier = $result['state']['flood_control_user_identifier'];

        if (!$result['state']['uid']) {
            $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));
            // Register a per-user failed login event.
            if ($flood_control_user_identifier) {
                $this->flood->register('user.failed_login_user', $flood_config->get('user_window'), $flood_control_user_identifier);
            }

            if ($flood_control_triggered = $result['state']['flood_control_triggered']) {
                $error['message'] = $this->tran->translate("Too many failed login attempt");
            } else {
                $error['message'] = $this->tran->translate('Unrecognized username or password');
                $accounts = $this->userStorage->loadByProperties(array('name' => $all['name']));
                if (!empty($accounts)) {
                    $this->loggerFactory->get('user')->notice('Login attempt failed for %user.', array('%user' => $all['name']));
                }
                else {
                    // If the username entered is not a valid user,
                    // only store the IP address.
                    $this->loggerFactory->get('user')->notice('Login attempt failed for %ip.', array('%ip' => \Drupal::request()->getClientIP()));
                    //$this->logger('user')->notice('Login attempt failed from %ip.', array('%ip' => \Drupal::request()->getClientIP()));
                }
            }

        } else if ($flood_control_user_identifier = $result['state']['flood_control_user_identifier']) {
            $this->flood->clear('user.failed_login_user', $flood_control_user_identifier);
        }

        return $error;
    }

    private function validateAuthentication($all)
    {
        $flood_config = \Drupal::getContainer()->get('config.factory')->get('user.flood');
        $state = array();
        // Do not allow any login from the current user's IP if the limit has been
        // reached. Default is 50 failed attempts allowed in one hour. This is
        // independent of the per-user limit to catch attempts from one IP to log
        // in to many different user accounts.  We have a reasonably high limit
        // since there may be only one apparent IP for all users at an institution.
        if (!$this->flood->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
            //$form_state->set('flood_control_triggered', 'ip');
            return array('message' => $this->tran->translate('user failed ip limit.'),
                'code' => 400,'state' => array('flood_control_triggered' => 'ip'));
        }

        $accounts = $this->userStorage->loadByProperties(array('name' => $all['name'], 'status' => 1));
        $account = reset($accounts);

        if ($account) {
            if ($flood_config->get('uid_only')) {
                // Register flood events based on the uid only, so they apply for any
                // IP address. This is the most secure option.
                $identifier = $account->id();
            }
            else {
                // The default identifier is a combination of uid and IP address. This
                // is less secure but more resistant to denial-of-service attacks that
                // could lock out all users with public user names.
                $identifier = $account->id() . '-' . \Drupal::request()->getClientIP();
            }

            $state['flood_control_user_identifier'] = $identifier;

            // Don't allow login if the limit for this user has been reached.
            // Default is to allow 5 failed attempts every 6 hours.
            if (!$this->flood->isAllowed('user.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
                $state['flood_control_triggered'] = 'user';
                return array('message' => $this->tran->translate('user login limit reached.'),'code' => 400,'state' => $state);
            }
        }

        $uid = $this->userAuth->authenticate($all['name'], $all['pass']);
        //var_dump($uid); die();
        if ($uid != false) {
            $state['uid'] = $uid;
            $state['uuid'] = $account->uuid();
            return array('message' => '','code' => 200,'state' => $state);
        } else {
            return array('message' => $this->tran->translate('Unrecognized username or password'),'code'=>401,'state'=>array());
        }

    }

    private function validateName($infor)
    {
        if ($infor['name'] != '' && !user_is_blocked($infor['name']) ) {
            return true;
        } else {
            return false;
        }

    }
}