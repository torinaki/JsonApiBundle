<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination;

/**
 * PageParamInterface interface for page parameter model
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
interface PageParamInterface
{
    const DEFAULT_NUMBER = 1;
    const DEFAULT_SIZE   = 40;

    /**
     * Returns current page number
     *
     * @return int
     */
    public function getNumber(): int;

    /**
     * Returns amount of items per page
     *
     * @return int
     */
    public function getSize(): int;
}
