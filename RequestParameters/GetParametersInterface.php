<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters;

use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParamInterface;

/**
 * GetParametersInterface interface for get parameters
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
interface GetParametersInterface
{
    /**
     * Returns list of filters
     *
     * @return FilterParamInterface[]
     */
    public function getFilter(): array;

    /**
     * Returns list of sorting
     *
     * @return SortParamInterface[]
     */
    public function getSort(): array;

    /**
     * Return page parameter
     *
     * @return PageParamInterface
     */
    public function getPage(): PageParamInterface;

    /**
     * Returns list of available fields for filtering with their operators
     *
     * @return array
     */
    public function getAvailableFilterFields(): array;

    /**
     * Returns list of available fields for sorting
     *
     * @return array
     */
    public function getAvailableSortingFields(): array;

    /**
     * Returns operators for filter fields
     *
     * @return array
     *
     * todo: remove this method when operator will be passes using query
     */
    public static function getFilterFieldOperators(): array;
}
