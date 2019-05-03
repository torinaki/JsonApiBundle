<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Expression\CompilableExpressionEvaluatorInterface;
use JMS\Serializer\Metadata\ClassMetadata as JmsClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\Type\ParserInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Annotation;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Metadata\ClassMetadata;
use JMS\Serializer\Metadata\Driver\AnnotationDriver as JmsAnnotationDriver;

/**
 * Copy pasted and adjusted from: https://github.com/schmittjoh/serializer/blob/master/src/Metadata/Driver/AnnotationDriver.php
 */
class AnnotationDriver extends JmsAnnotationDriver
{
    use ConverterTrait;

    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader, PropertyNamingStrategyInterface $namingStrategy, ?ParserInterface $typeParser = null, ?CompilableExpressionEvaluatorInterface $expressionEvaluator = null)
    {
        $this->reader = $reader;
        parent::__construct($reader, $namingStrategy, $typeParser, $expressionEvaluator);
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?ClassMetadata
    {
        $jmsMetadata = parent::loadMetadataForClass($class);
        if (!$jmsMetadata instanceof JmsClassMetadata) {
            throw new \RuntimeException(\sprintf('Expected metadata of class %s.', JmsClassMetadata::class));
        }

        $classMetadata = new JsonApiClassMetadata($name = $class->name);
        $classMetadata->populateFromJmsMetadata($jmsMetadata);

        $propertiesMetadata = [];
        $propertiesAnnotations = [];

        $excludeAll = false;
        $readOnlyClass = false;
        foreach ($this->reader->getClassAnnotations($class) as $annot) {
            if ($annot instanceof Annotation\Resource) {
                $classMetadata->setResource(
                    new Resource(
                        $annot->type,
                        $annot->showLinkSelf,
                        $annot->absolute
                    )
                );
            } elseif ($annot instanceof Exclude) {
                $excludeAll = true;
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

                foreach ($propertyAnnotations as $annot) {
                    if ($annot instanceof Annotation\Id) {
                        $classMetadata->setIdField($propertyMetadata->name);
                    } elseif ($annot instanceof Annotation\Relationship) {
                        $classMetadata->addRelationship(
                            new Relationship(
                                $propertyMetadata->name,
                                $annot->includeByDefault,
                                $annot->showData,
                                $annot->showLinkSelf,
                                $annot->showLinkRelated,
                                $annot->absolute
                            )
                        );
                    }
                }
            }
        }

        return $classMetadata;
    }
}
