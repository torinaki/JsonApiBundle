<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * FilterField validation constraint to use it on filter parameter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 *
 * @Annotation
 */
class FilterField extends Constraint
{
    /**
     * Message
     *
     * @var string
     */
    public $message = "Field '{{ field_name }}' is not allowed for filtering.";
}
