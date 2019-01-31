<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Parser;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\ParamParserInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\SortParamParser;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\ParamParserException;

/**
 * Tests for SortParamParser service
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortParamParserTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(ParamParserInterface::class, new SortParamParser());
    }

    /**
     * Data provider for testParse
     *
     * @return array
     */
    public function parseProvider()
    {
        return [
            // #0: empty data - empty array
            [
                'data'     => '',
                'expected' => [],
            ],
            // #1: column asc
            [
                'data'     => 'column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                ],
            ],
            // #2: column desc
            [
                'data'     => '-column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                ],
            ],
            // #3: two columns asc
            [
                'data'     => 'column1,column2',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                    [
                        'field'     => 'column2',
                        'direction' => 'asc',
                    ],
                ],
            ],
            // #4: two columns asc
            [
                'data'     => '-column1,-column2',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                    [
                        'field'     => 'column2',
                        'direction' => 'desc',
                    ],
                ],
            ],
            // #5: two columns asc and desc
            [
                'data'     => 'column1,-column2',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                    [
                        'field'     => 'column2',
                        'direction' => 'desc',
                    ],
                ],
            ],
            // #6: two columns desc and asc
            [
                'data'     => '-column1,column2',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                    [
                        'field'     => 'column2',
                        'direction' => 'asc',
                    ],
                ],
            ],
            // #7: two similar columns asc
            [
                'data'     => 'column1,column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                ],
            ],
            // #8: two similar columns desc
            [
                'data'     => '-column1,-column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                ],
            ],
            // #9: two similar columns asc and desc
            [
                'data'     => 'column1,-column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                ],
            ],
            // #10: two similar columns desc and asc
            [
                'data'     => '-column1,column1',
                'expected' => [
                    [
                        'field'     => 'column1',
                        'direction' => 'desc',
                    ],
                    [
                        'field'     => 'column1',
                        'direction' => 'asc',
                    ],
                ],
            ],
        ];
    }

    /**
     * Test for 'parse' method
     *
     * @param array $data     data to parse
     * @param array $expected expected result
     * @return void
     * @dataProvider parseProvider
     */
    public function testParse($data, $expected)
    {
        $service = new SortParamParser();

        $this->assertSame($expected, $service->parse($data, []));
    }

    /**
     * Data provider for testParseWhenDataIsNotArray
     *
     * @return array
     */
    public function parseWhenDataIsNotStringProvider()
    {
        return [
            [123],
            [0],
            [1],
            [true],
            [false],
            [null],
            [new \stdClass()],
        ];
    }

    /**
     * Test for 'parse' method to confirm that exception will be thrown when passed something different from a string
     *
     * @param mixed $data data to parse
     * @return void
     * @dataProvider parseWhenDataIsNotStringProvider
     */
    public function testParseWhenDataIsNotString($data)
    {
        $service = new SortParamParser();

        $this->expectException(ParamParserException::class);
        $service->parse($data, []);
    }
}
