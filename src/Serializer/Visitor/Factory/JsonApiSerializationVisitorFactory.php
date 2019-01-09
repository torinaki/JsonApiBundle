<?php declare(strict_types = 1);

/*
* This file is part of the eCORE CART software.
*
* (c) 2019, ecentria group, inc
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory;

use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiSerializationVisitor;
use Metadata\MetadataFactoryInterface;

/**
 * JSON:API serialization visitor factory
 *
 * @author Oleg Andreyev <oleg.andreyev@ecentria.com>
 */
class JsonApiSerializationVisitorFactory implements SerializationVisitorFactory
{
    /**
     * Factory
     *
     * @var JsonSerializationVisitorFactory
     */
    private $factory;
    private $showVersionInfo;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(JsonSerializationVisitorFactory $factory, MetadataFactoryInterface $metadataFactory, $showVersionInfo)
    {
        $this->factory = $factory;
        $this->showVersionInfo = $showVersionInfo;
        $this->metadataFactory = $metadataFactory;
    }

    public function getVisitor(): SerializationVisitorInterface
    {
        return new JsonApiSerializationVisitor($this->factory->getVisitor(), $this->metadataFactory, $this->showVersionInfo);
    }
}