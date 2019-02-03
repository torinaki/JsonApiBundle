<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\AdvancedNamingStrategyInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * JsonApi Deserialization Visitor.
 */
class JsonApiDeserializationVisitor implements DeserializationVisitorInterface
{
    protected $includedResources = [];

    protected $root;

    /** @var JsonDeserializationVisitor */
    private $jsonDeserializationVisitor;

    public function __construct(
        int $options = 0,
        int $depth = 512
    ) {
        $this->jsonDeserializationVisitor = new JsonDeserializationVisitor($options, $depth);
    }

    public function getJsonDeserializationVisitor():JsonDeserializationVisitor
    {
        return $this->jsonDeserializationVisitor;
    }

    public function prepare($data)
    {
        $data = $this->jsonDeserializationVisitor->prepare($data);

        $this->root = $data;

        return $data;
    }

    public function visitProperty(PropertyMetadata $metadata, $data)
    {
        // TODO: implement advanced naming strategy
//        if ($this->namingStrategy instanceof AdvancedNamingStrategyInterface) {
//            $propertyName = $this->namingStrategy->getPropertyName($metadata, $context);
//        } elseif ($this->namingStrategy instanceof PropertyNamingStrategyInterface) {
//            $propertyName = $this->namingStrategy->translateName($metadata);
//        } else {
//            $propertyName = $metadata->name;
//        }

        $propertyName = $metadata->serializedName;

        if ($metadata->name === 'id') {
            if (isset($data['id'])) {
                return $this->jsonDeserializationVisitor->visitProperty(
                    $metadata,
                    $data
                );
            } elseif (isset($data['data'])) {
                return $this->jsonDeserializationVisitor->visitProperty(
                    $metadata,
                    $data['data']
                );
            }
        } elseif (isset($data['data']['relationships'][$propertyName]) ||
            isset($data['relationships'][$propertyName])) { // TODO: add this property
            $included = isset($data['included']) ? $data['included'] : [];

            $visit = false;
            $relationship = [];
            if (array_key_exists('data', $data['data']['relationships'][$propertyName])) {
                $relationship = $data['data']['relationships'][$propertyName]['data'];
                $visit = true;
            } elseif (array_key_exists('data', $data['relationships'][$propertyName])) {
                $relationship = $data['relationships'][$propertyName]['data'];
                $visit = true;
            }

            $relationshipData = [];
            foreach ($included as $include) {
                if ($include['type'] === $relationship['type'] && $include['id'] === $relationship['id']) {
                    $relationshipData = $include;
                    break;
                }
            }

            if (!$relationshipData) {
                $relationshipData = $relationship;
            }

            if ($relationshipData || $visit) {
                return $this->jsonDeserializationVisitor->visitProperty(
                    $metadata,
                    [$propertyName => $relationshipData]
                );
            }
        } elseif (isset($data['data']['attributes'])) {
            return $this->jsonDeserializationVisitor->visitProperty(
                $metadata,
                $data['data']['attributes']
            );
        } elseif (isset($data['attributes'])) {
            return $this->jsonDeserializationVisitor->visitProperty(
                $metadata,
                $data['attributes']
            );
        } else {
            return $this->jsonDeserializationVisitor->visitProperty(
                $metadata,
                []
            );
        }
    }

    /**
     * @param mixed $data
     * @param array $type
     */
    public function visitNull($data, array $type): void
    {
        $this->jsonDeserializationVisitor->visitNull($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitString($data, array $type): string
    {
        return $this->jsonDeserializationVisitor->visitString($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type): bool
    {
        return $this->jsonDeserializationVisitor->visitBoolean($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitDouble($data, array $type): float
    {
        return $this->jsonDeserializationVisitor->visitDouble($data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitInteger($data, array $type): int
    {
        return $this->jsonDeserializationVisitor->visitInteger($data, $type);
    }

    /**
     * Returns the class name based on the type of the discriminator map value
     *
     * @param mixed $data
     */
    public function visitDiscriminatorMapProperty($data, ClassMetadata $metadata): string
    {
        return $this->jsonDeserializationVisitor->visitDiscriminatorMapProperty($data, $metadata);
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitArray($data, array $type): array
    {
        return $this->jsonDeserializationVisitor->visitArray($data, $type);
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param array $type
     */
    public function startVisitingObject(ClassMetadata $metadata, object $data, array $type): void
    {
        $this->jsonDeserializationVisitor->startVisitingObject($metadata, $data, $type);
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param mixed $data
     * @param array $type
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type): object
    {
        return $this->jsonDeserializationVisitor->endVisitingObject($metadata, $data, $type);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function getResult($data)
    {
        return $this->jsonDeserializationVisitor->getResult($data);
    }

    /**
     * Called before serialization/deserialization starts.
     */
    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        $this->jsonDeserializationVisitor->setNavigator($navigator);
    }
}
