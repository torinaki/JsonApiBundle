<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Tests\RequestParameters;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersAbstract;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\ValidatableInterface;

/**
 * Tests for GetParametersAbstract class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class GetParametersAbstractTest extends TestCase
{
    /**
     * Test to confirm that class implements required interfaces
     *
     * @return void
     */
    public function testImplements()
    {
        $object = $this->getClassInstance();

        $this->assertInstanceOf(GetParametersInterface::class, $object);
        $this->assertInstanceOf(ValidatableInterface::class, $object);
    }

    /**
     * Returns object
     *
     * @return GetParametersAbstract
     */
    private function getClassInstance()
    {
        return new class extends GetParametersAbstract {

            public function getAvailableFilterFields(): array
            {
                return [];
            }

            public function getAvailableSortingFields(): array
            {
                return [];
            }

            public static function getFilterFieldOperators(): array
            {
                return [];
            }
        };
    }
}
