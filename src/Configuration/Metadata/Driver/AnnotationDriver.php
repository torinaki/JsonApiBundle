<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\AccessType;
use JMS\Serializer\Annotation\Discriminator;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\HandlerCallback;
use JMS\Serializer\Annotation\Inline;
use JMS\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation\PostDeserialize;
use JMS\Serializer\Annotation\PostSerialize;
use JMS\Serializer\Annotation\PreSerialize;
use JMS\Serializer\Annotation\ReadOnly;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Since;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Until;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlAttributeMap;
use JMS\Serializer\Annotation\XmlDiscriminator;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlKeyValuePairs;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlMap;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlValue;
use JMS\Serializer\Exception\InvalidArgumentException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\Driver\AnnotationDriver as JMSAnnotationDriver;
use JMS\Serializer\Metadata\ExpressionPropertyMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Annotation;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Metadata\Driver\DriverInterface;
use Metadata\MethodMetadata;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Annotation driver
     *
     * @var JMSAnnotationDriver
     */
    private $annotationDriver;

    /**
     * @param Reader $reader
     */
    public function __construct(JMSAnnotationDriver $annotationDriver, Reader $reader)
    {
        $this->reader = $reader;
        $this->annotationDriver = $annotationDriver;
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?\Metadata\ClassMetadata
    {
        $propertiesMetadata = [];
        $propertiesAnnotations = [];

        $classMetadata = new ClassMetadata($name = $class->name);
        $parentMetadata = $this->annotationDriver->loadMetadataForClass($class);

        $ref = new \ReflectionClass($parentMetadata);
        foreach ($ref->getProperties() as $property) {
            $name = $property->getName();
            $value = $property->getValue($parentMetadata);
            $classMetadata->$name = $value;
        }

        $excludeAll = false;
        $readOnlyClass = false;

        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Exclude) {
                $excludeAll = true;
            } else {
                if ($annotation instanceof Annotation\Resource) {
                    $classMetadata->setResource(
                        new Resource(
                            $annotation->type,
                            $annotation->showLinkSelf,
                            $annotation->absolute
                        )
                    );
                }
            }
        }

        if (!$excludeAll) {
            foreach ($class->getProperties() as $property) {
                if ($property->class !== $name || (isset($property->info) && $property->info['class'] !== $name)) {
                    continue;
                }
                $propertiesMetadata[] = new PropertyMetadata($name, $property->getName());
                $propertiesAnnotations[] = $this->reader->getPropertyAnnotations($property);
            }

            foreach ($propertiesMetadata as $propertyKey => $propertyMetadata) {
                $propertyMetadata->readOnly = $propertyMetadata->readOnly || $readOnlyClass;

                $propertyAnnotations = $propertiesAnnotations[$propertyKey];

                foreach ($propertyAnnotations as $annotation) {
                    if ($annotation instanceof Annotation\Id) {
                        $classMetadata->setIdField($propertyMetadata->name);
                    } else if ($annotation instanceof Annotation\Relationship) {
                        $propertyMetadata->relationship = true;
                        $classMetadata->addRelationship(
                            new Relationship(
                                $propertyMetadata->name,
                                $annotation->includeByDefault,
                                $annotation->showData,
                                $annotation->showLinkSelf,
                                $annotation->showLinkRelated,
                                $annotation->absolute
                            )
                        );
                    }
                }
            }
        }

        return $classMetadata;
    }
}
