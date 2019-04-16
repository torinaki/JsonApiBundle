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
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\PageParamParser;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Parser\ParamParserException;

/**
 * Tests for PageParamParser service
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class PageParamParserTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(ParamParserInterface::class, new PageParamParser());
    }

    /**
     * Data provider for testParse
     *
     * @return array
     */
    public function parseProvider()
    {
        return [
            // #0: empty data - default values
            [
                'data'     => [],
                'expected' => [
                    'number' => PageParamInterface::DEFAULT_NUMBER,
                    'size'   => PageParamInterface::DEFAULT_SIZE
                ],
            ],
            // #1: empty number and size - use default values
            [
                'data'     => [
                    'number' => '',
                    'size'   => ''
                ],
                'expected' => [
                    'number' => PageParamInterface::DEFAULT_NUMBER,
                    'size'   => PageParamInterface::DEFAULT_SIZE
                ],
            ],
            // #2: some strings provided - use default values
            [
                'data'     => [
                    'number' => 'some string',
                    'size'   => 'another string'
                ],
                'expected' => [
                    'number' => PageParamInterface::DEFAULT_NUMBER,
                    'size'   => PageParamInterface::DEFAULT_SIZE
                ],
            ],
            // #3: only number provided - use default value for size
            [
                'data'     => [
                    'number' => 12,
                ],
                'expected' => [
                    'number' => 12,
                    'size'   => PageParamInterface::DEFAULT_SIZE
                ],
            ],
            // #4: only size provided - use default value for number
            [
                'data'     => [
                    'size' => 24,
                ],
                'expected' => [
                    'number' => PageParamInterface::DEFAULT_NUMBER,
                    'size'   => 24
                ],
            ],
            // #8: both provided - use value from data
            [
                'data'     => [
                    'number' => 3,
                    'size'   => 21
                ],
                'expected' => [
                    'number' => 3,
                    'size'   => 21
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
        $service = new PageParamParser();

        $this->assertSame($expected, $service->parse($data, []));
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
        $service = new PageParamParser();

        $this->expectException(ParamParserException::class);
        $service->parse($data, []);
    }
}
