<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory;

use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use const JSON_PRESERVE_ZERO_FRACTION;

class JsonApiSerializationVisitorFactory implements SerializationVisitorFactory
{
    /** @var int */
    private $options = JSON_PRESERVE_ZERO_FRACTION;

    /** @var MetadataFactoryInterface */
    private $metadataFactory;

    /** @var bool */
    private $showVersionInfo = false;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        bool $showVersionInfo = false
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->showVersionInfo = $showVersionInfo;
    }

    public function getVisitor(): SerializationVisitorInterface
    {
        return new JsonApiSerializationVisitor(
            $this->metadataFactory,
            $this->showVersionInfo,
            $this->options
        );
    }

    public function setOptions(int $options): self
    {
        $this->options = $options;
        return $this;
    }
}
