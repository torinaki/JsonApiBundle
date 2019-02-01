<?php

namespace Mango\Bundle\JsonApiBundle\Tests\Cache;

use Metadata\Cache\CacheInterface;
use Metadata\ClassMetadata;

class NoopCache implements CacheInterface
{
    /**
     * {@inheritDoc}
     */
    public function loadClassMetadataFromCache(\ReflectionClass $class)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function putClassMetadataInCache(ClassMetadata $metadata)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function evictClassMetadataFromCache(\ReflectionClass $class)
    {
        return null;
    }

    /**
     * Loads a class metadata instance from the cache
     */
    public function load(string $class): ?ClassMetadata
    {
        return null;
    }

    /**
     * Puts a class metadata instance into the cache
     */
    public function put(ClassMetadata $metadata): void
    {
    }

    /**
     * Evicts the class metadata for the given class from the cache.
     */
    public function evict(string $class): void
    {
    }
}
