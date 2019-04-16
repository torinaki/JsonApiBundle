<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints as AppAssert;

/**
 * SortParam model for sort parameter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortParam implements SortParamInterface
{
    /**
     * Field name
     *
     * @var string
     * @JMS\Type("string")
     * @AppAssert\SortField()
     */
    private $field;

    /**
     * Sorting direction
     *
     * @var string
     * @JMS\Type("string")
     * @Assert\Choice({"asc", "desc"})
     */
    private $direction;

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
    public function getDirection(): string
    {
        return $this->direction;
    }
}
