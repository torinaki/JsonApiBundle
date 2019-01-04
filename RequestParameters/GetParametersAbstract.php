<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters;

use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParamInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\ValidatableInterface;
use Mango\Bundle\JsonApiBundle\RequestParameters\Model\ValidatableTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GetParametersAbstract abstract class
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
abstract class GetParametersAbstract implements GetParametersInterface, ValidatableInterface
{
    use ValidatableTrait;

    /**
     * Filter
     *
     * @var FilterParamInterface[]
     * @JMS\Type("array<Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParam>")
     * @Assert\Valid()
     */
    private $filter = [];

    /**
     * Sort
     *
     * @var SortParamInterface[]
     * @JMS\Type("array<Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParam>")
     * @Assert\Valid()
     */
    private $sort = [];

    /**
     * Page
     *
     * @var PageParamInterface
     * @JMS\Type("Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination\PageParam")
     * @Assert\Valid()
     */
    private $page;

    /**
     * {@inheritdoc}
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * {@inheritdoc}
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): PageParamInterface
    {
        return $this->page;
    }
}
