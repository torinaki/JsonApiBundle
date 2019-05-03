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

namespace Mango\Bundle\JsonApiBundle\Tests\Functional\Configuration\Metadata\Driver;

use DateTime;
use JMS\Serializer\Metadata\PropertyMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Metadata\ClassMetadata as JsonApiClassMetadata;
use Mango\Bundle\JsonApiBundle\Configuration\Relationship;
use Mango\Bundle\JsonApiBundle\Configuration\Resource;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\GiftCoupon;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\Order;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderAddress;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderItem;
use Mango\Bundle\JsonApiBundle\Tests\Fixtures\OrderPaymentAbstract;

/**
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
trait DriverTestTrait
{
    public function provideTestLoadMetadataForClass(): ?\Generator
    {
        $classReflection = new \ReflectionClass(Order::class);
        $metadata = new JsonApiClassMetadata(Order::class);
        $metadata->setResource(
            new Resource(
                'order',
                false
            )
        );
        $metadata->setIdField('id');
        $metadata->addRelationship(
            new Relationship(
                'address',
                true,
                null,
                false,
                false
            )
        );
        $metadata->addRelationship(
            new Relationship(
                'payment',
                true,
                null,
                false,
                false
            )
        );
        $metadata->addRelationship(
            new Relationship(
                'items',
                true,
                null,
                false,
                false
            )
        );
        $metadata->addRelationship(
            new Relationship(
                'giftCoupons',
                true,
                null,
                false,
                false
            )
        );
        $property = new PropertyMetadata(Order::class, 'id');
        $property->serializedName = 'id';
        $property->setType(
            [
                'name' => 'string',
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'email');
        $property->serializedName = 'email';
        $property->setType(
            [
                'name' => 'string',
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'phone');
        $property->serializedName = 'phone';
        $property->setType(
            [
                'name' => 'string',
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'address');
        $property->serializedName = 'address';
        $property->setType(
            [
                'name' => OrderAddress::class,
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'adminComments');
        $property->serializedName = 'admin-comments';
        $property->setType(
            [
                'name' => 'string',
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'orderDate');
        $property->serializedName = 'date';
        $property->setType(
            [
                'name' => DateTime::class,
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'payment');
        $property->serializedName = 'payment';
        $property->setType(
            [
                'name' => OrderPaymentAbstract::class,
                'params' => [],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'items');
        $property->serializedName = 'items';
        $property->setType(
            [
                'name' => 'array',
                'params' => [
                    [
                        'name' => OrderItem::class,
                        'params' => [],
                    ]
                ],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $property = new PropertyMetadata(Order::class, 'giftCoupons');
        $property->serializedName = 'gift-coupons';
        $property->setType(
            [
                'name' => 'ArrayCollection',
                'params' => [
                    [
                        'name' => GiftCoupon::class,
                        'params' => [],
                    ]
                ],
            ]
        );
        $metadata->addPropertyMetadata($property);

        $metadata->fileResources[] = $classReflection->getFileName();
        yield 'Order' => [
            'className' => Order::class,
            'expectedMetadata' => $metadata,
        ];
    }
}
