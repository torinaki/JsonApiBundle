<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;

/**
 * DateHandler handler to add the same handlers for dates in json:api format as for json format
 *
 * @copyright 2018 OpticsPlanet, Inc
 * @author    Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DateHandler implements SubscribingHandlerInterface
{
    /**
     * Handler
     *
     * @var \JMS\Serializer\Handler\DateHandler
     */
    private $handler;

    /**
     * DateHandler constructor.
     *
     * @param \JMS\Serializer\Handler\DateHandler $handler
     */
    public function __construct(\JMS\Serializer\Handler\DateHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $deserializationTypes = ['DateTime', 'DateTimeImmutable', 'DateInterval'];
        $serialisationTypes = ['DateTime', 'DateTimeImmutable', 'DateInterval'];
        $format = MangoJsonApiBundle::FORMAT;

        foreach ($deserializationTypes as $type) {
            $methods[] = [
                'type'      => $type,
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format'    => $format,
            ];
        }

        foreach ($serialisationTypes as $type) {
            $methods[] = [
                'type'      => $type,
                'format'    => $format,
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'method'    => 'serialize' . $type,
            ];
        }

        return $methods;
    }

    public function serializeDateTime(SerializationVisitorInterface $visitor, \DateTime $date, array $type, SerializationContext $context)
    {
        return $this->handler->serializeDateTime(...\func_get_args());
    }

    public function serializeDateTimeImmutable(
        SerializationVisitorInterface $visitor,
        \DateTimeImmutable $date,
        array $type,
        SerializationContext $context
    ) {
        return $this->handler->serializeDateTimeImmutable(...\func_get_args());
    }

    public function serializeDateInterval(SerializationVisitorInterface $visitor, \DateInterval $date, array $type, SerializationContext $context)
    {
        return $this->handler->serializeDateInterval(...\func_get_args());
    }

    public function deserializeDateTimeFromJson(JsonDeserializationVisitor $visitor, $data, array $type): ?\DateTimeInterface
    {
        return $this->handler->deserializeDateTimeFromJson(...\func_get_args());
    }

    public function deserializeDateTimeImmutableFromJson(JsonDeserializationVisitor $visitor, $data, array $type): ?\DateTimeInterface
    {
        return $this->handler->deserializeDateTimeImmutableFromJson(...\func_get_args());
    }

    public function deserializeDateIntervalFromJson(JsonDeserializationVisitor $visitor, $data, array $type): ?\DateInterval
    {
        return $this->handler->deserializeDateIntervalFromJson(...\func_get_args());
    }
}
