<?php

namespace Drupal\netbyte_login\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\netbyte_login\Handler\LoginHandler;
use Drupal\netbyte_login\Handler\UserHandler;

use Drupal\Core\Form\FormState;
use Drupal\user\RegisterForm;
use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerResolver;

class LoginController extends ControllerBase
{
    public function login()
    {
        $handler = new LoginHandler();
        return $handler->login();
    }

    public function logout()
    {
        \user_logout();
        return new JsonResponse(array('message' => 'logout'));
    }

    public function profile($uid, $uuid)
    {
        $handler = new UserHandler(User::load(\Drupal::currentUser()->id()));
        $data = \Drupal::request()->request->all();
        return $handler->updateProfile($uid, $uuid, $data);
    }

    public function registration()
    {
        $values = \Drupal::request()->request->all();


        /*$form_state = new FormState();

        $args = func_get_args();
        // Remove $form_arg from the arguments.
        unset($args[0]);
        $form_state->addBuildInfo('args', array_values($args));

        return $this->buildForm($form_arg, $form_state);
        */
        //$form_state = (new FormState())->setValues($values);
        /*$builder = \Drupal::formBuilder();
        $form = $builder->buildForm('Drupal\user\RegisterForm',new FormState());
        var_dump($form); die();
        //$builder->buildForm("");*/

        /*$formBuilder = \Drupal::service('entity.form_builder');
        $form = $formBuilder->getForm(User::create(array()), 'register', array());
        $formObject = new RegisterForm(\Drupal::entityManager(),
            \Drupal::languageManager(),\Drupal::service('entity.query'));

        $form_state = (new FormState())->setValues($values);

        //$form_object = \Drupal::entityManager()->getFormObject('user', 'register');
        $entity = \Drupal::entityManager()->getStorage('user')->create([]);
        $formObject->setEntity($entity);
        //$formObject->form($form,$form_state);
        $entity = $formObject->buildEntity($form,$form_state);
        $formObject->setEntity($entity);
        $formObject->form($form,$form_state);
        $formObject->submitForm($form,$form_state);
        $formObject->save($form,$form_state);*/


        //var_dump($entity); die();

        /*$form_object = \Drupal::entityManager()->getFormObject('user', 'register');
        $entity = \Drupal::entityManager()->getStorage('user')->create([]);
        $form_object->setEntity($entity);*/
        //$form_object->form($form,$form_state);
        //var_dump($form_object);die();
        // Allow the entity form to determine the entity object from a given route
        // match.
        //$entity = $form_object->getEntityFromRouteMatch($route_match, $entity_type_id);

        //return $this->entityFormBuilder->getForm( Person::create(array()), 'default', array());

        //$builder->submitForm('Drupal\user\RegisterForm', $form_state);

        /*$form_object = \Drupal::entityManager()->getFormObject('user', 'register');
        $entity = \Drupal::entityManager()->getStorage('user')->create([]);
        $form_object->setEntity($entity);
        $formBuilder = \Drupal::service('entity.form_builder');
        $form = $formBuilder->getForm(User::create(array()), 'register', array());
        $form_state = (new FormState())->setValues($values);

        $form_object->submitForm($form,$form_state);
        //$form_object->form($form,$form_state);
        var_dump('ddd');die();
        */

        /*$builder = \Drupal::formBuilder();

        $form_object = \Drupal::entityManager()->getFormObject('user', 'register');
        $form_state = new FormState();
        $form_state->setUserInput($values);
        $form_state->disableRedirect();
        $form_id = 'user_register_form';

        $form = $builder->retrieveForm($form_id, $form_state);
        $builder->prepareForm($form_id, $form, $form_state);

        $response = $builder->processForm($form_id, $form, $form_state);
        //$builder->buildForm($form_object, $form_state);
        var_dump($response);die();*/

        //$formBuilder = $builder = \Drupal::formBuilder();
        //var_dump($formBuilder); die();
        //$form = $formBuilder->getForm(User::create(array()), 'register', array());
        //$form_state = (new FormState())->setValues($values);
        //\drupal_form_submit('user_register_form', $form_state);

        //$re = $formBuilder->submitForm('Drupal\user\RegisterForm',$form_state);
        //var_dump();die();
        return new JsonResponse(array('message' => 'Successfully registered a user '));
    }
}