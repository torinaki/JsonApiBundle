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

/**
 * Validatable interface
 *
 * @author Sergey Chernecov <sergey.chernecov@ecentria.com>
 *
 * @todo: this is copy of class from ecentria/converters-library and should be removed after library is available on github
 */
interface ValidatableInterface
{
    /**
     * Set violations
     *
     * @param ConstraintViolationListInterface $violations violations
     * @return mixed
     */
    public function setViolations(ConstraintViolationListInterface $violations);

    /**
     * Get violations
     *
     * @return ConstraintViolationListInterface
     */
    public function getViolations();

    /**
     * Set valid
     *
     * @param bool $valid valid
     * @return mixed
     */
    public function setValid($valid);

    /**
     * Is valid
     *
     * @return bool
     */
    public function isValid();
}
