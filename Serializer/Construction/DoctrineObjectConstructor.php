<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer\Construction;

use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\InvalidArgumentException;
use JMS\Serializer\Exception\ObjectConstructionException;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;

/**
 * DoctrineObjectConstructor constructor is a copy of the original JMS constructor with additional logic to
 * convert data to correctly find entity.
 * Note: It not works with composite identifiers.
 *
 * @copyright 2018 OpticsPlanet, Inc
 * @author    Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    const ON_MISSING_NULL = 'null';
    const ON_MISSING_EXCEPTION = 'exception';
    const ON_MISSING_FALLBACK = 'fallback';

    /**
     * @var string
     */
    private $fallbackStrategy;

    private $managerRegistry;
    private $fallbackConstructor;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $managerRegistry Manager registry
     * @param ObjectConstructorInterface $fallbackConstructor Fallback object constructor
     * @param string $fallbackStrategy
     */
    public function __construct(ManagerRegistry $managerRegistry, ObjectConstructorInterface $fallbackConstructor, $fallbackStrategy = self::ON_MISSING_NULL)
    {
        $this->managerRegistry = $managerRegistry;
        $this->fallbackConstructor = $fallbackConstructor;
        $this->fallbackStrategy = $fallbackStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function construct(VisitorInterface $visitor, ClassMetadata $metadata, $data, array $type, DeserializationContext $context)
    {
        $jsonApiData = $data;
        $isJsoApi = false;
        if (array_key_exists('data', $data) && array_key_exists('id', $data['data'])) {
            $isJsoApi = true;
            $data = $jsonApiData['data']['attributes'] ?? [];
        }
        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Locate possible ClassMetadata
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($metadata->name)) {
            // No ClassMetadata found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Managed entity, check for proxy load
        if (!is_array($data)) {
            // Single identifier, load proxy
            return $objectManager->getReference($metadata->name, $data);
        }

        // Fallback to default constructor if missing identifier(s)
        $classMetadata = $objectManager->getClassMetadata($metadata->name);
        if ($isJsoApi) {
            $data[$classMetadata->getIdentifierFieldNames()[0]] = $jsonApiData['data']['id'];
        }
        $identifierList = array();

        foreach ($classMetadata->getIdentifierFieldNames() as $name) {
            if (!array_key_exists($name, $data)) {
                return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            }

            $identifierList[$name] = $data[$name];
        }

        // Entity update, load it from database
        $object = $objectManager->find($metadata->name, $identifierList);

        if (null === $object) {
            switch ($this->fallbackStrategy) {
                case self::ON_MISSING_NULL:
                    return null;
                case self::ON_MISSING_EXCEPTION:
                    throw new ObjectConstructionException(sprintf("Entity %s can not be found", $metadata->name));
                case self::ON_MISSING_FALLBACK:
                    return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
                default:
                    throw new InvalidArgumentException("The provided fallback strategy for the object constructor is not valid");
            }
        }

        $objectManager->initializeObject($object);

        return $object;
    }
}
