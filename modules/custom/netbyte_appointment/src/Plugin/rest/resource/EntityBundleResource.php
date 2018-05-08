<?php
namespace Drupal\netbyte_appointment\Plugin\rest\resource;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\field\Tests\reEnableModuleFieldTest;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\Core\Entity\EntityStorageException;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Provides a resource to get appointment by entity.
 *
 * @RestResource(
 *   id = "entity_bundles",
 *   label = @Translation("Appointment resources"),
 *
 *
 *   serialization_class = "Drupal\node\Entity\Node",
 *   uri_paths = {
 *     "canonical" = "/bundles/appointment/{id}",
 *     "https://www.drupal.org/link-relations/create" = "/entity_bundles/{entity_type}"
 *   }
 * )
 */
class EntityBundleResource extends ResourceBase
{
    /**
     *  A curent user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;
    /**
     *  A instance of entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('rest'),
            $container->get('entity.manager'),
            $container->get('current_user')
        );
    }

    /**
     * Constructs a Drupal\rest\Plugin\ResourceBase object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        EntityManagerInterface $entity_manager,
        AccountProxyInterface $current_user) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->entityManager = $entity_manager;
        $this->currentUser = $current_user;
    }

    /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing a list of bundle names.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */

    /*public function get($year, $month)
    {

        $bundles_entities = $this->loadMonth($year, $month);

        if ($bundles_entities) {
            return new ResourceResponse($bundles_entities);
        }
        throw new NotFoundHttpException();
    }*/

    /**
     * Responds to entity POST requests and saves the new entity.
     *
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function post() {

        $data = \Drupal::request()->getContent();
        $data = json_decode($data);
        //$datetime = $data->field_appointment_date_time;

        $datetime = $this->toUTC( $data->field_appointment_date_time->value);
        //var_dump($data, $datetime); die();
        $values = array(
            'title' => $data->title->value,
            'body' => $data->body->value,
            'field_client' => $data->field_client->target_id,
            'field_appointment_date_time' => $datetime,
            'type' => 'appointment',
            'uid' => $data->field_client->target_id
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

        return new JsonResponse($this->status, $this->status['code']);
    }

    /**
     * Responds to entity DELETE requests.
     *
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function delete($id) {

        //$data = \Drupal::request()->request->query();
        try {
            $user = \Drupal::currentUser()->id();
            $node = Node::load($id);
            if ($node) {
                //$owner = $node->field_client->getValue();
                /*var_dump($owner, $user); die();
                if ($owner == $user) {
                    $node->delete();
                } else {
                    return new ResourceResponse(NULL, 401);
                }*/
                $owner = $this->finduser();

                //var_dump(\Drupal::request()->headers->get('authorization')); die();
                $node->delete();
            } else {
                return new ResourceResponse(NULL, 404);
            }


            return new ResourceResponse(NULL, 204);
        } catch(\Exception $e) {
            return new ResourceResponse(NULL, 500);
        }

        /*if (!$entity->access('delete')) {
            throw new AccessDeniedHttpException();
        }
        try {
            $entity->delete();
            $this->logger->notice('Deleted entity %type with ID %id.', array('%type' => $entity->getEntityTypeId(), '%id' => $entity->id()));

            // Delete responses have an empty body.
            return new ResourceResponse(NULL, 204);
        }
        catch (EntityStorageException $e) {
            throw new HttpException(500, 'Internal Server Error', $e);
        }*/
    }

    private function finduser()
    {
        $token = \Drupal::request()->headers->get('authorization');
        $parts = explode(" ",$token);
        $pass = base64_decode($parts[1]);
        $parts = explode(":",$pass);
        $user = user_load_by_name($parts[0]);
        return $user->id();
        //var_dump($user->id()); die();
    }


    private function toUTC($datetime)
    {
        $given = new \DateTime($datetime);
        $given->setTimezone(new \DateTimeZone("UTC"));

        return $given->format("Y-m-d\TH:i:s");
    }

    protected function validate(EntityInterface $entity) {
        $violations = $entity->validate();

        // Remove violations of inaccessible fields as they cannot stem from our
        // changes.
        $violations->filterByFieldAccess();

        if (count($violations) > 0) {
            $message = "Unprocessable Entity: validation failed.\n";
            foreach ($violations as $violation) {
                $message .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . "\n";
            }
            // Instead of returning a generic 400 response we use the more specific
            // 422 Unprocessable Entity code from RFC 4918. That way clients can
            // distinguish between general syntax errors in bad serializations (code
            // 400) and semantic errors in well-formed requests (code 422).
            throw new HttpException(422, $message);
        }
    }

    private function loadMonth($year, $month)
    {

        $number = $this->days_in_month($month, $year);
        $month = str_pad($month, 2, "0",STR_PAD_LEFT);

        /**
         * @var $query \Drupal\Core\Entity\Query\QueryInterface
         */
        $query = \Drupal::entityQuery('node');
        $query->condition('status', 1)
            ->condition('type','appointment')
            ->condition('field_appointment_date_time', $year."-".$month."-01 00:00:00", ">=")
            ->condition('field_appointment_date_time', $year."-".$month."-".$number." 00:00:00", "<=")
            ->condition('field_client.entity.uid', $this->currentUser->id())
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