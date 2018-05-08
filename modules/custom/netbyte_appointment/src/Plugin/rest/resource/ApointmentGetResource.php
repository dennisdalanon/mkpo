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
 *   id = "appointment_month",
 *   label = @Translation("Appointment get month resources"),
 *
 *   uri_paths = {
 *     "canonical" = "/bundles/appointment/year/{year}/month/{month}",
 *   }
 * )
 */
class ApointmentGetResource extends ResourceBase
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

    public function get($year, $month)
    {

        $bundles_entities = $this->loadMonth($year, $month);

        if ($bundles_entities) {
            return new ResourceResponse($bundles_entities);
        }
        throw new NotFoundHttpException();
    }


    private function toUTC($datetime)
    {
        $given = new \DateTime($datetime);
        $given->setTimezone(new \DateTimeZone("UTC"));

        return $given->format("Y-m-d\TH:i:s");
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
        //var_dump($this->currentUser->id()); die();
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