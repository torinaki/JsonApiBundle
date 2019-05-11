<?php
/**
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Tests\Functional\Serializer\Handler;

use JMS\Serializer\Serializer;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Functional\WebTestCase;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class Pagerfanta handler test
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class PagerfantaHandlerTest extends WebTestCase
{
    /**
     * Serializer
     *
     * @var Serializer|object
     */
    private $serializer;

    protected function setUp()
    {
        $kernel = static::bootKernel(['test_case' => 'Basic']);
        $this->serializer = $kernel->getContainer()->get('jms_serializer');
    }

    /**
     * @dataProvider provideTestSerialization
     */
    public function testSerialization(array $data, int $currentPage, int $maxPerPage, array $expectedDocument, $expectedData): void
    {
        $adapter = new ArrayAdapter($data);
        $paginator = (new Pagerfanta($adapter))
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($currentPage);
        $this->assertEquals(
            array_merge(
                \json_decode($this->serializer->serialize($expectedData, 'json:api'), true),
                $expectedDocument
            ),
            \json_decode($this->serializer->serialize($paginator, 'json:api'), true)
        );
    }

    public function provideTestSerialization()
    {
        $data = [
            (new Order())->setId(1),
            (new Order())->setId(2),
            (new Order())->setId(3),
            (new Order())->setId(4),
            (new Order())->setId(5),
        ];
        yield 'First page' => [
            'data' => $data,
            'currentPage' => 1,
            'maxPerPage' => 2,
            'expectedDocument' => [
                'meta' => [
                    'page' => 1,
                    'limit' => 2,
                    'pages' => 3,
                    'total' => 5,
                ],
                'links' => [
                    'first' => 'http://:/?page[number]=1&page[size]=2',
                    'last' => 'http://:/?page[number]=3&page[size]=2',
                    'next' => 'http://:/?page[number]=2&page[size]=2',
                    'previous' => null,
                ]
            ],
            'expectedData' => [$data[0], $data[1]],
        ];

        yield 'Second page' => [
            'data' => $data,
            'currentPage' => 2,
            'maxPerPage' => 2,
            'expectedDocument' => [
                'meta' => [
                    'page' => 2,
                    'limit' => 2,
                    'pages' => 3,
                    'total' => 5,
                ],
                'links' => [
                    'first' => 'http://:/?page[number]=1&page[size]=2',
                    'last' => 'http://:/?page[number]=3&page[size]=2',
                    'next' => 'http://:/?page[number]=3&page[size]=2',
                    'previous' => 'http://:/?page[number]=1&page[size]=2',
                ]
            ],
            'expectedData' => [$data[2], $data[3]],
        ];
    }
}
