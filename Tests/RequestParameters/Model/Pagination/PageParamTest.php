<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters\Model\Pagination;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParam;

/**
 * Tests for PageParam class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class PageParamTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $this->assertInstanceOf(PageParamInterface::class, new PageParam());
    }
}
