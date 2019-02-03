<?php
/*
 * (c) 2018, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Mango\Bundle\JsonApiBundle\MangoJsonApiBundle;
use Mango\Bundle\JsonApiBundle\Serializer\JsonApiDeserializationVisitor;

/**
 * DateHandler handler to add the same handlers for dates in json:api format as for json format
 *
 * @copyright 2018 OpticsPlanet, Inc
 * @author    Vlad Yarus <vladislav.yarus@intexsys.lv>
 */
class DateHandler implements SubscribingHandlerInterface
{
    /** @var BaseDateHandler */
    private $dateHandler;

    public function __construct(BaseDateHandler $dateHandler)
    {
        $this->dateHandler = $dateHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $supportedMethods = array_filter(BaseDateHandler::getSubscribingMethods(), function ($method) {
            return $method['format'] === 'json';
        });

        foreach ($supportedMethods as $method) {
            $method['format'] = MangoJsonApiBundle::FORMAT;
            if (!isset($method['method']) && $method['direction'] === GraphNavigator::DIRECTION_DESERIALIZATION) {
                $method['method'] = 'deserialize' . $method['type'] . 'FromJson';
            }
            $supportedMethods[] = $method;
        }

        return $supportedMethods;
    }

    /**
     * @param array $type
     *
     * @return \DOMCdataSection|\DOMText|mixed
     */
    public function serializeDateTime(SerializationVisitorInterface $visitor, \DateTime $date, array $type, SerializationContext $context)
    {
        return $this->dateHandler->serializeDateTime($visitor, $date, $type, $context);
    }

    /**
     * @param array $type
     *
     * @return \DOMCdataSection|\DOMText|mixed
     */
    public function serializeDateTimeImmutable(
        SerializationVisitorInterface $visitor,
        \DateTimeImmutable $date,
        array $type,
        SerializationContext $context
    ) {
        return $this->dateHandler->serializeDateTimeImmutable($visitor, $date, $type, $context);
    }

    /**
     * @param array $type
     *
     * @return \DOMCdataSection|\DOMText|mixed
     */
    public function serializeDateInterval(SerializationVisitorInterface $visitor, \DateInterval $date, array $type, SerializationContext $context)
    {
        return $this->dateHandler->serializeDateInterval($visitor, $date, $type, $context);
    }

    /**
     * @param mixed $data
     * @param array $type
     */
    public function deserializeDateTimeFromJson(JsonApiDeserializationVisitor $visitor, $data, array $type): ?\DateTimeInterface
    {
        return $this->dateHandler->deserializeDateTimeFromJson($visitor->getJsonDeserializationVisitor(), $data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     */
    public function deserializeDateTimeImmutableFromJson(JsonApiDeserializationVisitor $visitor, $data, array $type): ?\DateTimeInterface
    {
        return $this->dateHandler->deserializeDateTimeImmutableFromJson($visitor->getJsonDeserializationVisitor(), $data, $type);
    }

    /**
     * @param mixed $data
     * @param array $type
     */
    public function deserializeDateIntervalFromJson(JsonApiDeserializationVisitor $visitor, $data, array $type): ?\DateInterval
    {
        return $this->dateHandler->deserializeDateIntervalFromJson($visitor->getJsonDeserializationVisitor(), $data, $type);
    }
}
