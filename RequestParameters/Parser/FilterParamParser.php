<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Parser;

use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Filtering\FilterParamInterface;

/**
 * FilterParamParser service to parse 'filter' parameter for parameter converter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class FilterParamParser implements ParamParserInterface
{
    const PAYLOAD_OPERATORS = 'operators';

    /**
     * {@inheritdoc}
     */
    public function parse($data, array $payload = []): array
    {
        if (!is_array($data)) {
            throw new ParamParserException(sprintf('Filter param expected to be an array, %s given', gettype($data)));
        }

        $result = [];
        $operators = $payload[self::PAYLOAD_OPERATORS] ?? [];
        foreach ($data as $field => $value) {
            $result[] = [
                'field'    => $field,
                'value'    => $value,
                'operator' => $operators[$field] ?? FilterParamInterface::OPERATOR_EQUALS
            ];
        }

        return $result;
    }
}
