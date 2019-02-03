<?php


namespace Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory;


use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Metadata\MetadataFactoryInterface;

class JsonApiSerializationVisitorFactory implements SerializationVisitorFactory
{
    /**
     * @var int
     */
    private $options = JSON_PRESERVE_ZERO_FRACTION;

    /** @var MetadataFactoryInterface */
    private $metadataFactory;

    /** @var bool */
    private $showVersionInfo = false;

    public function __construct(
        MetadataFactoryInterface $metadataFactory
    ) {
        $this->metadataFactory = $metadataFactory;
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

    /**
     * @param bool $showVersionInfo
     *
     * @return JsonApiSerializationVisitorFactory
     */
    public function setShowVersionInfo($showVersionInfo)
    {
        $this->showVersionInfo = $showVersionInfo;

        return $this;
    }
}
