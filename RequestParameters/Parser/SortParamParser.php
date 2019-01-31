<?php
declare(strict_types = 1);
/*
 * (c) 2019, OpticsPlanet, Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mango\Bundle\JsonApiBundle\RequestParameters\Parser;

use Mango\Bundle\JsonApiBundle\RequestParameters\Model\Sorting\SortParamInterface;

/**
 * SortParamParser service to parse 'sort' parameter for parameter converter
 *
 * @author  Vlad Yarus <vladislav.yarus@ecentria.com>
 */
class SortParamParser implements ParamParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($data, array $payload = []): array
    {
        $result = [];
        if (!is_string($data)) {
            throw new ParamParserException(sprintf('Sort param expected to be a string, %s given', gettype($data)));
        }

        $fields = $data !== '' ? array_values(array_unique(explode(',', $data))) : [];
        foreach ($fields as $field) {
            $direction = SortParamInterface::DIRECTION_ASC;
            if (strpos($field, '-') === 0) {
                $direction = SortParamInterface::DIRECTION_DESC;
                $field = ltrim($field, '-');
            }
            $result[] = [
                'field'     => $field,
                'direction' => $direction
            ];
        }

        return $result;
    }
}
