<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use JMS\Serializer\Exception\InvalidMetadataException;
use JMS\Serializer\Metadata\ClassMetadata as JmsClassMetadata;
use Metadata\ClassMetadata as BaseClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource as ResourceMetadata;
use Mango\Bundle\JsonApiBundle\Util\StringUtil;
use Symfony\Component\Yaml\Yaml;
use JMS\Serializer\Metadata\Driver\YamlDriver as BaseYamlDriver;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class YamlDriver extends BaseYamlDriver
{
    use ConverterTrait;

    /**
     * {@inheritdoc}
     *
     * @throws InvalidMetadataException
     * @throws \RuntimeException
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, string $file): ?BaseClassMetadata
    {
        $file = \realpath($file);
        if (false === $file) {
            throw new \RuntimeException('Can not get real path of file');
        }
        $jmsMetadata = parent::loadMetadataFromFile($class, $file);
        if (!$jmsMetadata instanceof JmsClassMetadata) {
            throw new \RuntimeException(\sprintf('Expected metadata of class %s.', JmsClassMetadata::class));
        }

        $fileContents = \file_get_contents($file);
        if (false === $fileContents) {
            throw new \RuntimeException(\sprintf('Can not get file contents of file "%s"', $fileContents));
        }
        $config = Yaml::parse($fileContents);

        if (!isset($config[$name = $class->getName()])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $name, $file));
        }

        $config = $config[$name];

        $classMetadata = null;
        if (isset($config['resource'])) {
            $classMetadata = new JsonApiClassMetadata($name);
            $classMetadata->fileResources[] = $file;
            $fileName = $class->getFileName();
            if (false === $fileName) {
                throw new \RuntimeException(\sprintf('Class of file %s is internal and can not be serialized.', $file));
            }
            $classMetadata->fileResources[] = $fileName;

            $classMetadata->setResource($this->parseResource($config, $class));

            if (isset($config['resource']['idField'])) {
                $classMetadata->setIdField(trim($config['resource']['idField']));
            }

            if (isset($config['relations'])) {
                foreach ($config['relations'] as $name => $relation) {
                    $classMetadata->addRelationship(new Relationship(
                        $name,
                        $relation['includeByDefault'] ?? null,
                        $relation['showData'] ?? null,
                        $relation['showLinkSelf'] ?? null,
                        $relation['showLinkRelated'] ?? null,
                        $relation['absolute'] ?? null
                    ));
                }
            }
        }

        return $this->convert($name, $jmsMetadata, $classMetadata);
    }

    protected function parseResource(array $config, \ReflectionClass $class): ResourceMetadata
    {
        if (isset($config['resource'])) {
            $resource = $config['resource'];
            return new ResourceMetadata(
                $resource['type'],
                $resource['showLinkSelf'] ?? null,
                $resource['absolute'] ?? null
            );
        }

        return new ResourceMetadata(StringUtil::dasherize($class->getShortName()));
    }
}
