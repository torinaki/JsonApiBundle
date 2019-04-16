<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\SpecialParameters;

/**
 * Tests for SpecialParameters class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SpecialParametersTest extends TestCase
{
    /**
     * Test for 'getAll' method
     *
     * @return void
     */
    public function testGetAll()
    {
        $expected = ['filter', 'sort', 'page'];

        $this->assertEquals($expected, SpecialParameters::getAll());

    }
}
