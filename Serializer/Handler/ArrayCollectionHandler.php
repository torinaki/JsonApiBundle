<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\ArrayCollectionHandler as BaseArrayCollectionHandler;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * ArrayCollectionHandler handler to add the same handlers for ArrayCollection in json:api format as for json format
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class ArrayCollectionHandler implements SubscribingHandlerInterface
{
    /** @var BaseArrayCollectionHandler  */
    private $arrayCollectionHandler;

    public function __construct(BaseArrayCollectionHandler $arrayCollectionHandler)
    {
        $this->arrayCollectionHandler = $arrayCollectionHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = BaseArrayCollectionHandler::getSubscribingMethods();
        $additionalMethods = [];
        foreach ($methods as $method) {
            if ($method['format'] === 'json') {
                $method['format'] = MangoJsonApiBundle::FORMAT;
                $additionalMethods[] = $method;
            }
        }
        return array_merge($methods, $additionalMethods);
    }


    /**
     * @return array|\ArrayObject
     */
    public function serializeCollection(SerializationVisitorInterface $visitor, Collection $collection, array $type, SerializationContext $context)
    {
        return $this->arrayCollectionHandler->serializeCollection($visitor, $collection, $type, $context);
    }

    /**
     * @param mixed $data
     */
    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type, DeserializationContext $context): ArrayCollection
    {
        return $this->arrayCollectionHandler->deserializeCollection($visitor, $data, $type, $context);
    }
}
