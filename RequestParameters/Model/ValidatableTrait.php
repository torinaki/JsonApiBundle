<?php
/*
 * This file is part of the ecentria software.
 *
 * (c) 2017, ecentria, Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\RequestParameters\Model;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Validatable trait
 *
 * @author Sergey Chernecov <sergey.chernecov@intexsys.lv>
 */
trait ValidatableTrait
{
    /**
     * Constraint violation list
     *
     * @var ConstraintViolationListInterface
     *
     * @JMS\ReadOnly()
     */
    private $violations = [];

    /**
     * Valid
     *
     * @var bool
     *
     * @JMS\ReadOnly()
     */
    private $valid = false;

    /**
     * Set violations
     *
     * @param ConstraintViolationListInterface $violations violations
     * @return mixed
     */
    public function setViolations(ConstraintViolationListInterface $violations) : self
    {
        $this->violations = $violations;
        return $this;
    }

    /**
     * Get violations
     *
     * @return ConstraintViolationListInterface
     */
    public function getViolations() : ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * Get violations as array
     *
     * @return array
     */
    public function getViolationsArray() : array
    {
        $array = [];
        $array['violations'] = [];

        foreach ($this->violations as $violation) {
            $array['violations'][] = [
                'message'  => $violation->getMessage(),
                'property' => $violation->getPropertyPath(),
                'value'    => $violation->getInvalidValue()
            ];
        }
        return $array;
    }

    /**
     * Set valid
     *
     * @param bool $valid valid
     * @return mixed
     */
    public function setValid($valid) : self
    {
        $this->valid = filter_var($valid, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }

    /**
     * Is valid
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->valid;
    }
}
