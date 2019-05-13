<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer\Accessor;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Mango\Bundle\JsonApiBundle\Util\Model\AffectedPropertiesAwareInterface;

/**
 * DefaultAccessorStrategy is an override for original DefaultAccessorStrategy to add
 * custom behavior like track affected properties, etc.
 *
 * @author Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DefaultAccessorStrategy implements AccessorStrategyInterface
{
    /**
     * @var AccessorStrategyInterface
     */
    private $originalAccessorStrategy;

    /**
     * @param AccessorStrategyInterface $originalAccessorStrategy
     */
    public function __construct(AccessorStrategyInterface $originalAccessorStrategy)
    {
        $this->originalAccessorStrategy = $originalAccessorStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void
    {
        $this->originalAccessorStrategy->setValue($object, $value, $metadata, $context);

        if ($object instanceof AffectedPropertiesAwareInterface) {
            $object->addAffectedProperty($metadata->name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(object $object, PropertyMetadata $metadata, SerializationContext $context)
    {
        return $this->originalAccessorStrategy->getValue($object, $metadata, $context);
    }
}
