<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\ArrayCollectionHandler as BaseArrayCollectionHandler;
use JMS\Serializer\VisitorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Context;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

/**
 * ArrayCollectionHandler handler to add the same handlers for ArrayCollection in json:api format as for json format
 *
 * @author Alexander Kurbatsky <alexander.kurbatsky@intexsys.lv>
 */
class ArrayCollectionHandler extends BaseArrayCollectionHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = parent::getSubscribingMethods();
        $additionalMethods = [];
        foreach ($methods as $method) {
            if ($method['format'] === 'json') {
                $method['format'] = MangoJsonApiBundle::FORMAT;
                $additionalMethods[] = $method;
            }
        }
        return array_merge($methods, $additionalMethods);
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function deserializeCollection(VisitorInterface $visitor, $data, array $type, Context $context)
//    {
//        // See above.
//        $type['name'] = 'array';
//
//        return $visitor->visitArray($data, $type, $context);
//    }
}
