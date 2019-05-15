<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata;

use Doctrine\Common\Collections\Collection;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
interface ClassMetadataInterface
{
    /**
     * @return Resource
     */
    public function getResource();

    /**
     * @param Resource $resource
     */
    public function setResource(Resource $resource);

    /**
     * @return string
     */
    public function getIdField();

    /**
     * @param string $idField
     */
    public function setIdField($idField);

    /**
     * @return Collection|Relationship[]
     */
    public function getRelationships();

    /**
     * @param Collection $collection
     */
    public function setRelationships(Collection $collection);

    /**
     * @param Relationship $relationship
     */
    public function addRelationship($relationship);
}
