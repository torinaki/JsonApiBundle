<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Tests\Functional\Configuration\Metadata\Driver;

use JMS\Serializer\Expression\CompilableExpressionEvaluatorInterface;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\AnnotationDriver;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Functional\WebTestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class AnnotationDriverTest extends WebTestCase
{
    use DriverTestTrait;

    /**
     * @var CompilableExpressionEvaluatorInterface|ObjectProphecy
     */
    protected $expressionEvaluator;

    /**
     * @var AnnotationDriver
     */
    protected $driver;

    protected function setUp()
    {
        $kernel = static::bootKernel(['test_case' => 'AnnotationOnly']);
        $this->driver = $kernel->getContainer()->get('jms_serializer.metadata_driver');
    }

    public function testContainerSource()
    {
        $this->assertInstanceOf(AnnotationDriver::class, $this->driver);
    }

    public function testLoad()
    {
        $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(Order::class));
        $this->assertInstanceOf(JsonApiClassMetadata::class, $metadata);
    }

    /**
     * @dataProvider provideTestLoadMetadataForClass
     */
    public function testLoadMetadataForClass(string $className, JsonApiClassMetadata $expectedMetadata)
    {
        $class = new \ReflectionClass($className);
        /** @var JsonApiClassMetadata $metadata */
        $metadata = $this->driver->loadMetadataForClass($class);
        // Ignore date because is always differs
        $expectedMetadata->createdAt = $metadata->createdAt;
        $this->assertEquals(
            $metadata,
            $expectedMetadata
        );
    }
}
