<?php
/*
 * (c) Steffen Brem <steffenbrem@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Tests\Fixtures;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\AnnotationDriver;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\Driver\YamlDriver;
use Mango\Bundle\JsonApiBundle\EventListener\Serializer\JsonEventSubscriber;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Mango\Bundle\JsonApiBundle\Resolver\BaseUri\BaseUriResolver;
use Mango\Bundle\JsonApiBundle\Serializer\Exclusion\RelationshipExclusionStrategy;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\ExceptionHandler;
use Mango\Bundle\JsonApiBundle\Serializer\Serializer as JsonApiSerializer;
use Mango\Bundle\JsonApiBundle\Tests\Cache\NoopCache;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory\JsonApiDeserializationVisitorFactory;
use Mango\Bundle\JsonApiBundle\Serializer\Visitor\Factory\JsonApiSerializationVisitorFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\DateHandler;
use JMS\Serializer\Handler\StdClassHandler;
use Mango\Bundle\JsonApiBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\Handler\ArrayCollectionHandler as BaseArrayCollectionHandler;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\JsonDeserializationVisitorFactory;

/**
 * Json api serializer builder
 *
 * @author Ruslan Zavacky <ruslan.zavacky@gmail.com>
 * @deprecated Use \Mango\Bundle\JsonApiBundle\Tests\Functional\WebTestCase instead.
 *             Might be useful if serializer will be extracted as library
 */
class JsonApiSerializerBuilder
{
    /**
     * Build
     *
     * @return JsonApiSerializer
     */
    public static function build()
    {
        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy('-'));
        $drivers = [
            new YamlDriver(new FileLocator(['Mango\Bundle\JsonApiBundle\Tests\Fixtures' => __DIR__ . '/yml'])),
            new AnnotationDriver(
                new AnnotationReader(),
                $namingStrategy
            )
        ];

        $jmsMetadataFactory = new MetadataFactory(
            new AnnotationDriver(
                new AnnotationReader(),
                $namingStrategy
            )
        );
        $jsonApiChainDriver = new DriverChain($drivers);

        $jsonApiMetadataFactory = new MetadataFactory($jsonApiChainDriver);
        $jsonApiMetadataFactory->setCache(new NoopCache());
        $handlerRegistry = self::createHandlerRegistryAndAddDefaultHandlers();

        $jsonApiEventSubscriber = new JsonEventSubscriber(
            $jsonApiMetadataFactory,
            $jmsMetadataFactory,
            $namingStrategy,
            new RequestStack(),
            new BaseUriResolver(new RequestStack(), '/')
        );

        $doctrineProxySubscriber = new Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber();

        $dispatcher = new Serializer\EventDispatcher\EventDispatcher();
        $dispatcher->addSubscriber($doctrineProxySubscriber);
        $dispatcher->addSubscriber($jsonApiEventSubscriber);

        $accessorStrategy = new Serializer\Accessor\DefaultAccessorStrategy();

        $serializationVisitors = [
            MangoJsonApiBundle::FORMAT => new JsonApiSerializationVisitorFactory(
                $jsonApiMetadataFactory
            ),
            'json' => new JsonSerializationVisitorFactory(),
        ];
        $deserializationVisitors = [
            MangoJsonApiBundle::FORMAT => new JsonApiDeserializationVisitorFactory(),
            'json' => new JsonDeserializationVisitorFactory(),
        ];

        $objectConstructor = new Serializer\Construction\UnserializeObjectConstructor();

        // TODO: add ExpressionEvaluator
        $serializationGraphNavigatorFactory = [
            Serializer\GraphNavigatorInterface::DIRECTION_SERIALIZATION =>
                new Serializer\GraphNavigator\Factory\SerializationGraphNavigatorFactory(
                    $jsonApiMetadataFactory,
                    $handlerRegistry,
                    $accessorStrategy,
                    $dispatcher
                ),
            Serializer\GraphNavigatorInterface::DIRECTION_DESERIALIZATION =>
                new Serializer\GraphNavigator\Factory\DeserializationGraphNavigatorFactory(
                    $jsonApiMetadataFactory,
                    $handlerRegistry,
                    $objectConstructor,
                    $accessorStrategy,
                    $dispatcher
                ),
        ];

        $jmsSerializer = new Serializer\Serializer(
            $jmsMetadataFactory,
            $serializationGraphNavigatorFactory,
            $serializationVisitors,
            $deserializationVisitors
        );

        $exclusionStrategy = new RelationshipExclusionStrategy($jmsMetadataFactory);

        return new JsonApiSerializer($jmsSerializer, $exclusionStrategy);
    }

    /**
     * Create HandlerRegistry and add default handlers
     *
     * @return HandlerRegistry
     */
    private static function createHandlerRegistryAndAddDefaultHandlers()
    {
        $handlerRegistry = new HandlerRegistry();
        $handlerRegistry->registerSubscribingHandler(new DateHandler(new BaseDateHandler()));
        $handlerRegistry->registerSubscribingHandler(new StdClassHandler());
        $handlerRegistry->registerSubscribingHandler(new ArrayCollectionHandler(new BaseArrayCollectionHandler()));
        $handlerRegistry->registerSubscribingHandler(new ExceptionHandler());

        return $handlerRegistry;
    }
}
