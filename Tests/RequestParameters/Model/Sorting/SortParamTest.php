<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Model\Sorting;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParam;

/**
 * Tests for SortParam class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortParamTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(SortParamInterface::class, new SortParam());
    }
}
