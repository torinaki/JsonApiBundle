<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters;

/**
 * SpecialParameters class to handle constants with parameter names that are reserved for special use
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SpecialParameters
{
    const FILTER = 'filter';
    const SORT   = 'sort';
    const PAGE   = 'page';

    /**
     * Returns list of all special parameters
     *
     * @return array
     */
    public static function getAll(): array
    {
        return [self::FILTER, self::SORT, self::PAGE];
    }
}
