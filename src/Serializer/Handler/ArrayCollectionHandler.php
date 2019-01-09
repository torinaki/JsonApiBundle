<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

/**
 * ArrayCollectionHandler handler to add the same handlers for ArrayCollection in json:api format as for json format
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class ArrayCollectionHandler implements SubscribingHandlerInterface
{
    /**
     * Handler
     *
     * @var \JMS\Serializer\Handler\ArrayCollectionHandler
     */
    private $handler;

    /**
     * ArrayCollectionHandler constructor.
     *
     * @param \JMS\Serializer\Handler\ArrayCollectionHandler $handler
     */
    public function __construct(\JMS\Serializer\Handler\ArrayCollectionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $format = MangoJsonApiBundle::FORMAT;
        $collectionTypes = [
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection',
        ];

        foreach ($collectionTypes as $type) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type'      => $type,
                'format'    => $format,
                'method'    => 'serializeCollection',
            ];

            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'type'      => $type,
                'format'    => $format,
                'method'    => 'deserializeCollection',
            ];
        }

        return $methods;
    }

    public function serializeCollection(SerializationVisitorInterface $visitor, Collection $collection, array $type, SerializationContext $context)
    {
        return $this->handler->serializeCollection(...\func_get_args());
    }

    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context): ArrayCollection
    {
        return $this->handler->deserializeCollection(...\func_get_args());
    }
}
