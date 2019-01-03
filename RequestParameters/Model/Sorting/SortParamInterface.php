<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting;

/**
 * SortParamInterface interface for sort parameter model
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
interface SortParamInterface
{
    const DIRECTION_ASC = 'asc';
    const DIRECTION_DESC = 'desc';

    /**
     * Returns field name
     *
     * @return string
     */
    public function getField(): string;

    /**
     * Returns sorting direction
     *
     * @return string
     */
    public function getDirection(): string;
}
