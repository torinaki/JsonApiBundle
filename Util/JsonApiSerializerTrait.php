<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Util;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Pagerfanta\Adapter\CallbackAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Json api serializer trait
 *
 * @author Sergey Chernecov <sergey.chernecov@gmail.com>
 */
trait JsonApiSerializerTrait
{
    /**
     * Serialize
     *
     * @param mixed                     $data
     * @param string|null               $format
     * @param SerializationContext|null $serializationContext
     *
     * @return string
     * @throws \Exception
     */
    public function serialize(
        $data = null,
        $format = null,
        SerializationContext $serializationContext = null
    ) {
        $format = $format ? : MangoJsonApiBundle::FORMAT;

        return $this->getSerializer()
            ->serialize(
                $data,
                $format,
                $serializationContext
            );
    }

    /**
     * Build pagerfanta
     *
     * @param \Closure $getTotalCallback
     * @param \Closure $getResultsCallback
     *
     * @return Pagerfanta
     */
    public function buildPagerfanta(\Closure $getTotalCallback, \Closure $getResultsCallback)
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $pageFoundation = $request->get('page', []);

        $pager = new Pagerfanta(
            new CallbackAdapter(
                $getTotalCallback,
                $getResultsCallback
            )
        );

        $pager->setCurrentPage(isset($pageFoundation['number']) ? $pageFoundation['number'] : 1);
        $pager->setMaxPerPage(isset($pageFoundation['size']) ? $pageFoundation['size'] : 10);

        return $pager;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @throws \Exception
     */
    private function getSerializer()
    {
        switch (true) {
            case $this instanceof Controller:
            case $this instanceof ContainerInterface:
                return $this->get('json_api.serializer');
                break;
            default:
                throw new \Exception(
                    sprintf(
                        'Given trait assumes that class implements at least one of: "%s"',
                        implode(
                            '","',
                            [
                                ContainerInterface::class,
                                Controller::class
                            ]
                        )
                    )
                );
                break;
        }
    }
}