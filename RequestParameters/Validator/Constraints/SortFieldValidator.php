<?php
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Mango\Bundle\JsonApiBundle\RequestParameters\GetParametersInterface;

/**
 * SortFieldValidator validator
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortFieldValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var SortField $constraint */
        $object = $this->context->getRoot();
        if (!$object instanceof GetParametersInterface) {
            throw new \LogicException(
                sprintf('This constraint could be used for an instance of %s class only', GetParametersInterface::class)
            );
        }

        if (!in_array($value, $object->getAvailableSortingFields(), true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ field_name }}', $value)
                ->addViolation();
        }
    }
}
