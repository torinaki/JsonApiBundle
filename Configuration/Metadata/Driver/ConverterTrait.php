<?php
/*
 * This file is part of the ecentria group, inc. software.
 *
 * (c) 2019, ecentria group, Inc
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver;

use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use JMS\Serializer\Metadata\ClassMetadata as JmsClassMetadata;

/**
 * Class Converter trait
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
trait ConverterTrait
{
    public function convert(string $name, ?JmsClassMetadata $classMetadata, ?JsonApiClassMetadata $jsonApiClassMetadata): ?JsonApiClassMetadata
    {
        if ($classMetadata) {
            if (!isset($jsonApiClassMetadata)) {
                $jsonApiClassMetadata = new JsonApiClassMetadata($name);
            }
            $jsonApiClassMetadata->populateFromJmsMetadata($classMetadata);
            return $jsonApiClassMetadata;
        }
        return $jsonApiClassMetadata;
    }
}
