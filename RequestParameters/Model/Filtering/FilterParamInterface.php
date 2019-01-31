<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering;

/**
 * FilterParamInterface interface for filter parameter model
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
interface FilterParamInterface
{
    const OPERATOR_EQUALS   = 'equals';
    const OPERATOR_CONTAINS = 'contains';

    /**
     * Returns field name
     *
     * @return string
     */
    public function getField(): string;

    /**
     * Returns value
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Returns operator
     *
     * @return string
     */
    public function getOperator(): string;
}
