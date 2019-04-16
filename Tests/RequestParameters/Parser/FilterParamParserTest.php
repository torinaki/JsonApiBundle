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
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\FilterParamParser;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\ParamParserException;

/**
 * Tests for FilterParamParser service
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class FilterParamParserTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(ParamParserInterface::class, new FilterParamParser());
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
                'data'     => [],
                'payload'  => [],
                'expected' => [],
            ],
            // #1: no payload - use default operator
            [
                'data'     => [
                    'column1' => 'value1',
                ],
                'payload'  => [],
                'expected' => [
                    [
                        'field'    => 'column1',
                        'value'    => 'value1',
                        'operator' => FilterParamInterface::OPERATOR_EQUALS
                    ],
                ],
            ],
            // #2: operators in payload - use operator from payload
            [
                'data'     => [
                    'column1' => 'value1',
                ],
                'payload'  => [
                    'operators' => [
                        'column1' => 'column_operator'
                    ]
                ],
                'expected' => [
                    [
                        'field'    => 'column1',
                        'value'    => 'value1',
                        'operator' => 'column_operator'
                    ],
                ],
            ],
            // #3: multiple columns
            [
                'data'     => [
                    'column1' => 'value1',
                    'column2' => 'value2',
                    'column3' => 'value3',
                ],
                'payload'  => [
                    'operators'           => [
                        'column1' => 'column_operator_1',
                        'column3' => 'column_operator_3',
                    ],
                ],
                'expected' => [
                    [
                        'field'    => 'column1',
                        'value'    => 'value1',
                        'operator' => 'column_operator_1'
                    ],
                    [
                        'field'    => 'column2',
                        'value'    => 'value2',
                        'operator' => FilterParamInterface::OPERATOR_EQUALS
                    ],
                    [
                        'field'    => 'column3',
                        'value'    => 'value3',
                        'operator' => 'column_operator_3'
                    ],
                ],
            ],
        ];
    }

    /**
     * Test for 'parse' method
     *
     * @param array $data     data to parse
     * @param array $payload  payload
     * @param array $expected expected result
     * @return void
     * @dataProvider parseProvider
     */
    public function testParse($data, $payload, $expected)
    {
        $service = new FilterParamParser();

        $this->assertSame($expected, $service->parse($data, $payload));
    }

    /**
     * Data provider for testParseWhenDataIsNotArray
     *
     * @return array
     */
    public function parseWhenDataIsNotArrayProvider()
    {
        return [
            [''],
            ['some string'],
            [123],
            ['123'],
            ['0'],
            [0],
            [1],
            [true],
            [false],
            [null],
            [new \stdClass()],
        ];
    }

    /**
     * Test for 'parse' method to confirm that exception will be thrown when passed something different from an array
     *
     * @param mixed $data data to parse
     * @return void
     * @dataProvider parseWhenDataIsNotArrayProvider
     */
    public function testParseWhenDataIsNotArray($data)
    {
        $service = new FilterParamParser();

        $this->expectException(ParamParserException::class);
        $service->parse($data, []);
    }
}
