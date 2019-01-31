<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints as AppAssert;

/**
 * FilterParam model for filter parameter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class FilterParam implements FilterParamInterface
{
    /**
     * Field name
     *
     * @var string
     * @JMS\Type("string")
     * @AppAssert\FilterField()
     */
    private $field;

    /**
     * Operator
     *
     * @var string
     * @JMS\Type("string")
     * @Assert\Choice({"equals", "contains"})
     */
    private $operator;

    /**
     * Value
     *
     * @var string
     * @JMS\Type("string")
     */
    private $value;

    /**
     * {@inheritdoc}
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
