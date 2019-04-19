<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Functional;

use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\YamlDriver;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class YamlDriverTest extends WebTestCase
{
    public function testContainerSource()
    {
        $kernel = static::bootKernel(['test_case' => 'YamlOnly']);
        $driver = $kernel->getContainer()->get('jms_serializer.metadata_driver');
        $this->assertInstanceOf(YamlDriver::class, $driver);
    }

    public function testLoad()
    {
        $kernel = static::bootKernel(['test_case' => 'YamlOnly']);
        $driver = $kernel->getContainer()->get('jms_serializer.metadata_driver');
        $metadata = $driver->loadMetadataForClass(new \ReflectionClass(Order::class));
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
    }
}
