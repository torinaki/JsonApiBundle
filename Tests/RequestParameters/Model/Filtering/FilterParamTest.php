<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Model\Filtering;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParam;

/**
 * Tests for FilterParam class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class FilterParamTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(FilterParamInterface::class, new FilterParam());
    }
}
