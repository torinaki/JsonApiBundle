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
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\YamlDriver;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Functional\WebTestCase;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class YamlDriverTest extends WebTestCase
{
    use DriverTestTrait;

    /**
     * @var CompilableExpressionEvaluatorInterface|ObjectProphecy
     */
    protected $expressionEvaluator;

    /**
     * @var YamlDriver
     */
    protected $driver;

    protected function setUp()
    {
        $kernel = static::bootKernel(['test_case' => 'YamlOnly']);
        $this->driver = $kernel->getContainer()->get('jms_serializer.metadata_driver');
    }

    public function testContainerSource()
    {
        $this->assertInstanceOf(YamlDriver::class, $this->driver);
    }

    public function testLoad()
    {
        $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass(Order::class));
        $this->assertInstanceOf(JsonApiClassMetadata::class, $metadata);
    }

    /**
     * @dataProvider provideTestLoadMetadataForClassForYaml
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

    public function provideTestLoadMetadataForClassForYaml(): ?\Generator
    {
        foreach ($this->provideTestLoadMetadataForClass() as $testCaseName => $testCase) {
            /** @var JsonApiClassMetadata $expectedMetadata */
            $expectedMetadata = $testCase['expectedMetadata'];
            ['dirname' => $dirname, 'filename' => $filename] = \pathinfo($expectedMetadata->fileResources[0]);
            \array_unshift(
                $expectedMetadata->fileResources,
                $dirname . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . $filename . '.yml'
            );
            yield $testCaseName => $testCase;
        }
    }
}
