<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model\Pagination;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PageParam model for page parameter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class PageParam implements PageParamInterface
{
    /**
     * Current page number
     *
     * @var int
     * @JMS\Type("integer")
     * @Assert\NotNull()
     * @Assert\GreaterThan(0)
     */
    private $number;

    /**
     * Amount of items per page
     *
     * @var int
     * @JMS\Type("integer")
     * @Assert\NotNull()
     * @Assert\GreaterThan(0)
     */
    private $size;

    /**
     * {@inheritdoc}
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
