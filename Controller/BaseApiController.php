<?php
/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 7/28/17
 * Time: 10:12 AM
 */

namespace conghau\Bundle\ApiResource\Controller;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\User\UserInterface;
use conghau\Bundle\ApiResource\Helper\Helper;

/**
 * Class BaseTCHApiController
 * @package conghau\ApiResource\Controller
 */
class BaseApiController extends Controller
{
    /** @var  UserInterface $loggedUser */
    protected $loggedUser;

    /**
     * createApiResponse
     *
     * @param array   $data
     * @param integer $statusCode
     *
     * @return Response
     */
    public function createApiResponse($data = [], $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response(
            $json,
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * serialize
     *
     * @param array  $data
     * @param string $format
     * @param array  $groups
     *
     * @return mixed|string
     */
    public function serialize($data, $format = 'json', $groups = [])
    {
        if (!empty($groups)) {
            array_push($groups, 'base');

            return $this->container->get('jms_serializer')->serialize(
                $data,
                $format,
                SerializationContext::create()->setGroups($groups)
            );
        }

        return $this->container->get('jms_serializer')->serialize($data, $format);
    }

    /**
     * mapDataOnEntity
     *
     * @param array  $data
     * @param object $targetEntity
     *
     * @return mixed
     */
    public function mapDataOnEntity(array $data, $targetEntity)
    {
        /** @var object $source */
        $sourceEntity = $this->get('jms_serializer')
            ->deserialize(
                json_encode($data),
                get_class($targetEntity),
                'json'
            );

        return $this->fillProperties($data, $targetEntity, $sourceEntity);
    }

    /**
     * fillProperties
     *
     * @param $params
     * @param $targetEntity
     * @param $sourceEntity
     *
     * @return mixed
     */
    protected function fillProperties($params, $targetEntity, $sourceEntity)
    {
        $propertyAccessor = new PropertyAccessor();
        /** @var PropertyMetadata[] $propertyMetadata */
        $propertyMetadata = $this->get('jms_serializer.metadata_factory')
            ->getMetadataForClass(get_class($sourceEntity))
            ->propertyMetadata;
        foreach ($propertyMetadata as $realPropertyName => $data) {
            $serializedPropertyName = $data->serializedName ?: Helper::fromCamelCase($realPropertyName);
            if (array_key_exists($serializedPropertyName, $params)) {
                $newValue = $propertyAccessor->getValue($sourceEntity, $realPropertyName);
                $propertyAccessor->setValue($targetEntity, $realPropertyName, $newValue);
            }
        }

        return $targetEntity;
    }

    /**
     * checkRequireLogin
     *
     * @param bool $isThrowEx
     *
     * @return bool
     * @throws \Exception
     */
    protected function checkRequireLogin($isThrowEx = true)
    {
        $this->loggedUser = $this->getUser();
        if (!empty($this->loggedUser)) {
            return true;
        }
        if ($isThrowEx) {
            throw new \Exception('user_unauthorized');
        }

        return false;
    }

    protected function writeErrorLog(string $controller, string $action, string $errorMessage = '')
    {
        $logger = $this->get('logger');
        if (is_null($logger) || empty($logger)) {
            return;
        }
        /**
         * @var LoggerInterface $logger
         */
        $logger->error(
            json_encode(
                [
                    'controller' => $controller,
                    'action' => $action,
                    'message' => $errorMessage,
                ]
            )
        );
    }


    /**
     * @param string $json
     * @param string $class
     *
     * @return object
     */
    protected function mapJsonToModel(string $json, string $class)
    {
        $mapper = new \JsonMapper();

        return $mapper->map(
            json_decode($json),
            (new \ReflectionClass($class))->newInstance()
        );
    }
}