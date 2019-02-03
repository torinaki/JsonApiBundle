<?php


namespace Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory;


use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\Factory\DeserializationVisitorFactory;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiDeserializationVisitor;

class JsonApiDeserializationVisitorFactory implements DeserializationVisitorFactory
{
    /**
     * @var int
     */
    private $options = 0;

    /**
     * @var int
     */
    private $depth = 512;

    public function getVisitor(): DeserializationVisitorInterface
    {
        return new JsonApiDeserializationVisitor($this->options, $this->depth);
    }

    public function setOptions(int $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }
}
